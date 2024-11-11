<?php
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

    // Define the base directory you want to allow
    $baseDir = '/sites/emcotech/audits';  // Your allowed base directory

    // Sanitize and resolve the provided path
    $realFolderPath = sanitizePath($folderPath);

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
                echo "Error: Unable to create zip file.";
            }
        } else {
            echo "Error: The provided path is not a directory.";
        }
    } else {
        // Reject paths that are outside the allowed base directory
        echo "Error: Invalid directory path. Only subdirectories are allowed.";
    }
} else {
    echo "Error: No path provided.";
}
?>
