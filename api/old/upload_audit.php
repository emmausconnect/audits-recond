<?php

require __DIR__.'/../../helpers.php';

if (config('debug')) {
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);

    error_reporting(E_ALL);
    error_reporting(-1);
    ini_set("error_reporting", E_ALL);
}

if ($_POST["region"] == "UHPA") {
    $_POST["region"] = "STRASBOURG";
}

$region_dir = __DIR__ . "/../../" . $_POST["region"];
$final_file_path = $region_dir . "/" . $_FILES["actual_file"]["name"];
$error = $_FILES['actual_file']['error'];

file_put_contents("error.txt", serialize([$_POST, $_FILES, $final_file_path]));
file_put_contents("final_file_path.txt", $final_file_path);
file_put_contents("uploaded_file_error.txt", $_FILES['actual_file']['error']);

if (!is_dir($region_dir)) {
    // mkdir($region_dir, 0775, true);
    $nozone = "_Région inconnue";
    $final_file_path =
        __DIR__ . "/../../" .
        $nozone .
        "/" .
        $_FILES["actual_file"]["name"];
}

// Before moving the file, verify it exists
if (isset($_FILES['actual_file']) && $_FILES['actual_file']['error'] === UPLOAD_ERR_OK) {
    $from = $_FILES['actual_file']['tmp_name'];
    $to = $final_file_path;

    if (move_uploaded_file($from, $to)) {
        http_response_code(200);
        echo "Fichier déposé sûr " . $to;
    } else {
        http_response_code(400);
        echo $_FILES['actual_file']['error'];
    }
} else {
    http_response_code(400);
    echo "Aucun fichier envoyé.";
}