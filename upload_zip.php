<?php

require 'helpers.php';

if (config('debug')) {
    ini_set("display_errors", 0);
    ini_set("display_startup_errors", 0);

    error_reporting(E_ALL);
    error_reporting(-1);
    ini_set("error_reporting", E_ALL);
}

// Liste des régions associées aux préfixes ECID
$regions = [
    "ST" => "STRASBOURG",
    "SD" => "SAINT-DENIS",
    "MB" => "MAISON BLANCHE",
    "CR" => "CRÉTEIL",
    "MA" => "MARSEILLE",
    "GR" => "GRENOBLE",
    "LA" => "LA VILLETTE",
    "LY" => "LYON",
    "BX" => "BORDEAUX",
    "LI" => "LILLE",
    "VI" => "VICTOIRES",
    "TE" => "TEST",
];

// Récupérer la valeur de ecid dans $_POST
$ecid = isset($_POST["ecid"]) ? $_POST["ecid"] : "";

// Extraire les deux premiers caractères pour déterminer la région
$prefix = substr($ecid, 0, 2);

// Déterminer la région en fonction du préfixe
$region = isset($regions[$prefix]) ? $regions[$prefix] : "Région inconnue";
$region_dir = config('project_root_path') . "/" . $region;

function deleteDir($path)
{
    return is_file($path)
        ? @unlink($path)
        : array_map(__FUNCTION__, glob($path . "/*")) == @rmdir($path);
}

// Vérifiez que le fichier est bien téléchargé
if (
    isset($_FILES["actual_file"]) &&
    $_FILES["actual_file"]["error"] === UPLOAD_ERR_OK
) {
    $uploaded_file = $_FILES["actual_file"]["tmp_name"];
    $filename = $_FILES["actual_file"]["name"];

    // Créez le répertoire si nécessaire
    if (!is_dir($region_dir)) {
        mkdir($region_dir, 0755, true);
    }

    // Déterminez le répertoire de destination basé sur ecid
    $ecid_dir = $region_dir . "/" . $ecid;

    // Créez le répertoire ecid si nécessaire
    //
    // Supprimez le fichier s'il existe déjà
    if (is_dir($ecid_dir)) {
        deleteDir($ecid_dir);
        mkdir($ecid_dir, 0755, true);
    } else {
        mkdir($ecid_dir, 0755, true);
    }

    // Déplacer le fichier téléchargé vers le répertoire ecid
    $destination = $ecid_dir . "/" . $filename;

    // Déplacer le fichier téléchargé vers le répertoire cible
    if (move_uploaded_file($uploaded_file, $destination)) {
        // Vérifiez si le fichier est un zip et décompressez-le
        $zip = new ZipArchive();
        if ($zip->open($destination) === true) {
            $zip->extractTo($ecid_dir); // Décompression dans le répertoire ecid
            $zip->close();
            // echo "Fichier decompresse avec succes dans : $ecid_dir";
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Erreur lors de l'ouverture du fichier zip.";
        }
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Echec du déplacement du fichier.";
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    echo "Erreur lors du téléchargement du fichier.";
}
