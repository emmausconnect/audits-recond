<?php
// Function to create a zip archive of a folder
function zipFolder($folderPath, $zipFilePath) {
    $zip = new ZipArchive();
    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $folderPath = realpath($folderPath);

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folderPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($folderPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
        return true;
    } else {
        return false;
    }
}

if (isset($_GET['path'])) {
    $folderPath = $_GET['path'];
    if (is_dir($folderPath)) {
        // Extract the base name of the folder for the zip file name
        $folderName = basename(realpath($folderPath));
        $zipFilePath = tempnam(sys_get_temp_dir(), 'zip');

        if (zipFolder($folderPath, $zipFilePath)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $folderName . '.zip"');
            header('Content-Length: ' . filesize($zipFilePath));
            readfile($zipFilePath);

            unlink($zipFilePath); // Delete the temporary file
        } else {
            echo "Error: Unable to create zip file.";
        }
    } else {
        echo "Error: The provided path is not a directory.";
    }
} else {
    echo "Error: No path provided.";
}
?>