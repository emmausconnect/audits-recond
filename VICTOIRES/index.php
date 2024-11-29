<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des fichiers HTML</title>
    <style>
        #fileTable,
        #searchResults {
            margin-bottom: 4vh;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            max-width: 70vw;
            margin: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
            font-family: 'Courier New', Courier, monospace;
        }
        th {
            position: relative;
        }
        th .sort-indicator {
            margin-left: 5px;
        }
        /* Alternance de couleur des lignes */
        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tbody tr:hover {
            background-color: #e5e5e5; /* Légèrement éclairci au survol */
        }
        tbody tr.footer:hover {
            background-color: unset;
        }
        #header {
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .icon {
            font-size: 0.8em; /* Taille plus petite pour l'icône */
            margin-right: 5px;
            text-decoration: none; /* Enlever le soulignement du lien */
            color: #333; /* Couleur du lien */
        }
        .icon:hover {
            color: #000; /* Couleur du lien au survol */
        }
        .file-link {
            font-weight: normal; /* Remettre la police à normale par défaut */
        }
        .audit-count {
            font-size: 0.8em;
            color: #666;
        }
        .table-row-link {
            display: table-row;
            text-decoration: none;
            color: inherit;
        }
        .table-row-link:hover {
            background-color: #e5e5e5; /* Légèrement éclairci au survol */
        }
        .table-row-link:hover,
        .table-row-link:hover .file-link,
        .table-row-link:hover .date-cell,
        .table-row-link:hover .type-cell {
            font-weight: bold; /* Mettre en gras les liens, la date et le type au survol */
        }
        .type-cell {
            width: 120px; /* Largeur fixe pour la colonne Type */
        }
        .dl-cell {
            width: 25px; /* Largeur fixe pour la colonne Nom */
        }
        .file-cell {
            width: 570px; /* Largeur fixe pour la colonne Nom */
        }
        @media (max-width: 1300px) {
            body {
                margin: 0;
                max-width: unset;
            }
        }
    </style>
</head>
<body>

<?php
$parentFolder = basename(__DIR__); // Nom du dossier parent
$files = array_filter(glob("*"), function ($file) {
    return $file !== "index.html" && $file !== "." && $file !== "..";
});
$auditCount = count($files);


$path = isset($_GET["path"]) ? $_GET["path"] : ".";
if ($path == "") {
    $path = ".";
}

if (strpos(realpath($path), __DIR__) !== 0) {
    // The path is "above" __DIR__, handle accordingly
    die("<pre>Access to this directory is not allowed.</pre>");
}
?>

<?php
function searchFiles($directory, $searchTerm) {
    $results = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() != '.' && $file->getFilename() != '..' && $file->getFilename() != 'index.php' && $file->getFilename() != 'search.php') {
            $filePath = $file->getPathname();
            $content = @file_get_contents($filePath);

            if ($content !== false && (stripos($file->getFilename(), $searchTerm) !== false || stripos($content, $searchTerm) !== false)) {
                $relativePath = str_replace(__DIR__ . '/', '', $filePath);
                $results[] = [
                    'path' => $relativePath,
                    'name' => $file->getFilename(),
                    'type' => getFileType($file->getFilename(), false),
                    'timestamp' => $file->getMTime()
                ];
            }
        }
    }
    return $results;
}
?>

<h1 id="header">

    <a class="icon" href=".." title="Revenir au dossier parent">🔙</a>

    <?= $parentFolder ?> - AUDITS DE LA RÉGION <span class="audit-count">(<?= $auditCount ?> audits)</span>
</h1>

<?php
if ($path != ".") {
    echo '<table id="headerTable">';
        echo '<tr class="table-row-link">';
            echo '<td class="file-cell"><a class="table-row-link" title="Revenir au dossier parent" href="?path=' . urlencode(dirname($path)) . '">🔙 Revenir au dossier parent</a></td>';
        echo "</tr>";
        echo '<tr class="table-row-link">';
            echo '<td class="file-cell"><a class="table-row-link" title="Télécharger le dossier" href="/zip.php?path=./'.$parentFolder.'/'.urlencode($path).'" download>⬇️ Télécharger ce dossier en .zip</a></td>';
        echo "</tr>";
    echo "</table>";
    echo "<br/>";
} else {
    echo '<table id="headerTable">';
        echo '<tr class="table-row-link">';
            echo '<td class="file-cell"><a class="table-row-link" title="Télécharger tous les audits en .zip" href="/zip.php?path=./'.$parentFolder.'/'.urlencode($path).'" download>⬇️ Télécharger ce dossier en .zip</a></td>';
        echo "</tr>";
    echo "</table>";
    echo "<br/>";
}
?>

<div style="margin-bottom: 20px;">
    <input type="text" id="searchBox" placeholder="Rechercher dans les fichiers..." style="width: calc(100% - 20px); padding: 8px; font-family: 'Courier New', Courier, monospace;">
</div>

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
document.getElementById('searchBox').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const searchTerm = e.target.value.trim();
    const fileTable = document.getElementById('fileTable');
    const searchResults = document.getElementById('searchResults');

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
                            row.className = 'table-row-link';
                            row.innerHTML = `
                                <td class='dl-cell'><a class='download-link' title='Télécharger' href="${file.path}" download>⬇️</a></td>
                                <td class='file-cell'>📄 <a class='file-link' href="${file.path}" target="_blank">${file.name}</a></td>
                                <td class='type-cell'>${file.type}</td>
                                <td class='date-cell'>${formatDate(file.timestamp)}</td>
                                <td style='display: none;' data-timestamp="${file.timestamp}">${file.timestamp}</td>
                            `;
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
            <th></th>
            <th onclick="sortTable(1)">Nom <span id="nameSortIndicator" class="sort-indicator">↕️</span></th>
            <th onclick="sortTable(2)">Type<span id="typeSortIndicator" class="sort-indicator">↕️</span></th>
            <th onclick="sortTable(3)">Date <span id="dateSortIndicator" class="sort-indicator">⬇️ Descendant</span></th>
            <th style="display: none;">Timestamp</th> <!-- Colonne cachée pour le vrai timestamp Unix -->
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
            if ($file == "." || $file == ".." || $file == "index.php" || $file == "search.php") {
                continue;
            }

            $item_path = $path . "/" . $file;

            $is_dir = is_dir($item_path);
            $icon = get_icon($is_dir ? "dir" : "file");

            $fileName = basename($file);
            $type = getFileType($fileName, $is_dir);

            $timestamp = filemtime($item_path);

            echo "<tr class='table-row-link'>";

            if ($is_dir) {
                echo "<td class='dl-cell'><a class='download-link' title='Télécharger le dossier' href=\"/zip.php?path=./$parentFolder/$item_path\" download>⬇️</a></td>";
                echo '<td><a class="table-row-link" href="?path=' . urlencode($item_path) . '"><span class="icon">' . $icon . "</span>" . htmlspecialchars($file) . "</a></td>";
            } else {
                echo "<td class='dl-cell'><a class='download-link' title='Télécharger' href=\"$item_path\" download>⬇️</a></td>";
                echo "<td class='file-cell'>$icon <a class='file-link' href=\"$item_path\" target=\"_blank\">$fileName</a></td>";
            }

            echo "<td class='type-cell'>$type</td>";
            echo "<td class='date-cell'>" .
                formatFrenchDate($timestamp) .
                "</td>";
            echo "<td style='display: none;' data-timestamp=\"$timestamp\">" .
                intval($timestamp) .
                "</td>";
            echo "</tr>";
        }

        function getFileType($fileName, $is_dir)
        {
            $suffix = substr($fileName, 2, 2);
            switch ($suffix) {
                case "SM":
                    return "Smartphone";
                case "PC":
                    return "PC";
                case "TA":
                    return "Tablette";
                case "TE":
                    return "Téléphone";
                case $is_dir:
                    return "Dossier";
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
                x = a.cells[1].querySelector('.file-link').textContent.trim();
                y = b.cells[1].querySelector('.file-link').textContent.trim();
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

    // Initialize the table sorting on page load
    document.addEventListener('DOMContentLoaded', function() {
        sortTable(3); // Initial sort by date descending
        sortTable(3); // Initial sort by date descending
        // updateSortIndicators(3, 'desc'); // Update indicators for descending order
    });
</script>

</body>

<?php

echo '<table id="headerTable">';
    echo '<tr class="footer">';
        echo '<td class=""><a class=""><i>Crédits — Joffrey SCHROEDER, Jean-Jacques FOUGÈRE</i></a></td>';
        echo '<td class=""><a class=""><i>Version 1.2</i></a></td>';
    echo "</tr>";
echo "</table>";

?>
</html>
