<?php
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
]);
session_start();

require 'helpers.php';
$root = config('project_root_path');

$hostname = $_SERVER['HTTP_HOST'];
$region_dir = $root . "/" . $_POST["region"];
$final_file_path = $region_dir . "/";
$error = $_FILES['actual_file']['error'];

function createDirectoryStructure($path) {
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}
function handleFileUploads($final_file_path) {
    $response = [
        "success" => true,
        "user" => $_SESSION['user']['username'],
        "acl" => $_SESSION['user']['acl'],
        "files" => [],
        "errors" => []
    ];

    // Vérifier si des fichiers ont été uploadés
    if (empty($_FILES['files'])) {
        http_response_code(400);
        return json_encode([
            "success" => false,
            "message" => "Aucun fichier reçu"
        ]);
    }

    // Parcourir tous les fichiers
    foreach ($_FILES['files']['name'] as $key => $filename) {
        $from = $_FILES['files']['tmp_name'][$key];
        
        // Récupérer le chemin relatif
        $relativePath = isset($_POST['paths'][$key]) ? trim($_POST['paths'][$key], '/') : '';
        
        // Si le chemin est "Files", traiter comme un fichier à la racine
        if ($relativePath === 'Files') {
            $relativePath = '';
        }
        
        // Construire le chemin complet
        $fullPath = $final_file_path;
        if ($relativePath) {
            $fullPath .= '/' . $relativePath;
            // Créer le dossier si nécessaire
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0777, true);
            }
        }
        
        $to = $fullPath . '/' . $filename;

        // Tenter de déplacer le fichier
        if (move_uploaded_file($from, $to)) {
            $response['files'][] = [
                "filename" => $filename,
                "path" => $to,
                "success" => true
            ];
        } else {
            $response['errors'][] = [
                "filename" => $filename,
                "path" => $to,
                "error" => "Échec de l'upload"
            ];
            $response['success'] = false;
        }
    }

    http_response_code($response['success'] ? 200 : 400);
    return json_encode($response);
}

if (!is_dir($region_dir)) {
    $nozone = "_Région inconnue";
    $final_file_path = $root . "/" . $nozone . "/";
}

$isLogged = false;
if (empty($_SESSION['user'])) {
    echo json_encode([
        "success" => false,
        "error" => true,
        "message" => "Vous n'êtes pas connecté (session empty)."
    ]);
} else {
    if ($_POST['region'] === $_SESSION['user']['region']) {
        $isLogged = true;
        $username = $_SESSION['user']['username'];
        $acl      = $_SESSION['user']['acl'];
        $prefix   = $_SESSION['user']['prefix'];
        echo handleFileUploads($final_file_path);
    } else {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Non autorisé"
        ]);
    }
}
