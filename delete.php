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

// Vérification de la connexion et des permissions
if (empty($_SESSION['user'])) {
    die(json_encode(['success' => false, 'message' => 'Non autorisé']));
}

if (!isset($_POST['path'])) {
    die(json_encode(['success' => false, 'message' => 'Chemin non spécifié']));
}

$path = urldecode(__DIR__ . '/' . $_SESSION['user']['region'] . '/'. $_POST['path']);
$path_region = urldecode(__DIR__ . '/' . $_SESSION['user']['region']);
$type = $_POST['type'] ?? 'file';

// Sécurité : vérifier que le chemin est dans le bon dossier
if (strpos(realpath($path), __DIR__) !== 0) {
    die(json_encode(['success' => false, 'message' => 'Chemin non autorisé',  'region' => $_SESSION['user']['region'], 'path' => $path, 'realpath' => realpath($path),'dir' => __DIR__]));
}

// Créer le dossier CORBEILLE s'il n'existe pas
$corbeilleDir = $path_region . '/CORBEILLE';
if (!file_exists($corbeilleDir)) {
    mkdir($corbeilleDir, 0755, true);
}

// Générer un nom unique pour éviter les conflits
$baseName = basename($path);
$newPath = $corbeilleDir . '/' . $baseName;
$counter = 1;

while (file_exists($newPath)) {
    $info = pathinfo($baseName);
    $newPath = $corbeilleDir . '/' . $info['filename'] . '_' . $counter . (isset($info['extension']) ? '.' . $info['extension'] : '');
    $counter++;
}

try {
    if ($type === 'dir') {
        rename($path, $newPath);
    } else {
        rename($path, $newPath);
    }
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}