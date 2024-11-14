<?php

require 'helpers.php';

// Function to create a zip archive of a folder
function zipFolder($folderPath, $zipFilePath) {
    $zip = new ZipArchive();
    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $folderPath = realpath($folderPath);  // Get the absolute path of the folder

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

// Function to sanitize the folder path and prevent directory traversal
function sanitizePath($path) {
    // Remove any '..' or '.' from the path (directory traversal protection)
    $path = realpath($path);  // Resolve symbolic links and absolute path

    // Ensure the path does not contain any parent directories (..)
    if ($path === false || strpos($path, '..') !== false || strpos($path, './') === 0) {
        return false;
    }

    return $path;
}

if (isset($_GET['path'])) {
    $folderPath = $_GET['path'];

    // Sanitize and resolve the provided path
    $realFolderPath = sanitizePath($folderPath);

    // Define the base directory you want to allow
    $baseDir =  config('project_root_path');  // Allowed base directory (default is __DIR__, loaded from where config.php is)

    if (config('debug')) {
        var_dump($baseDir);
        var_dump($realFolderPath);
        return;
    }

    if ($realFolderPath !== false && strpos($realFolderPath, $baseDir . DIRECTORY_SEPARATOR) === 0) {
        if (is_dir($realFolderPath)) {
            // Extract the base name of the folder for the zip file name
            $folderName = basename($realFolderPath);
            $zipFilePath = tempnam(sys_get_temp_dir(), 'zip');

            if (zipFolder($realFolderPath, $zipFilePath)) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $folderName . '.zip"');
                header('Content-Length: ' . filesize($zipFilePath));
                readfile($zipFilePath);

                unlink($zipFilePath); // Delete the temporary file
            } else {
                echo "<pre>Error: Unable to create zip file.</pre>";
            }
        } else {
            echo "<pre>Error: The provided path is not a directory.</pre>";
        }
    } else {
        // Reject paths that are outside the allowed base directory
        echo "<pre>Error: Invalid directory path. Only subdirectories are allowed.</pre>";
    }
} else {
    echo "<pre>Error: No path provided.</pre>";
}
?>
