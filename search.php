<?php
header('Content-Type: application/json');

if (!isset($_GET['term']) || strlen($_GET['term']) < 2) {
    echo json_encode([]);
    exit;
}

$searchTerm = $_GET['term'];
$baseDir = __DIR__;

function searchFiles($directory, $searchTerm) {
    $results = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() != '.' && $file->getFilename() != '..' && $file->getFilename() != 'index.php' && $file->getFilename() != 'search.php') {
            $filePath = $file->getPathname();
            $content = @file_get_contents($filePath);

            if ($content !== false && (stripos($file->getFilename(), $searchTerm) !== false || stripos($content, $searchTerm) !== false)) {
                $relativePath = str_replace($directory . '/', '', $filePath);
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

function getFileType($fileName, $is_dir) {
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

$results = searchFiles($baseDir, $searchTerm);
echo json_encode($results);