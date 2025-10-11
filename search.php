<?php
header('Content-Type: application/json');
if (!isset($_GET['term']) || strlen($_GET['term']) < 2) {
    echo json_encode([]);
    exit;
}
$searchTerm = $_GET['term'];
$searchMode = isset($_GET['mode']) ? $_GET['mode'] : 'all';
$baseDir = __DIR__;

function searchFiles($directory, $searchTerm, $searchMode) {
    
    // Fake delay for testing
    // sleep(5);
    
    $results = [];
    $foundFolders = []; // Keep track of folders we've already added
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        $filename = $file->getFilename();
        if ($filename === '.' || $filename === '..' || $filename === 'index.php' ||
            $filename === '.gitkeep' || $filename === 'search.php') {
            continue;
        }

        $filePath = $file->getPathname();
        $relativePath = str_replace($directory . '/', '', $filePath);

        // Skip if the path contains 'CORBEILLE' anywhere
        if (stripos($relativePath, 'CORBEILLE') !== false) {
            continue;
        }

        // Handle folders
        if ($file->isDir()) {
            if (stripos($filename, $searchTerm) !== false) {
                // Add folder if its name matches and we haven't added it yet
                if (!isset($foundFolders[$relativePath])) {
                    $results[] = [
                        'path' => $relativePath,
                        'name' => $filename,
                        'type' => 'Dossier',
                        'timestamp' => $file->getMTime(),
                        'isDir' => true
                    ];
                    $foundFolders[$relativePath] = true;
                }
            }
            continue;
        }

        // Handle files
        $parentDir = dirname($relativePath);
        
        if ($searchMode === 'filenames') {
            // Only search in filenames
            if (stripos($filename, $searchTerm) !== false) {
                // Add parent folder if it hasn't been added yet
                if ($parentDir !== '.' && !isset($foundFolders[$parentDir])) {
                    $parentDirInfo = new SplFileInfo($directory . '/' . $parentDir);
                    $results[] = [
                        'path' => $parentDir,
                        'name' => basename($parentDir),
                        'type' => 'Dossier',
                        'timestamp' => $parentDirInfo->getMTime(),
                        'isDir' => true
                    ];
                    $foundFolders[$parentDir] = true;
                }
                // Add the file
                $results[] = [
                    'path' => $relativePath,
                    'name' => $filename,
                    'type' => getFileType($filename, false),
                    'timestamp' => $file->getMTime(),
                    'isDir' => false
                ];
            }
        } else {
            $content = @file_get_contents($filePath);
            if ($content !== false && (stripos($filename, $searchTerm) !== false || stripos($content, $searchTerm) !== false)) {
                // Add parent folder if it hasn't been added yet
                if ($parentDir !== '.' && !isset($foundFolders[$parentDir])) {
                    $parentDirInfo = new SplFileInfo($directory . '/' . $parentDir);
                    $results[] = [
                        'path' => $parentDir,
                        'name' => basename($parentDir),
                        'type' => 'Dossier',
                        'timestamp' => $parentDirInfo->getMTime(),
                        'isDir' => true
                    ];
                    $foundFolders[$parentDir] = true;
                }
                // Add the file
                $results[] = [
                    'path' => $relativePath,
                    'name' => $filename,
                    'type' => getFileType($filename, false),
                    'timestamp' => $file->getMTime(),
                    'isDir' => false
                ];
            }
        }
    }

    // Sort results: folders first (sorted by mtime), then files (sorted by mtime)
    usort($results, function($a, $b) {
        if ($a['isDir'] !== $b['isDir']) {
            return $b['isDir'] - $a['isDir']; // Folders first
        }
        // When both are folders or both are files, sort by timestamp (newest first)
        return $b['timestamp'] - $a['timestamp'];
    });

    return $results;
}

function getFileType($fileName, $is_dir) {
    if (str_starts_with($fileName, 'QrCode')) {
        return "▬ QR Code";
    }
    else if (str_ends_with($fileName, 'bolc2.csv')) {
        return "📃 CSV BOLC";
    }
    $suffix = substr($fileName, 2, 2);
    switch ($suffix) {
        case "SM":
            return "📱 Smartphone";
        case "PC":
            return "🖥️ PC";
        case "TA":
            return "⬜️ Tablette";
        case "TE":
            return "📞 Téléphone";
        case "PA":
            return "🤝🏻 UHPA";
        case "-P":
            return "📃 CSV BOLC";
        case "-T":
            return "📃 CSV BOLC";
        case $is_dir:
            return "📁 Dossier";
        default:
            return "Autre";
    }
}

$results = searchFiles($baseDir, $searchTerm, $searchMode);
echo json_encode($results);