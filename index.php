<?php
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
]);
session_start();

$path = isset($_GET["path"]) ? $_GET["path"] : ".";
if ($path == "" || $path == ".git") {
    $path = ".";
}

$hostname = $_SERVER['HTTP_HOST'];
$helpers_file = '';
$isSubdir = false;
$isLogged = false;
$prefix = '';
$isSubsubdir = $path == "." ? false : true;
$isCorbeille = (isset($_GET["path"]) && strpos($_GET["path"], 'CORBEILLE') !== false);

if (file_exists('../config.php')) {
    $helpers_file = '../helpers.php';
    $isSubdir = true;
} elseif (file_exists('config.php')) {
    $helpers_file = 'helpers.php';
}

if ($helpers_file) {
    require_once $helpers_file;
}

$parentFolder = basename(__DIR__);
$region = $parentFolder;

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Liste des fichiers HTML</title>
    <style>
        @import url('//fonts.cdnfonts.com/css/jetbrains-mono-2');

        :root {
            --font-family: "JetBrains Mono", monospace;
            --text-color: #000;
            --text-color-alt: #666;
            --background-color: #fff;
            --background-color-alt: #eee;
            --background-color-hover: #c6c6c6;
            --border-color: #717171;
            --drop-box-files-background: #f0f0f0;
            --file-link-color: blue;
            --audit-count-color: #666;
            --format-file-span: #d00000;

            --border-thickness: 2px;
            --font-weight-normal: 500;
            --font-weight-medium: 600;
            --font-weight-bold: 800;
            --line-height: 1.20rem;

            font-family: var(--font-family);
            font-optical-sizing: auto;
            font-weight: var(--font-weight-normal);
            font-style: normal;
            font-variant-numeric: tabular-nums lining-nums;
            font-size: 15px;
            --font-size: 15px;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --text-color: #fff;
                --text-color-alt: #aaa;
                --background-color: #000;
                --background-color-alt:#212121;
                --background-color-hover: #484848;
                --border-color: #606060;
                --drop-box-files-background: #151515;
                --file-link-color: #0affdd;
                --audit-count-color: #a4a4a4;
                --format-file-span: #f00;
            }
        }

        * {
            box-sizing: border-box;
        }

        * + * {
            /* margin-top: var(--line-height); */
        }

        a {
            text-decoration-thickness: var(--border-thickness);
            text-decoration: none;
        }

        .format-file-span {
            color: var(--format-file-span);
        }

        a:hover {
            text-decoration: underline;
            font-weight: var(--font-weight-bold);
            text-decoration-thickness: var(--border-thickness);
        }

        a:link, a:visited {
            color: var(--text-color);
        }

        a.download-link {
            text-decoration: none;
        }

        .table-row-link:hover .dl-cell,
        .table-row-link:hover .type-cell {
            font-weight: normal;
        }

        .clickable:hover {
            text-decoration: underline;
            text-decoration-thickness: var(--border-thickness);
        }

        html {
            /* display: flex;
            width: 100%;
            margin: 0;
            padding: 0;
            flex-direction: column;
            align-items: center; */
            background: var(--background-color);
            color: var(--text-color);
        }

        /* Alternance de couleur des lignes */
        tbody tr:nth-child(even) {
            background-color: var(--background-color-alt);
        }

        .table-row-link:hover {
            background-color: var(--background-color-hover);
        }

        a.folder-link:hover {
            text-decoration: underline;
            text-decoration-thickness: var(--border-thickness);
            font-weight: var(--font-weight-bold);
        }

        #searchBox {
            background-color: var(--background-color);
            color: var(--text-color);
            font-size: var(--font-size);
            font-family: var(--font-family);
            border: var(--border-thickness) solid var(--border-color);
        }


        /* body {
            position: relative;
            width: 100%;
            margin: 0;
            padding: var(--line-height) 2ch;
            max-width: calc(min(80ch, round(down, 100%, 1ch)));
            line-height: var(--line-height);
            overflow-x: hidden;
        } */


        .delete-btn {
            color: #ff0000;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
            font-size: 12px;
        }

        .delete-btn:hover {
            opacity: 1;
        }

        .search-file {
            margin-bottom: 20px;
            width: 100%;
        }

        .folder-group {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .folder-group h4 {
            margin: 0 0 10px 0;
        }
        .remove-file {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0 5px;
        }
        .folder-group ul {
            list-style: none;
            padding-left: 20px;
        }
        .folder-group li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2px 0;
        }
        .auth-infos {
            margin-top: 10px;
            display: block;
            margin-bottom: 15px;
        }

        #fileTable,
        #searchResults {
            margin-bottom: 4vh;
        }

        body {
            font-family: 'JetBrains Mono', 'Courier New', Courier, monospace;
            max-width: 70vw;
            margin: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 6px;
            text-align: left;
            border: var(--border-thickness) solid var(--border-color);
            font-family: 'JetBrains Mono', 'Courier New', Courier, monospace;
            border: var(--border-thickness) solid var(--border-color);
            padding: calc((var(--line-height) / 2)) calc(1ch - var(--border-thickness) / 2) calc((var(--line-height) / 2) - (var(--border-thickness)));
            line-height: var(--line-height);
            vertical-align: top;
            text-align: left;
        }

        th {
            position: relative;
        }


        th .sort-indicator {
            margin-left: 5px;
        }

        tbody tr.footer:hover {
            background-color: unset;
        }

        #header {
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 10px;
            font-family: 'Courier New', Courier, monospace;
        }

        .icon {
            font-size: 0.8em;
            /* Taille plus petite pour l'icône */
            margin-right: 5px;
            margin-left: 5px;
            text-decoration: none;
            /* Enlever le soulignement du lien */
            color: #333;
            /* Couleur du lien */
        }

        a.file-link {
            /* color: var(--file-link-color); */
        }

        .file-link {
            font-weight: normal;
            /* Remettre la police à normale par défaut */
        }

        .audit-count {
            font-size: 0.8em;
            color: var(--audit-count-color);
        }

        .table-row-link {
            display: table-row;
            text-decoration: none;
            color: inherit;
        }



        .table-row-link:hover,
        .table-row-link:hover .file-link,
        .table-row-link:hover .date-cell,
        .table-row-link:hover .type-cell {
            font-weight: var(--font-weight-bold);
            /* Mettre en gras les liens, la date et le type au survol */
        }

        .folder-link {
            text-decoration: none;
            color: inherit;
        }

        .folder-link:hover,
        .folder-link:hover .file-link,
        .folder-link:hover .date-cell,
        .folder-link:hover .type-cell {
            font-weight: bold;
            /* Mettre en gras les liens, la date et le type au survol */
        }

        .dl-cell {
            width: 25px;
            text-align: center;
        }

        .file-cell {
            width: 570px;
        }

        .type-cell {
            width: 160px;
        }

        .date-cell {
            width: 370px;
        }


        @media (max-width: 1300px) {
            body {
                margin: 0;
                max-width: unset;
            }
            html {
                padding: 10px;
            }
        }

        @media (max-width: 1300px) {

            #fileTable,
            #searchResults,
            #searchBox {
                width: 100% !important;
            }
        }
    </style>
    <style>
        #container-upload {
            display: flex;
            gap: 20px; /* Espace entre les deux box */
        }

        #drop-box-files,
        #drop-box-folder {
            height: 100px;
            border: var(--border-thickness) dashed var(--border-color);
            display: flex;
            background: var(--drop-box-files-background);
            justify-content: center;
            align-items: center;
            font-size: 16px;
            position: relative;
            cursor: pointer;
            flex: 1;
            text-align: center;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        #drag-overlay-files,
        #drag-overlay-folder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 18px;
            text-align: center;
        }

        #drop-box-files.drag-over,
        #drop-box-folder.drag-over {
            background-color: var(--drop-box-files-background);
            border-color: var(--border-color);
        }

        /* Hidden file input */
        #file-input, #folder-input {
            display: none;
        }

        /* Styling for file list */
        #file-list {
            margin-top: 20px;
            padding: 0px;
            padding-left: 10px;
            border: 1px solid #ccc;
            font-size: 14px;
            display: none;  /* Hidden by default */
        }

        /* Styling for send button */
        #send-button {
            margin-top: 20px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            display: none;  /* Hidden by default */
        }

        #send-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
    <style>
        #notification-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .notification {
            padding: 12px 24px;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }

        .notification-success {
            background-color: #4caf50;
        }

        .notification-error {
            background-color: #f44336;
        }

        .notification-warning {
            background-color: #ff9800;
        }

        .notification-info {
            background-color: #2196f3;
        }
    </style>
</head>

<body>

    <?php
    if ($isSubdir) {
        echo '<span class="auth-infos">';

        if (empty($_SESSION['user'])) {
            ?>
            <a class='login-link' href="../login.php?region=<?php echo $region; ?>">Se connecter</a>
            <?php
        } else {
            if ($region !== $_SESSION['user']['region']) {
                ?> 
                <a href="../login.php?region=<?php echo $region; ?>">Se connecter</a>
                <?php
            } else {

                if (config('debug')) {
                    var_dump($_SESSION['user']);
                }

                $isLogged = true;
                $username = $_SESSION['user']['username'];
                $email    = $_SESSION['user']['username'];
                $acl      = $_SESSION['user']['acl'];
                $prefix   = $_SESSION['user']['prefix'];

                echo 'Connecté en tant que <strong>' . $_SESSION['user']['username'] . '</strong>. <a class="login-link" href="/logout.php?region=' . $region . '">Se déconnecter</a>.';
            }
        }

        echo '</span>';
    }
    ?>

    <?php
    $files = array_filter(glob("*"), function ($file) {
        return $file !== "index.html" && $file !== "index.php" && $file !== '.gitkeep' && $file !== "search.php" && $file !== "CORBEILLE" && $file !== "." && $file !== "..";
    });
    $auditCount = count($files);

    if (strpos(realpath($path), __DIR__) !== 0) {
        // The path is "above" __DIR__, handle accordingly
        die("<pre>L'accès à ce répertoire n'est pas autorisé.</pre>");
    }
    ?>

    <?php
    if ($isSubdir) {
        ?>
        <h1 id="header">
            <a class="icon" href=".." title="Revenir au dossier parent">🔙</a>
            <?= $parentFolder ?> - AUDITS DE LA RÉGION <span class="audit-count">(<?= $auditCount ?> audits)</span>
        </h1>
        <?php
    } else {
        ?>
        <h1 id="header">
            AUDITS DES RÉGIONS
        </h1>
        <?php
    }
    ?>

    <?php
    if ($isSubdir && $isLogged) {
        ?>
        <div id="container-upload">
            <div id="drop-box-files">
            <span>
                <div id="drag-overlay-files">📃 Relâcher pour déposer le fichier.</div>
                📃 Glisser des fichiers ici, ou me cliquer pour sélectionner des
                <span class="clickable" id="select-files">fichiers</span> ou des
                <span class="clickable" id="select-folder">dossiers</span>
                <br />
                <span class="format-file-span">Format fichier</span>
                <span> : <?php echo $prefix; ?>XX24-0000.html</span>
                <br />
                <span class="format-file-span">Format dossier</span>
                <span> : <?php echo $prefix; ?>XX24-0000</span>
            </span>

            <!-- Input caché pour les fichiers -->
            <input type="file" id="file-input" accept=".html" multiple style="display: none">

            <!-- Input caché pour les dossiers -->
            <input type="file" id="folder-input" webkitdirectory style="display: none">
            </div>
        </div>

        <div id="file-list">
            <p>Aucun fichier sélectionné.</p>
        </div>

        <button id="send-button" disabled>Envoyer les fichiers</button>
        <?php
    }
    ?>

    <br />

    <?php
    if ($isSubdir && $path == '.') {
        // Compter le nombre d'éléments dans la corbeille
        $trashDir = __DIR__ . '/CORBEILLE';

        if (!is_dir($trashDir)) {
            mkdir($trashDir, 0777, true);
        }
        
        $trashCount = count(scandir($trashDir)) - 2;
        
        echo '<table id="headerTable">';
        echo '<tr class="table-row-link">';
        echo '<td class="file-cell" style="border-bottom:0"><span class="icon" style="float:left">🗑️</span><a class="table-row-link" title="Voir la corbeille" href="?path=./CORBEILLE">Voir la corbeille (' . $trashCount . ')</a></td>';
        echo "</tr>";
        echo "</table>";
    }
    ?>

    <?php
    if ($isSubdir) {
        echo '<table id="headerTable">';
        if ($path != '.') {
            echo '<tr class="table-row-link">';
            echo '<td class="file-cell"><span class="icon" style="float:left">🔙</span><a class="table-row-link" title="Revenir au dossier parent" href="?path=' . urlencode(dirname($path)) . '">Revenir au dossier parent</a></td>';
            echo "</tr>";
        }
        echo '<tr class="table-row-link">';
        echo '<td class="file-cell"><span class="icon" style="float:left">⬇️</span><a class="table-row-link" title="Télécharger le dossier" href="/zip.php?path=./' . $parentFolder . '/' . urlencode($path) . '" download>Télécharger ce dossier en .zip</a></td>';
        echo "</tr>";
        echo "</table>";
        echo "<br/>";
    }
    ?>

    <?php
    if ($isSubdir) {
        if ($path == '.') {
        ?>
        <table class='search-file'>
            <input type="text" id="searchBox" placeholder="Rechercher dans les fichiers..."
                style="width: 100%; padding: 8px;">
        </table>
        <span>/</span><br /><br />
        <?php
        }
        else {
            echo '<span>' . substr($path, 1) . '</span><br /><br />';
        }
    }
    ?>

    <div id="searchResults" style="display: none;">
        <h2>Résultats de recherche</h2>
        <table id="searchResultsTable">
            <thead>
                <tr>
                    <th></th>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th style="display: none;">Timestamp</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <script>
        let searchTimeout;
        let parentFolder = '<?php echo $parentFolder; ?>';
        document.getElementById('searchBox').addEventListener('input', function (e) {
    clearTimeout(searchTimeout);
    const searchTerm = e.target.value.trim();
    const fileTable = document.getElementById('fileTable');
    const searchResults = document.getElementById('searchResults');
    const isLogged = '<?php echo $isLogged; ?>';
    const isSubsubdir = '<?php echo $isSubsubdir; ?>';
    const isCorbeille = '<?php echo $isCorbeille; ?>';

    if (searchTerm.length >= 2) {
        searchTimeout = setTimeout(() => {
            fetch(`search.php?term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(results => {
                    const searchResultsTable = document.getElementById('searchResultsTable').getElementsByTagName('tbody')[0];
                    searchResultsTable.innerHTML = '';

                    if (results.length > 0) {
                        fileTable.style.display = 'none';
                        searchResults.style.display = 'block';

                        results.forEach(file => {
                            const row = document.createElement('tr');

                            if (file.isDir) {
                                row.innerHTML = `
                                    <td class='dl-cell'><a class='download-link' title='Télécharger le dossier' href="/zip.php?path=./${parentFolder}/${file.path}" download>⬇️</a></td>
                                    <td class='file-cell'>
                                    ${isLogged && !isSubsubdir ? `<span class='delete-btn' data-path='${file.path}' data-type='dir'>❌</span>` : ''}
                                    <span class='icon'>📁</span>
                                    <a class='folder-link file-or-folder-link' href="?path=${encodeURIComponent(file.path)}">${file.name}</a>
                                    </td>
                                    <td class='type-cell'>${file.type}</td>
                                    <td class='date-cell'>${formatDate(file.timestamp)}</td>
                                    <td style='display: none;' data-timestamp="${file.timestamp}">${file.timestamp}</td>
                                `;
                            }
                            else {
                                row.innerHTML = `
                                    <td class='dl-cell'><a class='download-link' title='Télécharger' href="${file.path}" download>⬇️</a></td>
                                    <td class='file-cell'>
                                    ${isLogged && !isSubsubdir ? `<span class='delete-btn' data-path='${file.path}' data-type='file'>❌</span>` : ''}
                                    <span class='icon'>📄</span>
                                    <a class='file-link file-or-folder-link' href="${file.path}" target="_blank">${file.name}</a>
                                    </td>
                                    <td class='type-cell'>${file.type}</td>
                                    <td class='date-cell'>${formatDate(file.timestamp)}</td>
                                    <td style='display: none;' data-timestamp="${file.timestamp}">${file.timestamp}</td>
                                `;
                            }
                            searchResultsTable.appendChild(row);
                        });
                    } else {
                        searchResultsTable.innerHTML = '<tr><td colspan="4">Aucun résultat trouvé</td></tr>';
                        searchResults.style.display = 'block';
                        fileTable.style.display = 'none';
                    }
                });
        }, 300);
    } else {
        searchResults.style.display = 'none';
        fileTable.style.display = 'table';
    }
});

        function formatDate(timestamp) {
            const months = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
            const date = new Date(timestamp * 1000);
            const day = date.getDate();
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            return `${day} ${month} ${year} à ${hours}:${minutes}`;
        }
    </script>

    <table id="fileTable">
        <thead>
            <tr>
                <?php
                if ($isSubdir) {
                    ?>
                    <th></th>
                    <th onclick="sortTable(1)">Nom <span id="nameSortIndicator" class="sort-indicator">↕️</span></th>
                    <th onclick="sortTable(2)">Type<span id="typeSortIndicator" class="sort-indicator">↕️</span></th>
                    <th onclick="sortTable(3)">Date <span id="dateSortIndicator" class="sort-indicator">⬇️ Descendant</span>
                    </th>
                    <th style="display: none;">Timestamp</th> <!-- Colonne cachée pour le vrai timestamp Unix -->
                    <?php
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            function get_icon($filetype)
            {
                return $filetype == "dir" ? "📁" : "📄";
            }

            $items = scandir($path);

            foreach ($items as $file) {

                if ($file == "." || $file == ".."  || $file == "CORBEILLE"  || $file == ".gitkeep" || str_ends_with($file, '.php') || str_ends_with($file, '.sh') || str_ends_with($file, '.md') || str_ends_with($file, '.gitignore') || $file == ".git") {
                    continue;
                }

                $item_path = $path . "/" . $file;

                $is_dir = is_dir($item_path);
                $icon = get_icon($is_dir ? "dir" : "file");

                $fileName = basename($file);
                $type = getFileType($fileName, $is_dir);

                $timestamp = filemtime($item_path);

                echo "<tr class='table-row-link'>";

                // if ($is_dir) {
                //     if ($isSubdir) {
                //         echo "<td class='dl-cell'><a class='download-link' title='Télécharger le dossier' href=\"/zip.php?path=./$parentFolder/$item_path\" download>⬇️</a></td>";
                //         echo '<td><span class="icon">' . $icon . '</span><a class="folder-link file-or-folder-link" href="?path=' . urlencode($item_path) . '">' . htmlspecialchars($file) . "</a></td>";
                //     } else {
                //         echo '<td><span class="icon">' . $icon . '</span><a class="folder-link file-or-folder-link" href="./' . htmlspecialchars($file) . '">' . htmlspecialchars($file) . "</a></td>";
                //     }
                // } else {
                //     echo "<td class='dl-cell'><a class='download-link' title='Télécharger' href=\"$item_path\" download>⬇️</a></td>";
                //     echo "<td class='file-cell'><span class=\"icon\">" . $icon . "</span><a class='file-link file-or-folder-link' href=\"$item_path\" target=\"_blank\">$fileName</a></td>";
                // }

                if ($is_dir) {
                    if ($isSubdir) {
                        echo "<td class='dl-cell'><a class='download-link' title='Télécharger le dossier' href=\"/zip.php?path=./$parentFolder/$item_path\" download>⬇️</a></td>";
                        echo '<td>' . 
                            ($isLogged && !$isSubsubdir ? '<span class="delete-btn" data-path="' . urlencode($item_path) . '" data-type="dir">❌</span> ' : '') . 
                            '<span class="icon">' . $icon . '</span> <a class="folder-link file-or-folder-link" href="?path=' . urlencode($item_path) . '">' . htmlspecialchars($file) . "</a></td>";
                    } else {
                        echo '<td><span class="icon">' . $icon . '</span><a class="folder-link file-or-folder-link" href="./' . htmlspecialchars($file) . '">' . htmlspecialchars($file) . "</a></td>";
                    }
                } else {
                    echo "<td class='dl-cell'><a class='download-link' title='Télécharger' href=\"$item_path\" download>⬇️</a></td>";
                    echo "<td class='file-cell'>" . 
                        ($isLogged && !$isSubsubdir ? "<span class='delete-btn' data-path='" . urlencode($item_path) . "' data-type='file'>❌</span> " : "") . 
                        "<span class=\"icon\">" . $icon . "</span> <a class='file-link file-or-folder-link' href=\"$item_path\" target=\"_blank\">$fileName</a></td>";
                }

                if ($isSubdir) {
                    echo "<td class='type-cell'>$type</td>";
                    echo "<td class='date-cell'>" . formatFrenchDate($timestamp) . "</td>";
                    echo "<td style='display: none;' data-timestamp=\"$timestamp\">" . intval($timestamp) . "</td>";
                }
                echo "</tr>";

            }

            function getFileType($fileName, $is_dir)
            {
                if (str_starts_with($fileName, 'QrCode')) {
                    return "▬ QR Code";
                } else if (str_ends_with($fileName, 'bolc2.csv')) {
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

            function formatFrenchDate($timestamp)
            {
                $months = [
                    "Janvier",
                    "Février",
                    "Mars",
                    "Avril",
                    "Mai",
                    "Juin",
                    "Juillet",
                    "Août",
                    "Septembre",
                    "Octobre",
                    "Novembre",
                    "Décembre",
                ];
                $dateObj = new DateTime();
                $dateObj->setTimestamp($timestamp);
                $dateObj->setTimezone(new DateTimeZone("Europe/Paris"));
                $day = $dateObj->format("d");
                $monthIndex = $dateObj->format("n") - 1;
                $year = $dateObj->format("Y");
                $hours = $dateObj->format("H");
                $minutes = $dateObj->format("i");

                return "$day " . $months[$monthIndex] . " $year à $hours:$minutes";
            }
            ?>
        </tbody>
    </table>

    <script>
        let currentSortColumn = 2;
        let currentSortDirection = 'desc';

        function sortTable(columnIndex) {
            const table = document.getElementById("fileTable");
            const tbody = table.tBodies[0];
            const rows = Array.from(tbody.rows);

            const dir = (columnIndex === currentSortColumn && currentSortDirection === 'asc') ? 'desc' : 'asc';

            rows.sort((a, b) => {
                let x, y;

                if (columnIndex === 1) { // Tri par colonne "Nom"
                    x = a.cells[1].querySelector('.file-or-folder-link').textContent.trim();
                    y = b.cells[1].querySelector('.file-or-folder-link').textContent.trim();
                } else if (columnIndex === 2) { // Tri par colonne "Type"
                    x = a.cells[2].textContent.trim();
                    y = b.cells[2].textContent.trim();
                } else if (columnIndex === 3) { // Tri par colonne "Date"
                    x = parseInt(a.cells[4].getAttribute("data-timestamp"));
                    y = parseInt(b.cells[4].getAttribute("data-timestamp"));
                }

                // Debugging log
                // console.log(`x: ${x}, typeof x: ${typeof x}, y: ${y}, typeof y: ${typeof y}, dir: ${dir}`);

                // Compare x and y based on the direction (asc or desc)
                if (typeof x === 'string') {
                    return (dir === 'asc') ? x.localeCompare(y) : y.localeCompare(x);
                } else { // Assuming x and y are numbers (timestamps)
                    return (dir === 'asc') ? (x - y) : (y - x);
                }
            });

            // Clear existing rows in tbody
            while (tbody.firstChild) {
                tbody.removeChild(tbody.firstChild);
            }

            // Append sorted rows to tbody
            rows.forEach(row => tbody.appendChild(row));

            // Update current sort column and direction
            currentSortColumn = columnIndex;
            currentSortDirection = dir;

            // Update sort indicators (if needed)
            updateSortIndicators(columnIndex, dir);
        }

        function updateSortIndicators(columnIndex, direction) {
            const nameSortIndicator = document.getElementById('nameSortIndicator');
            const typeSortIndicator = document.getElementById('typeSortIndicator');
            const dateSortIndicator = document.getElementById('dateSortIndicator');

            nameSortIndicator.textContent = '↕️';
            typeSortIndicator.textContent = '↕️';
            dateSortIndicator.textContent = '↕️';

            if (columnIndex === 1) {
                nameSortIndicator.textContent = direction === 'asc' ? '⬆️' : '⬇️';
            } else if (columnIndex === 2) {
                typeSortIndicator.textContent = direction === 'asc' ? '⬆️' : '⬇️';
            } else if (columnIndex === 3) {
                dateSortIndicator.textContent = direction === 'asc' ? '⬆️ Ascendant' : '⬇️ Descendant';
            }
        }

        <?php
        if ($isSubdir) {
            ?>
            // Initialize the table sorting on page load
            document.addEventListener('DOMContentLoaded', function () {
                sortTable(3); // Initial sort by date descending
                sortTable(3); // Initial sort by date descending
                // updateSortIndicators(3, 'desc'); // Update indicators for descending order
            });
            <?php
        }
        ?>
    </script>


    <script>
        const dropBoxFiles = document.getElementById('drop-box-files');
        const dragOverlayFiles = document.getElementById('drag-overlay-files');
        const dragOverlayFolder = document.getElementById('drag-overlay-folder');
        const fileInput = document.getElementById('file-input');
        const fileList = document.getElementById('file-list');
        const folderInput = document.getElementById('folder-input');
        const sendButton = document.getElementById('send-button');
        const regionPrefix = '<?php echo $prefix; ?>';

        // Regex patterns for validation
        const FOLDER_PATTERN = new RegExp(`^${regionPrefix}(SM|TA|TE|PC)2\\d{1}-\\d{4,5}$`);
        const FILE_PATTERN = new RegExp(`^${regionPrefix}(SM|TA|TE|PC)2\\d{1}-\\d{4,5}.*\\.html$`, 'i');

        // Validation functions
        function isValidFolderName(folderName) {
            return FOLDER_PATTERN.test(folderName);
        }

        function isValidFileName(fileName) {
            return FILE_PATTERN.test(fileName);
        }

        let selectedFiles = [];

        // Variable globale pour stocker tous les fichiers sélectionnés
        let allSelectedFiles = [];

        // Fonction modifiée pour accumuler les fichiers
        const displayFiles = (files, folderName = null) => {
        if (folderName) {
            console.log(`Ajout des fichiers du dossier: ${folderName}`);
        }

        // Ajouter les nouveaux fichiers à la collection existante
        allSelectedFiles = [...allSelectedFiles, ...Array.from(files)];

        // Mise à jour de l'affichage
        if (allSelectedFiles.length === 0) {
            fileList.innerHTML = '<p>Aucun fichier sélectionné.</p>';
            fileList.style.display = 'none';
            sendButton.style.display = 'none';
        } else {
            // Grouper les fichiers par dossier
            const filesByFolder = new Map();
            allSelectedFiles.forEach(file => {
                // Détermine le nom du dossier (soit depuis webkitRelativePath, soit depuis notre contexte)
                const folderName = file.webkitRelativePath
                    ? file.webkitRelativePath.split('/')[0]
                    : 'Fichiers';

                if (!filesByFolder.has(folderName)) {
                    filesByFolder.set(folderName, []);
                }
                filesByFolder.get(folderName).push(file);
            });

            // Créer l'HTML avec les fichiers groupés par dossier
            const foldersHtml = Array.from(filesByFolder.entries()).map(([folder, files]) => `
            <div class="folder-group">
                <h4>📁 ${folder}</h4>
                <ul>
                ${files.map(file => `
                    <li>
                    ${file.name}
                    <button class="remove-file" data-file-name="${file.name}" data-folder="${folder}">
                        ❌
                    </button>
                    </li>
                `).join('')}
                </ul>
            </div>
            `).join('');

            fileList.innerHTML = foldersHtml;
            fileList.style.display = 'block';
            sendButton.style.display = 'block';
            sendButton.disabled = false;

            // Ajouter les gestionnaires d'événements pour les boutons de suppression
            document.querySelectorAll('.remove-file').forEach(button => {
                button.addEventListener('click', (e) => {
                    const fileName = e.target.dataset.fileName;
                    const folder = e.target.dataset.folder;

                    // Supprimer le fichier de allSelectedFiles
                    allSelectedFiles = allSelectedFiles.filter(file => {
                        const fileFolder = file.webkitRelativePath
                            ? file.webkitRelativePath.split('/')[0]
                            : 'Fichiers';
                        return !(file.name === fileName && fileFolder === folder);
                    });

                    // Mettre à jour l'affichage
                    displayFiles([]);  // Passer un tableau vide car on a déjà mis à jour allSelectedFiles
                });
            });
        }
        };

        // Fonction pour réinitialiser la sélection
        const resetFileSelection = () => {
            allSelectedFiles = [];
            displayFiles([]);
        };

        dropBoxFiles.addEventListener('dragover', (event) => {
            event.preventDefault();
            if (!dropBoxFiles.classList.contains('drag-over-files')) {
                dropBoxFiles.classList.add('drag-over');
                dragOverlayFiles.style.display = 'flex';
            }
        });
        dropBoxFiles.addEventListener('dragleave', () => {
            if (!dropBoxFiles.matches(':hover')) {
                dropBoxFiles.classList.remove('drag-over-files');
                dragOverlayFiles.style.display = 'none';
            }
        });

        // Function pour lire récursivement le contenu des dossiers
        const readEntryContent = async (entry) => {
        if (!entry.isDirectory) {
            return new Promise((resolve, reject) => {
            entry.file(file => resolve([file]), reject);
            });
        }

        const dirReader = entry.createReader();
        const files = [];

        const readEntries = () => {
            return new Promise((resolve, reject) => {
            dirReader.readEntries(async entries => {
                if (!entries.length) {
                resolve();
                } else {
                const entryFiles = await Promise.all(
                    entries.map(entry => readEntryContent(entry))
                );
                files.push(...entryFiles.flat());
                readEntries().then(resolve);
                }
            }, reject);
            });
        };

        await readEntries();
        return files;
        };


        // Récupération des éléments
        const selectFiles = document.getElementById('select-files');
        const selectFolder = document.getElementById('select-folder');

        // Style pour les éléments cliquables
        document.head.insertAdjacentHTML('beforeend', `
        <style>
            .clickable {
                cursor: pointer;
                color: var(--file-link-color);
            }
        </style>
        `);

        // Gestionnaires de clic pour les sélecteurs
        selectFiles.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.click();
        });

        selectFolder.addEventListener('click', (e) => {
            e.stopPropagation();
            folderInput.click();
        });

        // Unified handling function for files and folders
        async function processInputFiles(inputFiles) {
            const folderContents = new Map();

            for (const file of inputFiles) {
                // Check if file comes from a folder structure
                if (file.webkitRelativePath) {
                    const pathParts = file.webkitRelativePath.split('/');
                    const folderName = pathParts[0];

                    // Validate folder name
                    if (!isValidFolderName(folderName)) {
                        showNotification(`Dossier invalide: ${folderName} ne correspond pas au format ${regionPrefix}/${regionPrefix}SM-XXXX`, 'error');
                        continue;
                    }

                    // Only process files directly in the parent folder
                    if (pathParts.length === 2 && !file.name.startsWith('.')) {
                        console.log("valid : folder - : " + file.name)
                        const folderFiles = folderContents.get(folderName) || [];
                        folderFiles.push(file);
                        folderContents.set(folderName, folderFiles);
                        console.log(folderFiles)
                    } else {
                        console.log("invalid : folder - : " + file.name)
                    }
                } else {
                    // Handle individual files
                    if (isValidFileName(file.name)) {
                        const individualFiles = folderContents.get(null) || [];
                        individualFiles.push(file);
                        folderContents.set(null, individualFiles);
                    } else {
                        showNotification(`Fichier invalide: ${file.name} ne correspond pas au format ${regionPrefix}PC/${regionPrefix}SM-XXXXX.html`, 'error');
                    }
                }
            }

            // Process each folder's contents
            for (const [folderName, files] of folderContents) {
                if (files.length > 0) {
                    handleFiles(files, folderName);
                }
            }
        }

        // Event listeners
        fileInput.addEventListener('change', async (event) => {
            const files = Array.from(event.target.files);
            await processInputFiles(files);
            event.target.value = ''; // Reset input after processing
        });

        folderInput.addEventListener('change', async (event) => {
            const files = Array.from(event.target.files);
            await processInputFiles(files);
            event.target.value = ''; // Reset input after processing
        });

        dropBoxFiles.addEventListener('drop', async (event) => {
            event.preventDefault();
            dropBoxFiles.classList.remove('drag-over-files');
            dragOverlayFiles.style.display = 'none';

            const items = Array.from(event.dataTransfer.items);
            const allFiles = [];

            for (const item of items) {
                if (item.kind === 'file') {
                    const entry = item.webkitGetAsEntry?.() ||
                                 item.getAsEntry?.() ||
                                 item.mozGetAsEntry?.();

                    if (entry) {
                        if (entry.isDirectory) {
                            const dirReader = entry.createReader();
                            await new Promise((resolve) => {
                                dirReader.readEntries(async (entries) => {
                                    for (const subEntry of entries) {
                                        if (!subEntry.isDirectory) {
                                            const file = await new Promise(resolve => {
                                                subEntry.file(resolve);
                                            });
                                            allFiles.push(file);
                                        }
                                    }
                                    resolve();
                                });
                            });
                        } else {
                            const file = item.getAsFile();
                            if (file) {
                                allFiles.push(file);
                            }
                        }
                    }
                }
            }

            await processInputFiles(allFiles);
        });

        // Modified handleFiles function for consistent display
        const handleFiles = (files, folderName = null) => {
            // const validFiles = files.filter(file => isValidFileName(file.name));
            const validFiles = files.filter(file => file.name);
            const invalidCount = files.length - validFiles.length;

            console.log("invalid count : " + invalidCount)
            if (invalidCount > 0) {
                showNotification(`${invalidCount} fichier(s) ignoré(s) car ne respectant pas le format requis`, 'warning');
            }

            if (validFiles.length > 0) {
                console.log(`Fichiers valides ${folderName ? `dans le dossier "${folderName}"` : ''}: ${validFiles.length}`);
                displayFiles(validFiles, folderName);
            }
        };

        sendButton.addEventListener('click', async () => {
            if (allSelectedFiles.length === 0) {
                showNotification('Aucun fichier sélectionné', 'error');
                return;
            }

            sendButton.disabled = true;
            let progress = 0;
            const totalFiles = allSelectedFiles.length;

            try {
                // Créer le FormData avec la structure dossier
                const formData = new FormData();

                // Ajouter chaque fichier avec son chemin relatif
                allSelectedFiles.forEach((file, index) => {
                    const folderPath = file.webkitRelativePath
                        ? file.webkitRelativePath.split('/')[0]
                        : 'Files';

                    formData.append('files[]', file);
                    formData.append('paths[]', folderPath);

                    console.log(file);

                    // Mise à jour du progrès
                    progress = Math.round(((index + 1) / totalFiles) * 100);
                    // showNotification(`Traitement des fichiers: ${progress}%`, 'info', 1000);
                });

                // Ajout de metadata sur le nombre total de fichiers
                formData.append('totalFiles', totalFiles);
                formData.append('region', '<?php echo $region; ?>');

                // Envoi avec gestion du timeout
                const controller = new AbortController();
                const timeout = setTimeout(() => controller.abort(), 30000); // 30s timeout

                const response = await fetch('/upload.php', {
                    method: 'POST',
                    body: formData,
                    signal: controller.signal
                });

                clearTimeout(timeout);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    showNotification(`${totalFiles} fichiers téléchargés avec succès !`, 'success');

                    // Réinitialisation
                    allSelectedFiles = [];
                    displayFiles([]);
                    sendButton.disabled = false;

                    setTimeout(function(){
                       window.location.reload();
                    }, 2500);
                } else {
                    throw new Error(data.message || 'Erreur lors du téléchargement');
                }

            } catch (error) {
                console.error('Upload error:', error);

                if (error.name === 'AbortError') {
                    showNotification('Le téléchargement a pris trop de temps', 'error');
                } else {
                    showNotification(
                        `Erreur lors du téléchargement : ${error.message}`,
                        'error'
                    );
                }

                sendButton.disabled = false;
            }
        });

        const showNotification = (message, type = 'success', duration = 3000) => {
            // Création du conteneur de notification s'il n'existe pas déjà
            let notificationContainer = document.getElementById('notification-container');
            if (!notificationContainer) {
                notificationContainer = document.createElement('div');
                notificationContainer.id = 'notification-container';
                document.body.appendChild(notificationContainer);
            }

            // Création de la notification
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;

            // Ajout au conteneur
            notificationContainer.appendChild(notification);

            // Animation d'entrée
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);

            // Suppression automatique
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300); // Attendre la fin de l'animation de sortie
            }, duration);
        };

        // Gestion de la suppression
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-btn')) {
                if (!confirm('Êtes-vous sûr de vouloir déplacer cet élément dans la corbeille ?')) {
                    return;
                }

                const path = e.target.dataset.path;
                const type = e.target.dataset.type;
                const row = e.target.closest('tr');
                const isInSearchResults = row.closest('#searchResultsTable') !== null;

                fetch('/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `path=${path}&type=${type}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Animation de suppression
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            
                            // Si c'est dans les résultats de recherche et qu'il n'y a plus de résultats
                            if (isInSearchResults) {
                                const searchResultsTable = document.getElementById('searchResultsTable').getElementsByTagName('tbody')[0];
                                if (searchResultsTable.children.length === 0) {
                                    searchResultsTable.innerHTML = '<tr><td colspan="4">Aucun résultat trouvé</td></tr>';
                                }
                            }
                            
                            showNotification('Élément déplacé dans la corbeille', 'success');
                        }, 300);

                        // Si c'est un dossier, on retire aussi tous les fichiers liés dans les résultats de recherche
                        if (type === 'dir') {
                            const pathToMatch = path + '/';
                            const allRows = document.querySelectorAll('#searchResultsTable tr, #fileTable tr');
                            allRows.forEach(row => {
                                const deleteBtn = row.querySelector('.delete-btn');
                                if (deleteBtn && deleteBtn.dataset.path.startsWith(pathToMatch)) {
                                    row.style.transition = 'opacity 0.3s';
                                    row.style.opacity = '0';
                                    setTimeout(() => row.remove(), 300);
                                }
                            });
                        }
                    } else {
                        showNotification(data.message || 'Erreur lors de la suppression', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Erreur lors de la suppression', 'error');
                    console.error('Error:', error);
                });
            }
        });
    </script>

</body>

<?php

echo '<table id="headerTable">';
echo '<tr class="footer">';
echo '<td class=""><i>Crédits — <a href="//schroed.fr" target="blank_">Joffrey SCHROEDER</a>, Jean-Jacques FOUGÈRE</i></td>';
echo '<td class=""><a href="//github.com/emmausconnect/audits-recond" target="_blank"><i>Version ' . config('version') . '</i></a></td>';
echo "</tr>";
echo "</table>";

?>

</html>