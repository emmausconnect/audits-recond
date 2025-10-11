<?php
// Configuration de la session
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
]);
session_start();

require 'helpers.php';

/**
 * Vérification de l'authentification - PRIORITAIRE
 */
function checkAuthentication()
{
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => true,
            "message" => "Vous n'êtes pas connecté (session empty)."
        ]);
        exit;
    }
    return true;
}

/**
 * Vérification des autorisations
 */
function checkAuthorization($requestedRegion)
{
    if (!isset($_SESSION['user']['region']) || $requestedRegion !== $_SESSION['user']['region']) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "Non autorisé - région non autorisée"
        ]);
        exit;
    }
    return true;
}

/**
 * Validation des données POST
 */
function validatePostData()
{
    if (!isset($_POST['region']) || empty(trim($_POST['region']))) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Région non spécifiée"
        ]);
        exit;
    }

    if (empty($_FILES['files'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Aucun fichier reçu"
        ]);
        exit;
    }

    return true;
}

/**
 * Création de la structure de répertoire
 */
function createDirectoryStructure($path)
{
    if (!file_exists($path)) {
        if (!mkdir($path, 0777, true)) {
            throw new Exception("Impossible de créer le répertoire : " . $path);
        }
    }
}

/**
 * Déplacer un fichier ou dossier existant vers CORBEILLE avec timestamp
 */
function moveToCorbeille($existingPath, $baseDirectory)
{
    $corbeilleDir = rtrim($baseDirectory, '/') . '/CORBEILLE';

    // Créer le dossier CORBEILLE s'il n'existe pas
    createDirectoryStructure($corbeilleDir);

    // Obtenir le nom de base et l'extension
    $pathInfo = pathinfo($existingPath);
    $basename = $pathInfo['filename'];
    $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

    // Générer le timestamp
    $timestamp = date('Ymd_His');

    // Construire le nouveau nom avec timestamp
    $newName = $basename . '_' . $timestamp . $extension;
    $corbeilleDestination = $corbeilleDir . '/' . $newName;

    // S'assurer que le nom de destination est unique (au cas où)
    $counter = 1;
    $originalCorbeilleDestination = $corbeilleDestination;
    while (file_exists($corbeilleDestination)) {
        $corbeilleDestination = $corbeilleDir . '/' . $basename . '_' . $timestamp . '_' . $counter . $extension;
        $counter++;
    }

    // Déplacer le fichier/dossier vers CORBEILLE
    if (rename($existingPath, $corbeilleDestination)) {
        return [
            'success' => true,
            'moved_to' => $corbeilleDestination,
            'original_path' => $existingPath
        ];
    } else {
        throw new Exception("Impossible de déplacer vers CORBEILLE: " . $existingPath);
    }
}

/**
 * Gestion des uploads de fichiers
 */
function handleFileUploads($final_file_path, $region_dir)
{
    $response = [
        "success" => true,
        "user" => $_SESSION['user']['username'] ?? 'unknown',
        "acl" => $_SESSION['user']['acl'] ?? 'none',
        "files" => [],
        "errors" => [],
        "moved_to_corbeille" => []
    ];

    // Parcourir tous les fichiers
    $fileCount = count($_FILES['files']['name']);

    for ($key = 0; $key < $fileCount; $key++) {
        // Vérifier si l'index existe pour éviter les warnings
        if (
            !isset($_FILES['files']['name'][$key]) ||
            !isset($_FILES['files']['tmp_name'][$key]) ||
            !isset($_FILES['files']['error'][$key])
        ) {
            continue;
        }

        $filename = $_FILES['files']['name'][$key];
        $from = $_FILES['files']['tmp_name'][$key];
        $error = $_FILES['files']['error'][$key];

        // Vérifier les erreurs d'upload
        if ($error !== UPLOAD_ERR_OK) {
            $response['errors'][] = [
                "filename" => $filename,
                "error" => "Erreur d'upload (code: " . $error . ")"
            ];
            $response['success'] = false;
            continue;
        }

        // Validation du nom de fichier
        if (empty($filename) || empty($from)) {
            $response['errors'][] = [
                "filename" => $filename ?: 'fichier_inconnu',
                "error" => "Nom de fichier ou chemin temporaire vide"
            ];
            $response['success'] = false;
            continue;
        }

        // Récupérer le chemin relatif
        $relativePath = isset($_POST['paths'][$key]) ? trim($_POST['paths'][$key], '/') : '';

        // Si le chemin est "Files", traiter comme un fichier à la racine
        if ($relativePath === 'Files') {
            $relativePath = '';
        }

        // Construire le chemin complet
        $fullPath = rtrim($final_file_path, '/');
        if (!empty($relativePath)) {
            $fullPath .= '/' . $relativePath;

            // Créer le dossier si nécessaire
            try {
                createDirectoryStructure($fullPath);
            } catch (Exception $e) {
                $response['errors'][] = [
                    "filename" => $filename,
                    "error" => "Impossible de créer le répertoire: " . $e->getMessage()
                ];
                $response['success'] = false;
                continue;
            }
        }

        $to = $fullPath . '/' . basename($filename); // basename pour la sécurité

        // Vérifier si le fichier/dossier existe déjà
        if (file_exists($to)) {
            try {
                $moveResult = moveToCorbeille($to, $region_dir);
                $response['moved_to_corbeille'][] = [
                    "original_path" => $moveResult['original_path'],
                    "moved_to" => $moveResult['moved_to'],
                    "filename" => $filename
                ];
            } catch (Exception $e) {
                $response['errors'][] = [
                    "filename" => $filename,
                    "error" => "Erreur lors du déplacement vers CORBEILLE: " . $e->getMessage()
                ];
                $response['success'] = false;
                continue;
            }
        }

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
                "error" => "Échec de l'upload - impossible de déplacer le fichier"
            ];
            $response['success'] = false;
        }
    }

    http_response_code($response['success'] ? 200 : 400);
    return json_encode($response);
}

/**
 * Gestion des uploads de dossiers (si nécessaire)
 */
function handleDirectoryOverride($destinationPath, $region_dir)
{
    if (is_dir($destinationPath)) {
        try {
            $moveResult = moveToCorbeille($destinationPath, $region_dir);
            return $moveResult;
        } catch (Exception $e) {
            throw new Exception("Impossible de déplacer le dossier existant vers CORBEILLE: " . $e->getMessage());
        }
    }
    return null;
}

// === EXECUTION PRINCIPALE ===

try {
    // 1. Vérification de l'authentification (PRIORITAIRE)
    checkAuthentication();

    // 2. Validation des données POST
    validatePostData();

    // 3. Vérification des autorisations
    checkAuthorization($_POST['region']);

    // 4. Configuration des chemins
    $root = config('project_root_path');
    $region = trim($_POST['region']);
    $region_dir = $root . "/" . $region;
    $final_file_path = $region_dir . "/";

    // 5. Vérification/création du répertoire de région
    if (!is_dir($region_dir)) {
        $nozone = "_Région_inconnue";
        $region_dir = $root . "/" . $nozone; // Mise à jour de $region_dir
        $final_file_path = $region_dir . "/";
    }

    // 6. Création de la structure de répertoire
    createDirectoryStructure(dirname($final_file_path));

    // 7. Traitement des uploads
    echo handleFileUploads($final_file_path, $region_dir);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur serveur: " . $e->getMessage()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur fatale: " . $e->getMessage()
    ]);
}
?>