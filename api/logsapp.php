<?php
// Configuration
$logFile = './logs_txt/api_usage.log';
$maxLines = 1000; // Number of lines to keep in memory
$refreshInterval = 1000; // milliseconds

// Check file permissions and existence
if (!file_exists($logFile)) {
    die("Log file not found: $logFile<br>Current user: " . get_current_user() . "<br>PHP process user: " . posix_getpwuid(posix_geteuid())['name']);
}

if (!is_readable($logFile)) {
    $filePerms = substr(sprintf('%o', fileperms($logFile)), -4);
    $fileOwner = posix_getpwuid(fileowner($logFile))['name'];
    $fileGroup = posix_getgrgid(filegroup($logFile))['name'];
    die("Log file exists but is not readable by PHP process<br>" .
        "File: $logFile<br>" .
        "Permissions: $filePerms<br>" .
        "Owner: $fileOwner<br>" .
        "Group: $fileGroup<br>" .
        "PHP process user: " . posix_getpwuid(posix_geteuid())['name']);
}

// Function to get last N lines efficiently
function getLastLines($filename, $lines = 50) {
    if (!file_exists($filename)) {
        return [];
    }
    
    $filesize = filesize($filename);
    if ($filesize == 0) {
        return [];
    }
    
    $handle = fopen($filename, 'rb');
    if (!$handle) {
        return [];
    }
    
    // Start from end of file
    $pos = $filesize;
    $lineCount = 0;
    $content = '';
    $chunkSize = 4096;
    
    while ($pos > 0 && $lineCount < $lines) {
        $readSize = min($chunkSize, $pos);
        $pos -= $readSize;
        
        fseek($handle, $pos);
        $chunk = fread($handle, $readSize);
        $content = $chunk . $content;
        
        // Count newlines in this chunk
        $lineCount += substr_count($chunk, "\n");
    }
    
    fclose($handle);
    
    $allLines = explode("\n", $content);
    
    // Remove empty lines and get last N lines
    $allLines = array_filter($allLines, function($line) {
        return trim($line) !== '';
    });
    
    return array_slice($allLines, -$lines);
}

// AJAX endpoint for getting updates
if (isset($_GET['ajax']) && $_GET['ajax'] === 'update') {
    header('Content-Type: application/json');
    
    $lastModified = isset($_GET['lastModified']) ? (int)$_GET['lastModified'] : 0;
    $currentModified = filemtime($logFile);
    
    $response = [
        'modified' => $currentModified,
        'hasChanges' => $currentModified > $lastModified,
        'lines' => []
    ];
    
    if ($response['hasChanges']) {
        $response['lines'] = getLastLines($logFile, $maxLines);
    }
    
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tail Log Viewer - <?php echo basename($logFile); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            background: #1a1a1a;
            color: #00ff00;
            height: 100vh;
            overflow: hidden;
        }
        
        .header {
            background: #333;
            padding: 10px 20px;
            border-bottom: 2px solid #555;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .file-info {
            font-size: 14px;
            color: #ccc;
        }
        
        .controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .menu-bar {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .menu-item {
            color: #ccc;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            transition: background-color 0.2s;
        }
        
        .menu-item:hover {
            background: #555;
        }
        
        .menu-item.active {
            background: #2d5a2d;
            color: #90ee90;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status.connected {
            background: #2d5a2d;
            color: #90ee90;
        }
        
        .status.disconnected {
            background: #5a2d2d;
            color: #ff9090;
        }
        
        .log-container {
            height: calc(100vh - 60px);
            overflow-y: auto;
            overflow-x: hidden;
            padding: 10px;
            background: #1a1a1a;
            position: relative;
        }
        
        .log-container.nowrap {
            overflow-x: auto;
        }
        
        .log-line {
            padding: 2px 0;
            border-bottom: 1px solid #2a2a2a;
            white-space: pre-wrap;
            word-break: break-all;
            font-size: 13px;
            line-height: 1.4;
        }
        
        .log-container.nowrap .log-line {
            white-space: nowrap;
            word-break: keep-all;
        }
        
        .log-line:hover {
            background: #2a2a2a;
        }
        
        .log-line.new {
            background: #2a4a2a;
            animation: highlight 2s ease-out;
        }
        
        @keyframes highlight {
            0% { background: #4a4a2a; }
            100% { background: transparent; }
        }
        
        .scroll-indicator {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: #ff6600;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            display: none;
            z-index: 1000;
        }
        
        .scroll-indicator:hover {
            background: #ff8833;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .keyboard-help {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: #ccc;
            padding: 10px;
            border-radius: 5px;
            font-size: 11px;
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="file-info">
            Tailing: <?php echo htmlspecialchars($logFile); ?> | 
            Lines: <span id="lineCount">0</span> | 
            Size: <span id="fileSize">0 KB</span>
        </div>
        <div class="controls">
            <div class="menu-bar">
                <div class="menu-item active" id="wrapToggle" onclick="toggleWrap()">
                    Wrap: ON
                </div>
                <div class="menu-item" onclick="toggleKeyboardHelp()">
                    Help (?)
                </div>
            </div>
            <div id="lastUpdate">Never updated</div>
            <div id="status" class="status connected">Connected</div>
        </div>
    </div>
    
    <div class="log-container" id="logContainer">
        <div class="loading">Loading log file...</div>
    </div>
    
    <div class="scroll-indicator" id="scrollIndicator" onclick="scrollToBottom()">
        New lines available ↓
    </div>
    
    <div class="keyboard-help" id="keyboardHelp">
        <strong>Keyboard Shortcuts:</strong><br>
        W - Toggle line wrap<br>
        End - Scroll to bottom<br>
        Home - Scroll to top<br>
        F5 - Refresh page<br>
        ? - Toggle this help
    </div>

    <script>
        let lastModified = 0;
        let isScrolledToBottom = true;
        let autoScroll = true;
        let updateInterval;
        let previousLineCount = 0;
        let wrapEnabled = getStoredWrapState(); // Load from localStorage
        let isInitialLoad = true;
        
        const logContainer = document.getElementById('logContainer');
        const statusElement = document.getElementById('status');
        const scrollIndicator = document.getElementById('scrollIndicator');
        const lineCountElement = document.getElementById('lineCount');
        const fileSizeElement = document.getElementById('fileSize');
        const lastUpdateElement = document.getElementById('lastUpdate');
        const wrapToggleElement = document.getElementById('wrapToggle');
        const keyboardHelpElement = document.getElementById('keyboardHelp');
        
        // Function to get wrap state from localStorage
        function getStoredWrapState() {
            const stored = localStorage.getItem('logViewer_wrapEnabled');
            return stored !== null ? JSON.parse(stored) : true; // Default to true if not set
        }
        
        // Function to save wrap state to localStorage
        function saveWrapState(enabled) {
            localStorage.setItem('logViewer_wrapEnabled', JSON.stringify(enabled));
        }
        
        // Check if user scrolled away from bottom
        logContainer.addEventListener('scroll', function() {
            const threshold = 50; // pixels from bottom
            isScrolledToBottom = logContainer.scrollTop + logContainer.clientHeight >= 
                               logContainer.scrollHeight - threshold;
            
            if (isScrolledToBottom) {
                scrollIndicator.style.display = 'none';
            }
        });
        
        function scrollToBottom() {
            logContainer.scrollTop = logContainer.scrollHeight;
            isScrolledToBottom = true;
            scrollIndicator.style.display = 'none';
        }
        
        function toggleWrap() {
            wrapEnabled = !wrapEnabled;
            saveWrapState(wrapEnabled); // Save to localStorage
            applyWrapState();
        }
        
        function applyWrapState() {
            if (wrapEnabled) {
                logContainer.classList.remove('nowrap');
                wrapToggleElement.textContent = 'Wrap: ON';
                wrapToggleElement.classList.add('active');
            } else {
                logContainer.classList.add('nowrap');
                wrapToggleElement.textContent = 'Wrap: OFF';
                wrapToggleElement.classList.remove('active');
            }
        }
        
        function initializeWrapState() {
            applyWrapState();
        }
        
        function toggleKeyboardHelp() {
            const isVisible = keyboardHelpElement.style.display === 'block';
            keyboardHelpElement.style.display = isVisible ? 'none' : 'block';
        }
        
        function updateStatus(connected) {
            if (connected) {
                statusElement.className = 'status connected';
                statusElement.textContent = 'Connected';
            } else {
                statusElement.className = 'status disconnected';
                statusElement.textContent = 'Disconnected';
            }
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }
        
        function updateLogContent(lines) {
            const newLineCount = lines.length;
            let html = '';
            
            lines.forEach((line, index) => {
                // Only mark as new if it's not the initial load and the line is actually new
                const isNew = !isInitialLoad && newLineCount > previousLineCount && index >= previousLineCount;
                const newClass = isNew ? ' new' : '';
                
                html += `<div class="log-line${newClass}">` +
                       `${escapeHtml(line)}` +
                       `</div>`;
            });
            
            logContainer.innerHTML = html;
            lineCountElement.textContent = newLineCount;
            
            // Auto-scroll if user was at bottom or it's the initial load
            if (isScrolledToBottom || isInitialLoad) {
                scrollToBottom();
            } else if (newLineCount > previousLineCount && !isInitialLoad) {
                scrollIndicator.style.display = 'block';
            }
            
            previousLineCount = newLineCount;
            
            // Mark initial load as complete
            if (isInitialLoad) {
                isInitialLoad = false;
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function fetchUpdates() {
            fetch(`?ajax=update&lastModified=${lastModified}&t=${Date.now()}`)
                .then(response => response.json())
                .then(data => {
                    updateStatus(true);
                    
                    if (data.hasChanges) {
                        lastModified = data.modified;
                        updateLogContent(data.lines);
                        lastUpdateElement.textContent = new Date().toLocaleTimeString();
                        
                        // Update file size (approximate)
                        const totalChars = data.lines.join('\n').length;
                        fileSizeElement.textContent = formatFileSize(totalChars);
                    }
                })
                .catch(error => {
                    console.error('Error fetching updates:', error);
                    updateStatus(false);
                });
        }
        
        // Initialize wrap state on page load
        initializeWrapState();
        
        // Initial load
        fetchUpdates();
        
        // Set up interval for updates
        updateInterval = setInterval(fetchUpdates, <?php echo $refreshInterval; ?>);
        
        // Handle page visibility change to pause/resume updates
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(updateInterval);
            } else {
                updateInterval = setInterval(fetchUpdates, <?php echo $refreshInterval; ?>);
                fetchUpdates(); // Immediate update when page becomes visible
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            switch(e.key.toLowerCase()) {
                case 'w':
                    toggleWrap();
                    e.preventDefault();
                    break;
                case 'end':
                    scrollToBottom();
                    e.preventDefault();
                    break;
                case 'home':
                    logContainer.scrollTop = 0;
                    e.preventDefault();
                    break;
                case 'f5':
                    location.reload();
                    break;
                case '?':
                    toggleKeyboardHelp();
                    e.preventDefault();
                    break;
            }
        });
    </script>
</body>
</html>