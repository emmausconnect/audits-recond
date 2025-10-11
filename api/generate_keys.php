<?php
/**
 * Script pour générer des clés API pour les utilisateurs et applications
 * Accessible uniquement via CLI
 */

// Vérifier que le script est exécuté en CLI
if (php_sapi_name() !== 'cli') {
    header('HTTP/1.0 403 Forbidden');
    echo "Ce script ne peut être exécuté que via la ligne de commande.";
    exit(1);
}

require_once '../helpers.php';

// Fonction pour afficher une ligne formatée
function displayKeyInfo($username, $apiKey, $region, $acl, $type = 'Utilisateur') {
    echo str_pad($username, 20) . " | ";
    echo str_pad($apiKey, 64) . " | ";
    echo str_pad($region, 20) . " | ";
    echo str_pad("ACL: $acl", 10) . " | ";
    echo "$type\n";
}

// En-tête
echo "\n=== Générateur de clés API ===\n\n";
echo str_pad("Nom", 20) . " | ";
echo str_pad("Clé API", 64) . " | ";
echo str_pad("Région/Description", 20) . " | ";
echo str_pad("Niveau", 10) . " | ";
echo "Type\n";
echo str_repeat("-", 130) . "\n";

// Récupérer la configuration
$config = require_once '../config.php';

// Afficher d'abord les applications avec clés prédéfinies
echo "\n--- APPLICATIONS PRÉDÉFINIES ---\n\n";
foreach ($config['apps'] as $appName => $appInfo) {
    displayKeyInfo(
        $appName,
        $appInfo['api_key'],
        $appInfo['description'],
        $appInfo['acl'],
        'Application'
    );
}

// Ensuite parcourir toutes les régions et utilisateurs
echo "\n--- UTILISATEURS PAR RÉGION ---\n\n";
foreach ($config['auth'] as $region => $users) {
    foreach ($users as $user) {
        $apiKey = generateApiKey($user['username'], $user['pass']);
        displayKeyInfo(
            $user['username'],
            $apiKey,
            $region,
            $user['acl']
        );
    }
}

echo "\n";
echo "Pour utiliser cette clé API, ajoutez l'en-tête 'X-API-Key' à vos requêtes.\n";
echo "Exemple avec cURL: curl -H 'X-API-Key: votre_clé_api' https://api.emmaus-connect.org/api/regions\n\n";