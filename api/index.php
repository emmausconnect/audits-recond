<?php

require_once "../helpers.php";

if (config("debug")) {
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);
}

function validateHostAccess(): void {
    $allowedHosts = ['audits.emmaus-connect.org', 'audit.emmaus-connect.org'];
    
    if (!in_array(config('hostname'), $allowedHosts)) {
        sendResponseAdvanced(403, [
            "error" => true,
            "message" => "L'API n'est pas disponible depuis ce domaine",
            "allowed_hosts" => $allowedHosts
        ]);
    }
}

validateHostAccess();

// Configuration des headers CORS et JSON
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit(0);
}

// Récupérer l'URI et la méthode de la requête
$request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$request_method = $_SERVER["REQUEST_METHOD"];

// Retirer le préfixe du chemin de base
$base_path = "/api";
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}


// Fonction pour vérifier si un chemin existe pour d'autres méthodes HTTP
function checkPathExistsInOtherMethods($routes, $requestPath, $currentMethod) {
    $supportedMethods = [];
    
    // Parcourir toutes les méthodes HTTP
    foreach ($routes as $method => $paths) {
        // Ignorer la méthode courante
        if ($method === $currentMethod) {
            continue;
        }
        
        // Vérifier chaque pattern de chemin pour cette méthode
        foreach ($paths as $pathPattern => $config) {
            $pattern = "#^{$pathPattern}$#";
            
            // Vérifier si le chemin correspond à ce pattern
            if (preg_match($pattern, $requestPath)) {
                $supportedMethods[] = $method;
                break;
            }
        }
    }
    
    return $supportedMethods;
}


$routes = [
    "GET" => [
        "/version/?" => ["handler" => "getApiVersion", "acl" => 0],
        "/regions/?" => ["handler" => "getRegions", "acl" => 0],
        "/regions/([A-Za-z]{2})/?" => [
            "handler" => "getRegionAudits",
            "acl" => 0,
        ],
        // '/regions/([A-Za-z]{2})/audits/?' => ['handler' => 'getRegionAudits', 'acl' => 0],
        "/doc" => ["handler" => "getApiDocumentation", "acl" => 0],
        "/" => ["handler" => "redirSwagger", "acl" => 0],
        "/logs/?" => ["handler" => "getLogs", "acl" => 10],
        // endpoints pour les applications
        "/apps/?" => ["handler" => "getApps", "acl" => 0],
        "/apps/web?" => ["handler" => "getAppsWeb", "acl" => 0],
        "/apps/([a-zA-Z0-9_-]+)/?" => [
            "handler" => "getAppVersions",
            "acl" => 0,
        ],
        "/apps/([a-zA-Z0-9_-]+)/web/?" => [
            "handler" => "getAppVersionsWeb",
            "acl" => 0,
        ],
        "/apps/([a-zA-Z0-9_-]+)/latest/?" => [
            "handler" => "getLatestAppVersion",
            "acl" => 0,
        ],
        "/apps/([a-zA-Z0-9_-]+)/download/latest/?" => [
            "handler" => "downloadLatestAppVersion",
            "acl" => 0,
        ],
        "/apps/([a-zA-Z0-9_-]+)/download/([0-9]+\.[0-9]+\.[0-9]+)/?" => [
            "handler" => "downloadSpecificAppVersion",
            "acl" => 0,
        ],
        "/apps/([a-zA-Z0-9_-]+)/changelog/?" => [
            "handler" => "getAppChangelog",
            "acl" => 0,
        ],
        "/apps/([a-zA-Z0-9_-]+)/changelog/web/?" => [
            "handler" => "getAppChangelogWeb",
            "acl" => 0,
        ],
        "/apps/([a-zA-Z0-9_-]+)/changelog/raw/?" => [
            "handler" => "getAppChangelogRaw",
            "acl" => 0,
        ],
        "/cpu/?" => [
            "handler" => "getCpuBenchmark",
            "acl" => 0,
        ],
        "/cpu/csv/?" => [
            "handler" => "getCpuBenchmarkCSV",
            "acl" => 0,
        ],
    ],
    "POST" => [
        "/upload/zip/?" => ["handler" => "uploadZip", "acl" => 3],
        "/upload/audit/?" => ["handler" => "uploadAudit", "acl" => 3],
        // endpoint pour créer une nouvelle version d'application
        "/apps/([a-zA-Z0-9_-]+)/upload/?" => [
            "handler" => "uploadAppVersion",
            "acl" => 5,
        ],
        // endpoint pour uploader un fichier changelog
        "/apps/([a-zA-Z0-9_-]+)/changelog/?" => [
            "handler" => "uploadAppChangelog",
            "acl" => 5,
        ],
    ],
    "PUT" => [
        // endpoints pour mettre à jour le numéro de version et le changelog
        "/apps/([a-zA-Z0-9_-]+)/version/?" => [
            "handler" => "updateAppVersion",
            "acl" => 5,
        ],
        "/apps/([a-zA-Z0-9_-]+)/changelog/?" => [
            "handler" => "updateAppChangelog",
            "acl" => 5,
        ],
    ],
];

// Router
$found = false;

// Parcourir les routes pour la méthode demandée (si elle existe)
foreach ($routes[$request_method] ?? [] as $pattern => $route) {
    $pattern = "#^{$pattern}$#";
    if (preg_match($pattern, $request_uri, $matches)) {
        array_shift($matches); // Supprimer la correspondance complète
        $found = true;
        $handler = $route["handler"];
        $required_acl = $route["acl"];

        // Vérifier si la route nécessite authentification
        if ($required_acl > 0) {
            try {
                $apiKey = getApiKey();
                if (!$apiKey) {
                    sendResponse(401, [
                        "error" => "Clé API manquante",
                        "requested_path" => $base_path . $request_uri,
                        // 'headers_received' => getallheaders()
                    ]);
                }

                $authenticatedEntity = authenticateApiKey($apiKey);
                if (!$authenticatedEntity) {
                    sendResponse(401, [
                        "error" => "Clé API invalide",
                        "requested_path" => $base_path . $request_uri,
                    ]);
                }

                if ($authenticatedEntity["acl"] < $required_acl) {
                    sendResponse(403, [
                        "error" =>
                            'Niveau d\'accès insuffisant pour cette ressource',
                    ]);
                }

                // Journaliser l'utilisation pour les requêtes POST ou PUT
                if ($request_method === "POST" || $request_method === "PUT") {
                    // Si l'utilisateur est authentifié, passez l'entité d'authentification
                    if (isset($authenticatedEntity)) {
                        logApiUsage(
                            $authenticatedEntity,
                            $base_path . $request_uri,
                            $request_method
                        );
                    } else {
                        // Pour les requêtes non authentifiées
                        logApiUsage(
                            null,
                            $base_path . $request_uri,
                            $request_method
                        );
                    }
                } else {
                    // Pour les requêtes non authentifiées
                    logApiUsage(
                        null,
                        $base_path . $request_uri,
                        $request_method
                    );
                }
                // Passer l'entité authentifiée à la fonction de gestion
                array_unshift($matches, $authenticatedEntity);
                call_user_func_array($handler, $matches);
            } catch (Exception $e) {
                sendResponse(500, ["error" => $e->getMessage()]);
            }
        } else {
            // Route publique, pas de vérification d'authentification
            logApiUsage(null, $base_path . $request_uri, $request_method);
            call_user_func_array($handler, $matches);
        }
        break;
    }
}

if (!$found) {
    // Vérifier si le chemin existe pour d'autres méthodes HTTP
    $supportedMethods = checkPathExistsInOtherMethods($routes, $request_uri, $request_method);
    
    if (!empty($supportedMethods)) {
        // Le chemin existe pour d'autres méthodes
        logApiUsage(null, $base_path . $request_uri, $request_method . " (405)");
        header("Allow: " . implode(", ", $supportedMethods));
        sendResponse(405, [
            "error" => true,
            "message" => "Méthode " . $request_method . " non supportée pour ce chemin. Méthodes supportées: " . implode(", ", $supportedMethods),
            "requested_path" => $base_path . $request_uri,
            "supportedMethods" => $supportedMethods
        ]);
    } else {
        // Le chemin n'existe pas du tout
        logApiUsage(null, $base_path . $request_uri, $request_method . " (404)");
        sendResponse(404, [
            "error" => true,
            "message" => "Endpoint non trouvé",
            "requested_path" => $base_path . $request_uri,
        ]);
    }
}

function getApiVersion()
{
    $version = config("version");
    sendResponse(200, ["apiVersion" => $version]);
}

function getRegions()
{
    $regions = config("regions");
    sendResponse(200, ["regions" => $regions]);
}

function getRegionDetails($regionCode)
{
    $regions = config("regions");
    $regionCode = strtoupper($regionCode);
    if (isset($regions[$regionCode])) {
        sendResponse(200, [
            "code" => $regionCode,
            "name" => $regions[$regionCode],
        ]);
    } else {
        sendResponse(404, ["error" => true, "message" => "Région non trouvée"]);
    }
}

/**
 * Liste tous les audits d'une région spécifique
 * @param string $regionCode Code de la région (2 caractères)
 */
function getRegionAudits($regionCode)
{
    $regions = config("regions");
    // Vérifier si la région existe
    $regionCode = strtoupper($regionCode);
    if (!isset($regions[$regionCode])) {
        sendResponse(404, ["error" => true, "message" => "Région non trouvée"]);
    }
    $regionName = $regions[$regionCode];
    // Chemin du répertoire de la région
    $regionDir = config("project_root_path") . "/" . $regionName;
    // Vérifier si le répertoire existe
    if (!is_dir($regionDir)) {
        sendResponse(200, ["region" => $regionName, "audits" => []]);
    }

    // Récupérer tous les éléments dans le répertoire de la région
    $audits = [];
    $items = scandir($regionDir);

    foreach ($items as $item) {
        // Ignorer "." et ".."
        if ($item === "." || $item === "..") {
            continue;
        }

        $itemPath = $regionDir . "/" . $item;

        // Vérifier si c'est la corbeille (case insensitive)
        if (is_dir($itemPath) && strtolower($item) === "corbeille") {
            continue;
        }

        // Cas 1: C'est un fichier HTML -> 1 audit
        if (
            is_file($itemPath) &&
            pathinfo($itemPath, PATHINFO_EXTENSION) === "html"
        ) {
            $auditInfo = [
                "ecid" => pathinfo($item, PATHINFO_FILENAME),
                "type" => "file",
                "modified_date" => filemtime($itemPath),
                "web_url" =>
                    "https://" .
                    config("hostname") .
                    "/" .
                    rawurlencode($regionName) .
                    "/" .
                    rawurlencode($item),
                "download_url" =>
                    "https://" .
                    config("hostname") .
                    "/" .
                    rawurlencode($regionName) .
                    "/" .
                    rawurlencode($item),
                "files" => [
                    [
                        "name" => $item,
                        // 'size' => filesize($itemPath),
                        "type" => "file",
                        "modified_date" => filemtime($itemPath),
                    ],
                ],
            ];
            $audits[] = $auditInfo;
        }
        // Cas 2: C'est un dossier -> 1 audit
        elseif (is_dir($itemPath)) {
            $auditInfo = [
                "ecid" => $item,
                "type" => "directory",
                "modified_date" => filemtime($itemPath),
                "web_url" =>
                    "https://" .
                    config("hostname") .
                    "/" .
                    rawurlencode($regionName) .
                    "/?path=" .
                    rawurlencode("./") .
                    rawurlencode($item),
                "download_url" =>
                    "https://" .
                    config("hostname") .
                    "/zip.php?path=" .
                    rawurlencode("./") .
                    rawurlencode($regionName) .
                    "/" .
                    rawurlencode($item),
                // "download_url" => "https://audits.emmaus-connect.org/zip.php?path=./LA%20VILLETTE/./LVPC25-0092",
                "files" => [],
            ];

            // Récupérer tous les fichiers du dossier
            $files = scandir($itemPath);
            foreach ($files as $file) {
                if ($file === "." || $file === "..") {
                    continue;
                }

                $filePath = $itemPath . "/" . $file;
                $fileInfo = [
                    "name" => $file,
                    // 'size' => is_file($filePath) ? filesize($filePath) : 0,
                    "type" => is_dir($filePath) ? "directory" : "file",
                    "modified_date" => filemtime($filePath),
                ];
                $auditInfo["files"][] = $fileInfo;
            }

            $audits[] = $auditInfo;
        }
    }

    // Trier les audits par date de modification (plus récent d'abord)
    usort($audits, function ($a, $b) {
        return $b["modified_date"] - $a["modified_date"];
    });

    // Formater les dates pour l'affichage
    foreach ($audits as &$audit) {
        $audit["modified_date"] = date("Y-m-d H:i:s", $audit["modified_date"]);
        foreach ($audit["files"] as &$file) {
            $file["modified_date"] = date(
                "Y-m-d H:i:s",
                $file["modified_date"]
            );
            // Convertir la taille en format lisible (KB, MB, etc.)
            // $file['size_formatted'] = formatFileSize($file['size']);
        }
    }

    sendResponse(200, [
        "region" => [
            "code" => $regionCode,
            "name" => $regionName,
        ],
        "audits_count" => count($audits),
        "audits" => $audits,
    ]);
}

/**
 * Formater la taille du fichier en KB, MB, etc.
 * @param int $size Taille en octets
 * @return string Taille formatée
 */
function formatFileSize($size)
{
    $units = ["B", "KB", "MB", "GB", "TB"];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . " " . $units[$i];
}

function uploadZip($user)
{
    // Vérification que les données nécessaires sont présentes
    if (!isset($_POST["ecid"]) || !isset($_FILES["actual_file"])) {
        sendResponse(400, ["error" => true, "message" => "Données manquantes"]);
    }

    $ecid = $_POST["ecid"];
    $prefix = substr($ecid, 0, 2);
    $regions = config("regions");
    $region = isset($regions[$prefix]) ? $regions[$prefix] : "_Région inconnue";

    // Chemin du répertoire de la région
    $region_dir = config("project_root_path") . "/" . $region;

    // Vérifiez que le fichier est bien téléchargé
    if ($_FILES["actual_file"]["error"] === UPLOAD_ERR_OK) {
        $uploaded_file = $_FILES["actual_file"]["tmp_name"];
        $filename = $_FILES["actual_file"]["name"];

        // Créez le répertoire si nécessaire
        if (!is_dir($region_dir)) {
            mkdir($region_dir, 0755, true);
        }

        // Déterminez le répertoire de destination basé sur ecid
        $ecid_dir = $region_dir . "/" . $ecid;

        // Si le dossier existe déjà, le déplacer dans "CORBEILLE"
        if (is_dir($ecid_dir)) {
            $corbeille_dir = $region_dir . "/CORBEILLE";
            if (!is_dir($corbeille_dir)) {
                mkdir($corbeille_dir, 0755, true);
            }
            // Générer un nom unique pour éviter les collisions
            $timestamp = date("Ymd_His");
            $corbeille_target = $corbeille_dir . "/" . $ecid . "_" . $timestamp;
            // Déplacer le dossier existant
            rename($ecid_dir, $corbeille_target);
        }

        // Créer le nouveau dossier ecid
        mkdir($ecid_dir, 0755, true);

        // Déplacer le fichier téléchargé
        $destination = $ecid_dir . "/" . $filename;
        if (move_uploaded_file($uploaded_file, $destination)) {
            // Si c'est un zip, le décompresser
            $zip = new ZipArchive();
            if ($zip->open($destination) === true) {
                $zip->extractTo($ecid_dir);
                $zip->close();
                unlink($destination);
                sendResponse(200, [
                    "message" => "Fichier ZIP reçu et décompressé avec succès",
                    "url" =>
                        "https://" .
                        config("hostname") .
                        "/" .
                        rawurlencode($region) .
                        "/?path=" .
                        rawurlencode("./") .
                        rawurlencode($ecid),
                    "region" => $region,
                    "ecid" => $ecid,
                ]);
            } else {
                sendResponse(500, [
                    "error" => true,
                    "message" => "Erreur lors de l'ouverture du fichier zip",
                ]);
            }
        } else {
            sendResponse(500, [
                "error" => true,
                "message" => "Échec du déplacement du fichier",
            ]);
        }
    } else {
        sendResponse(400, [
            "error" => true,
            "message" => "Erreur lors du téléchargement du fichier",
        ]);
    }
}

function uploadAudit()
{
    // Vérification que les données nécessaires sont présentes
    if (!isset($_POST["region"]) || !isset($_FILES["actual_file"])) {
        sendResponse(400, ["error" => true, "message" => "Données manquantes"]);
    }

    // Gestion spécifique pour la région UHPA
    if ($_POST["region"] === "UHPA") {
        $_POST["region"] = "STRASBOURG";
    }

    $region = $_POST["region"];
    $region_dir = config("project_root_path") . "/" . $region;

    // Vérifiez que le fichier est bien téléchargé
    if ($_FILES["actual_file"]["error"] === UPLOAD_ERR_OK) {
        $uploaded_file = $_FILES["actual_file"]["tmp_name"];
        $filename = $_FILES["actual_file"]["name"];
        $final_file_path = $region_dir . "/" . $filename;

        // Si le répertoire n'existe pas, utiliser "_Région inconnue"
        if (!is_dir($region_dir)) {
            $region = "_Région inconnue";
            $region_dir = config("project_root_path") . "/" . $region;
            $final_file_path = $region_dir . "/" . $filename;

            // Créer le répertoire si nécessaire
            if (!is_dir($region_dir)) {
                mkdir($region_dir, 0755, true);
            }
        }

        // Si le fichier existe déjà, le déplacer dans "CORBEILLE"
        if (file_exists($final_file_path)) {
            $corbeille_dir = $region_dir . "/CORBEILLE";
            if (!is_dir($corbeille_dir)) {
                mkdir($corbeille_dir, 0755, true);
            }
            // Générer un nom unique pour éviter les collisions
            $timestamp = date("Ymd_His");
            $corbeille_target = $corbeille_dir . "/" . pathinfo($filename, PATHINFO_FILENAME) . "_" . $timestamp . "." . pathinfo($filename, PATHINFO_EXTENSION);
            rename($final_file_path, $corbeille_target);
        }

        // Déplacer le nouveau fichier téléchargé
        if (move_uploaded_file($uploaded_file, $final_file_path)) {
            sendResponse(200, [
                "message" => "Fichier audit reçu avec succès",
                "url" =>
                    "https://" .
                    config("hostname") .
                    "/" .
                    rawurlencode($region) .
                    "/" .
                    rawurlencode($filename),
                "region" => $region,
                "file" => $filename,
            ]);
        } else {
            sendResponse(500, [
                "error" => true,
                "message" => "Échec du déplacement du fichier",
                "upload_error_code" => $_FILES["actual_file"]["error"],
            ]);
        }
    } else {
        sendResponse(400, [
            "error" => true,
            "message" => "Erreur lors du téléchargement du fichier",
            "upload_error_code" => $_FILES["actual_file"]["error"],
        ]);
    }
}

function redirSwagger()
{
    header("Content-Type: text/html; charset=UTF-8");
    require_once "swagger.php";
}

/**
 * Vérifie si l'utilisateur a accès à l'application spécifiée
 * @param array $entity Entité authentifiée
 * @param string $appId Identifiant de l'application
 * @return bool True si l'accès est autorisé, false sinon
 */
function hasAppAccess($entity, $appId)
{
    // Les administrateurs (ACL 10+) ont accès à toutes les applications
    if ($entity["acl"] >= 10) {
        return true;
    }

    // Vérifier si l'entité est une application avec une clé API
    if ($entity["type"] === "app") {
        // L'application ne peut modifier que ses propres données
        return $entity["name"] === $appId;
    }

    return false;
}

/**
 * Obtenir le chemin du répertoire de stockage des applications
 * @return string Chemin absolu du répertoire
 */
function getAppsDirectory()
{
    $dir = config("project_root_path") . "/storage/apps";
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

/**
 * Vérifie si l'app existe
 * @param string $appId Identifiant de l'application
 * @return bool True si une app existe dans le fichier de configuration
 */
function isAppExisting($appId)
{
    $apps = config("apps");
    return array_key_exists($appId, $apps);
}

/**
 * Obtenir le chemin du répertoire de stockage d'une application spécifique
 * @param string $appId Identifiant de l'application
 * @return string Chemin absolu du répertoire
 */
function getAppDirectory($appId)
{
    $dir = getAppsDirectory() . "/" . $appId;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

/**
 * Obtenir le chemin du répertoire de stockage des versions d'une application
 * @param string $appId Identifiant de l'application
 * @return string Chemin absolu du répertoire
 */
function getAppVersionsDirectory($appId)
{
    $dir = getAppDirectory($appId) . "/versions";
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

/**
 * Get app description from config file without authentication
 * @param string $appName Name of the app to get description for
 * @return string|null Description of the app or null if not found
 */
function getAppDescriptionFromConfig($appName)
{
    // Load apps configuration
    $apps = config("apps");

    // Check if the app exists in config
    if (isset($apps[$appName])) {
        return $apps[$appName]["description"] ?? null;
    }

    return null;
}

/**
 * Get all app descriptions from config file without authentication
 * @return array Associative array of app names and their descriptions
 */
function getAllAppDescriptionsFromConfig()
{
    // Load apps configuration
    $apps = config("apps");

    $descriptions = [];
    foreach ($apps as $appName => $appInfo) {
        $descriptions[$appName] = $appInfo["description"] ?? null;
    }

    return $descriptions;
}

/**
 * Lister toutes les applications disponibles
 */
function getApps()
{
    $appsDirectory = getAppsDirectory();
    if (!is_dir($appsDirectory)) {
        sendResponse(200, ["apps" => []]);
        return;
    }
    $apps = [];
    $items = scandir($appsDirectory);
    // Get all app descriptions from config
    $appDescriptions = getAllAppDescriptionsFromConfig();
    foreach ($items as $item) {
        // Ignorer "." et ".."
        if ($item === "." || $item === "..") {
            continue;
        }
        $appDir = $appsDirectory . "/" . $item;
        if (is_dir($appDir)) {
            // Lire les informations de base de l'application
            $appInfo = getAppInfo($item);
            if ($appInfo) {
                // Add description from config if available
                if (isset($appDescriptions[$item])) {
                    $appInfo["description"] = $appDescriptions[$item];
                }

                // Ajouter l'URL de téléchargement pour la dernière version
                $appInfo["download_url"] =
                    "https://" .
                    config("hostname") .
                    "/api/apps/" .
                    $item .
                    "/download/latest";

                // Récupérer la taille du fichier de la version actuelle
                $currentVersion = $appInfo["current_version"];
                $versionsDir = getAppVersionsDirectory($item);

                if (is_dir($versionsDir)) {
                    $versionFiles = scandir($versionsDir);
                    foreach ($versionFiles as $file) {
                        if (
                            preg_match(
                                "/^" .
                                    preg_quote($currentVersion, "/") .
                                    '\..*$/',
                                $file
                            )
                        ) {
                            $filePath = $versionsDir . "/" . $file;
                            $appInfo["file_size"] = filesize($filePath);
                            $appInfo["file_size_formatted"] = formatFileSize(
                                filesize($filePath)
                            );
                            break;
                        }
                    }
                }

                $apps[] = $appInfo;
            }
        }
    }
    sendResponse(200, ["apps" => $apps]);
}

/**
 * Afficher la liste de toutes les applications disponibles en format HTML
 */
function getAppsWeb()
{
    $appsDirectory = getAppsDirectory();
    $apps = [];
    // Get all app descriptions from config
    $appDescriptions = getAllAppDescriptionsFromConfig();
    // Si le répertoire existe, récupérer les applications
    if (is_dir($appsDirectory)) {
        $items = scandir($appsDirectory);
        foreach ($items as $item) {
            // Ignorer "." et ".."
            if ($item === "." || $item === "..") {
                continue;
            }
            $appDir = $appsDirectory . "/" . $item;
            if (is_dir($appDir)) {
                $appInfo = getAppInfo($item);
                if ($appInfo) {
                    if (isset($appDescriptions[$item])) {
                        $appInfo["description"] = $appDescriptions[$item];
                    }

                    // Récupérer la taille du fichier de la version actuelle
                    $currentVersion = $appInfo["current_version"];
                    $versionsDir = getAppVersionsDirectory($item);

                    // Rechercher le fichier de la version actuelle
                    if (is_dir($versionsDir)) {
                        $versionFiles = scandir($versionsDir);
                        foreach ($versionFiles as $file) {
                            if (
                                preg_match(
                                    "/^" .
                                        preg_quote($currentVersion, "/") .
                                        '\..*$/',
                                    $file
                                )
                            ) {
                                $filePath = $versionsDir . "/" . $file;
                                $appInfo["file_size"] = filesize($filePath);
                                $appInfo[
                                    "file_size_formatted"
                                ] = formatFileSize(filesize($filePath));
                                break;
                            }
                        }
                    }

                    $apps[] = $appInfo;
                }
            }
        }
    }
    // Trier les applications par nom
    usort($apps, function ($a, $b) {
        return strcmp($a["name"], $b["name"]);
    });
    $appName = config("app_name");
    // Générer la page HTML
    $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des apps — {$appName}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f6f8fa;
        }
        .container {
            padding: 30px;
            background-color: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .page-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .page-title {
            margin: 0;
            font-size: 28px;
            color: #24292e;
        }
        .apps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .app-card {
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 20px;
            background-color: #fff;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .app-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        .app-name {
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 18px;
            color: #0366d6;
        }
        .app-description {
            color: #586069;
            margin-bottom: 15px;
            font-size: 14px;
            height: 65px;
            overflow: hidden;
        }
        .app-version {
            font-size: 14px;
            color: #24292e;
            margin-bottom: 15px;
            padding: 4px 8px;
            background-color: #f1f8ff;
            border-radius: 3px;
            display: inline-block;
        }
        .app-info {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        .app-date, .app-size {
            font-size: 12px;
            color: #6a737d;
            padding: 3px 6px;
            background-color: #f6f8fa;
            border-radius: 3px;
        }
        .app-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .app-button {
            flex: 1;
            padding: 8px 12px;
            text-align: center;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .download-btn {
            background-color: #2ea44f;
            color: white;
        }
        .download-btn:hover {
            background-color: #2c974b;
        }
        .changelog-btn {
            background-color: #0366d6;
            color: white;
        }
        .changelog-btn:hover {
            background-color: #0256b9;
        }
        .no-apps {
            padding: 40px;
            text-align: center;
            color: #6a737d;
            font-style: italic;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Liste des apps • {$appName}</h1>
        </div>
        <div class="apps-grid">
HTML;
    if (empty($apps)) {
        $html .=
            '<div class="no-apps">Aucune application disponible pour le moment.</div>';
    } else {
        foreach ($apps as $app) {
            $updatedDate = date("d/m/Y à H:i", strtotime($app["updated_at"]));
            $downloadUrl =
                "https://" .
                config("hostname") .
                "/api/apps/" .
                $app["id"] .
                "/web/";
            $changelogUrl =
                "https://" .
                config("hostname") .
                "/api/apps/" .
                $app["id"] .
                "/changelog/web";

            // Récupérer la taille du fichier formatée
            $fileSize = isset($app["file_size_formatted"])
                ? $app["file_size_formatted"]
                : "N/A";

            $html .= <<<HTML
            <div class="app-card">
                <h2 class="app-name">{$app["name"]}</h2>
                <div class="app-description">{$app["description"]}</div>
                <div class="app-version">Version {$app["current_version"]}</div>
                <div class="app-info">
                    <span class="app-date"><i class="far fa-calendar-alt"></i> Mise à jour le {$updatedDate}</span>
                    <span class="app-size"><i class="fas fa-file-archive"></i> {$fileSize}</span>
                </div>
                <div class="app-buttons">
                    <a href="{$downloadUrl}"  target="_blank" class="app-button download-btn">
                        <i class="fas fa-download"></i> Télécharger
                    </a>
                    <a href="{$changelogUrl}" target="_blank" class="app-button changelog-btn">
                        <i class="fas fa-list"></i> Changelog
                    </a>
                </div>
            </div>
HTML;
        }
    }
    $html .= <<<HTML
        </div>
    </div>
</body>
</html>
HTML;
    header("Content-Type: text/html; charset=UTF-8");
    echo $html;
}

/**
 * Obtenir les informations d'une application spécifique
 * @param string $appId Identifiant de l'application
 */
function getAppDetails($appId)
{
    $appInfo = getAppInfo($appId);

    if ($appInfo) {
        sendResponse(200, $appInfo);
    } else {
        sendResponse(404, [
            "error" => true,
            "message" => "Application non trouvée",
        ]);
    }
}

/**
 * Obtenir les informations d'une application (méthode interne)
 * @param string $appId Identifiant de l'application
 * @return array|null Informations de l'application ou null si non trouvée
 */
function getAppInfo($appId)
{
    $appDir = getAppDirectory($appId);
    $infoFile = $appDir . "/info.json";

    if (file_exists($infoFile)) {
        $info = json_decode(file_get_contents($infoFile), true);
        return array_merge(["id" => $appId], $info);
    }

    // Si le fichier info.json n'existe pas mais que le répertoire existe,
    // créer un fichier info.json par défaut
    if (is_dir($appDir)) {
        $defaultInfo = [
            "name" => $appId,
            "description" => "Application " . $appId,
            "current_version" => "0.0.0",
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s"),
        ];
        file_put_contents(
            $infoFile,
            json_encode($defaultInfo, JSON_PRETTY_PRINT)
        );
        return array_merge(["id" => $appId], $defaultInfo);
    }

    return null;
}

/**
 * Lister toutes les versions d'une application
 * @param string $appId Identifiant de l'application
 */
function getAppVersions($appId)
{
    // Vérifier que l'application existe
    $appExist = isAppExisting($appId);
    if (!$appExist) {
        sendResponse(404, [
            "error" => true,
            "message" => "Application non trouvée",
        ]);
        return;
    }
    $appInfo = getAppInfo($appId);
    $currentVersion = $appInfo["current_version"];
    $versionsDir = getAppVersionsDirectory($appId);
    $versions = [];
    // Si le répertoire existe, lister les versions
    if (is_dir($versionsDir)) {
        $items = scandir($versionsDir);
        foreach ($items as $item) {
            // Ignorer "." et ".." et les fichiers qui ne sont pas des binaires
            if (
                $item === "." ||
                $item === ".." ||
                !preg_match('/^([0-9]+\.[0-9]+\.[0-9]+)\..*$/', $item, $matches)
            ) {
                continue;
            }
            $version = $matches[1];
            $versionFile = $versionsDir . "/" . $item;

            // Créer l'entrée de version avec tous les détails
            $versionEntry = [
                "version" => $version,
                "filename" => $item,
                "final_filename" => $appId . "-" . $item,
                "size" => filesize($versionFile),
                "size_formatted" => formatFileSize(filesize($versionFile)),
                "uploaded_at" => date("Y-m-d H:i:s", filemtime($versionFile)),
                "download_url" =>
                    "https://" .
                    config("hostname") .
                    "/api/apps/" .
                    $appId .
                    "/download/" .
                    $version,
            ];

            // Ajouter is_latest = true si c'est la version courante
            if ($version === $currentVersion) {
                $versionEntry["is_latest"] = true;
            } else {
                $versionEntry["is_latest"] = false;
            }

            $versions[] = $versionEntry;
        }
    }

    // Retourner les versions
    sendResponse(200, [
        "error" => false,
        "versions" => $versions,
    ]);
}

/**
 * Obtenir la liste des versions d'une application en format HTML
 * @param string $appId Identifiant de l'application
 */
function getAppVersionsWeb($appId)
{
    $appExist = isAppExisting($appId);
    if (!$appExist) {
        sendResponse(404, [
            "error" => true,
            "message" => "Application non trouvée",
        ]);
        return;
    }
    $appInfo = getAppInfo($appId);
    $currentVersion = $appInfo["current_version"];
    $versionsDir = getAppVersionsDirectory($appId);
    $versions = [];

    // Si le répertoire existe, lister les versions
    if (is_dir($versionsDir)) {
        $items = scandir($versionsDir);
        foreach ($items as $item) {
            // Ignorer "." et ".." et les fichiers qui ne sont pas des binaires
            if (
                $item === "." ||
                $item === ".." ||
                !preg_match('/^([0-9]+\.[0-9]+\.[0-9]+)\..*$/', $item, $matches)
            ) {
                continue;
            }
            $version = $matches[1];
            $versionFile = $versionsDir . "/" . $item;
            // Créer l'entrée de version avec tous les détails
            $versionEntry = [
                "version" => $version,
                "filename" => $item,
                "final_filename" => $appId . "-" . $item,
                "size" => filesize($versionFile),
                "size_formatted" => formatFileSize(filesize($versionFile)),
                "uploaded_at" => date("Y-m-d H:i:s", filemtime($versionFile)),
                "download_url" =>
                    "https://" .
                    config("hostname") .
                    "/api/apps/" .
                    $appId .
                    "/download/" .
                    $version,
                "is_latest" => $version === $currentVersion,
            ];
            $versions[] = $versionEntry;
        }
    }

    // Trier les versions par date (plus récent en premier)
    usort($versions, function ($a, $b) {
        return strtotime($b["uploaded_at"]) - strtotime($a["uploaded_at"]);
    });

    $changelog_url =
        "https://" . config("hostname") . "/api/apps/{$appId}/changelog/web";

    // Générer la page HTML
    $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Versions - {$appInfo["name"]}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 980px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f6f8fa;
        }
        .container {
            padding: 30px;
            background-color: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .app-header {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .app-title {
            margin: 0;
            font-size: 24px;
        }
        .app-version {
            color: #666;
            font-size: 16px;
            margin-top: 5px;
        }
        .version-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .version-table th, .version-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .version-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .version-latest {
            background-color: #e8f5e9;
        }
        .badge-latest {
            display: inline-block;
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            background-color: #28a745;
            border-radius: 12px;
            margin-left: 8px;
        }
        .download-btn {
            display: inline-block;
            padding: 5px 10px;
            background-color: #0366d6;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .download-btn:hover {
            background-color: #0256b9;
        }
        .no-versions {
            padding: 30px;
            text-align: center;
            color: #666;
            font-style: italic;
        }
        .app-changelog a {
            background-color: transparent;
            color: color(srgb 0.0382 0.4103 0.8567);
            text-decoration: none;
        }
        .app-changelog a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="app-header">
            <h1 class="app-title">{$appInfo["name"]}</h1>
            <div class="app-version">Version actuelle: {$appInfo["current_version"]}</div>
            <div class="app-changelog"><a href='{$changelog_url}'>Voir le changelog</a></div>
        </div>

        <h2>Historique des versions</h2>

HTML;

    if (empty($versions)) {
        $html .=
            '<div class="no-versions">Aucune version disponible pour cette application.</div>';
    } else {
        $html .= <<<HTML
        <table class="version-table">
            <thead>
                <tr>
                    <th>Version</th>
                    <th>Date de publication</th>
                    <th>Taille</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
HTML;

        foreach ($versions as $version) {
            $latestClass = $version["is_latest"]
                ? ' class="version-latest"'
                : "";
            $latestBadge = $version["is_latest"]
                ? '<span class="badge-latest">ACTUELLE</span>'
                : "";

            $html .= <<<HTML
                <tr{$latestClass}>
                    <td>{$version["version"]}{$latestBadge}</td>
                    <td>{$version["uploaded_at"]}</td>
                    <td>{$version["size_formatted"]}</td>
                    <td>
                        <a href="{$version["download_url"]}" class="download-btn">
                            <i class="fas fa-download"></i> Télécharger
                        </a>
                    </td>
                </tr>
HTML;
        }

        $html .= <<<HTML
            </tbody>
        </table>
HTML;
    }

    $html .= <<<HTML
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tout code JavaScript supplémentaire peut être ajouté ici
        });
    </script>
</body>
</html>
HTML;

    header("Content-Type: text/html; charset=UTF-8");
    echo $html;
}

/**
 * Obtenir la dernière version d'une application
 * @param string $appId Identifiant de l'application
 */
function getLatestAppVersion($appId)
{
    // Vérifier que l'application existe
    $appExist = isAppExisting($appId);
    if (!$appExist) {
        sendResponse(404, [
            "error" => true,
            "message" => "Application non trouvée",
        ]);
        return;
    }

    $appInfo = getAppInfo($appId);

    $currentVersion = $appInfo["current_version"];
    $versionsDir = getAppVersionsDirectory($appId);

    // Chercher le fichier correspondant à la dernière version
    $latestVersionFiles = glob($versionsDir . "/" . $currentVersion . ".*");

    if (empty($latestVersionFiles)) {
        sendResponse(404, [
            "error" => true,
            "message" => "Aucun fichier trouvé pour la dernière version",
        ]);
        return;
    }

    $latestVersionFile = $latestVersionFiles[0];
    $filename = basename($latestVersionFile);

    sendResponse(200, [
        "app" => $appInfo,
        "version" => $currentVersion,
        "filename" => $filename,
        "final_filename" => $appId . "-" . $filename,
        "size" => filesize($latestVersionFile),
        "size_formatted" => formatFileSize(filesize($latestVersionFile)),
        "uploaded_at" => date("Y-m-d H:i:s", filemtime($latestVersionFile)),
        "download_url" =>
            "https://" .
            config("hostname") .
            "/api/apps/" .
            $appId .
            "/download/latest",
    ]);
}

/**
 * Télécharger la dernière version d'une application
 * @param string $appId Identifiant de l'application
 */
function downloadLatestAppVersion($appId)
{
    // Vérifier que l'application existe
    $appExist = isAppExisting($appId);
    if (!$appExist) {
        sendResponse(404, [
            "error" => true,
            "message" => "Application non trouvée",
        ]);
        return;
    }

    $appInfo = getAppInfo($appId);

    $currentVersion = $appInfo["current_version"];
    $versionsDir = getAppVersionsDirectory($appId);

    // Chercher le fichier correspondant à la dernière version
    $latestVersionFiles = glob($versionsDir . "/" . $currentVersion . ".*");

    if (empty($latestVersionFiles)) {
        sendResponse(404, [
            "error" => true,
            "message" => "Aucun fichier trouvé pour la dernière version",
        ]);
        return;
    }

    $latestVersionFile = $latestVersionFiles[0];
    $filename = basename($latestVersionFile);

    // Gestion du téléchargement
    $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    // Définir le type MIME approprié selon l'extension, avec une valeur par défaut
    $mimeTypes = getMimeTypes();
    $contentType = isset($mimeTypes[$fileExtension])
        ? $mimeTypes[$fileExtension]
        : "application/octet-stream";

    // sendResponse(404, [
    //     "error" => true,
    //     "mimeTypes" => "$mimeTypes",
    //     "message" => "$fileExtension",
    //     "contentType" => "$contentType",
    // ]);

    # Envoyer les en-têtes
    header("Content-Type: " . $contentType);
    header(
        'Content-Disposition: attachment; filename="' .
            $appId .
            "-" .
            $filename .
            '"'
    );
    header("Content-Length: " . filesize($latestVersionFile));
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");

    readfile($latestVersionFile);
    exit();
}

/**
 * Télécharger une version spécifique d'une application
 * @param string $appId Identifiant de l'application
 * @param string $version Numéro de version à télécharger
 */
function downloadSpecificAppVersion($appId, $version)
{
    // Vérifier que l'application existe
    $appExist = isAppExisting($appId);
    if (!$appExist) {
        sendResponse(404, [
            "error" => true,
            "message" => "Application non trouvée",
        ]);
        return;
    }

    $appInfo = getAppInfo($appId);

    $versionsDir = getAppVersionsDirectory($appId);

    // Chercher le fichier correspondant à la version spécifiée
    $versionFiles = glob($versionsDir . "/" . $version . ".*");

    if (empty($versionFiles)) {
        sendResponse(404, [
            "error" => true,
            "message" => "Version spécifiée non trouvée",
        ]);
        return;
    }

    $versionFile = $versionFiles[0];
    $filename = basename($versionFile);

    // Gestion du téléchargement
    $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    // Définir le type MIME approprié selon l'extension, avec une valeur par défaut
    $mimeTypes = getMimeTypes();
    $contentType = isset($mimeTypes[$fileExtension])
        ? $mimeTypes[$fileExtension]
        : "application/octet-stream";

    // Envoyer les en-têtes
    header("Content-Type: " . $contentType);
    header(
        'Content-Disposition: attachment; filename="' .
            $appId .
            "-" .
            $filename .
            '"'
    );
    header("Content-Length: " . filesize($versionFile));
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");

    readfile($versionFile);
    exit();
}

/**
 * Obtenir le changelog d'une application
 * @param string $appId Identifiant de l'application
 */
function getAppChangelog($appId)
{
    $appExist = isAppExisting($appId);
    if (!$appExist) {
        sendResponse(404, [
            "error" => true,
            "message" => "Application non trouvée",
        ]);
        return;
    }

    $appInfo = getAppInfo($appId);

    $changelogFile = getAppDirectory($appId) . "/changelog.md";

    if (!file_exists($changelogFile)) {
        sendResponse(200, [
            "app" => $appInfo,
            "changelog" =>
                "# Changelog pour " .
                $appId .
                '\n\nAucun changement enregistré.',
        ]);
        return;
    }

    $changelog = file_get_contents($changelogFile);

    sendResponse(200, [
        "app" => $appInfo,
        "changelog" => $changelog,
    ]);
}

/**
 * Obtenir le changelog d'une application
 * @param string $appId Identifiant de l'application
 */
function getAppChangelogRaw($appId)
{
    $appExist = isAppExisting($appId);
    if (!$appExist) {
        sendResponse(404, [
            "error" => true,
            "message" => "Application non trouvée",
        ]);
        return;
    }

    $appInfo = getAppInfo($appId);

    $changelogFile = getAppDirectory($appId) . "/changelog.md";

    if (!file_exists($changelogFile)) {
        sendResponse(200, [
            "app" => $appInfo,
            "changelog" =>
                "# Changelog pour " .
                $appId .
                '\n\nAucun changement enregistré.',
        ]);
        return;
    }

    $changelog = file_get_contents($changelogFile);
    header("Content-Type: text/html; charset=UTF-8");
    echo $changelog;
}

/**
 * Obtenir le changelog d'une application en format HTML avec parseur Markdown
 * @param string $appId Identifiant de l'application
 */
function getAppChangelogWeb($appId)
{
    $appExist = isAppExisting($appId);
    if (!$appExist) {
        sendResponse(404, [
            "error" => true,
            "message" => "Application non trouvée",
        ]);
        return;
    }

    $appInfo = getAppInfo($appId);
    $changelogFile = getAppDirectory($appId) . "/changelog.md";

    if (!file_exists($changelogFile)) {
        $changelog =
            "# Changelog pour " . $appId . "\n\nAucun changement enregistré.";
    } else {
        $changelog = file_get_contents($changelogFile);
    }

    // Échapper le contenu pour l'inclure en toute sécurité dans JavaScript
    $escapedChangelog = htmlspecialchars($changelog, ENT_QUOTES, "UTF-8");

    $versions_url = "https://" . config("hostname") . "/api/apps/{$appId}/web";

    // Générer la page HTML avec le parseur Markdown (marked.js)
    $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changelog - {$appInfo["name"]}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.2.0/github-markdown.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 980px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f6f8fa;
        }
        .container {
            padding: 30px;
            background-color: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .app-header {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .app-title {
            margin: 0;
            font-size: 24px;
        }
        .app-version {
            color: #666;
            font-size: 16px;
            margin-top: 5px;
        }
        .app-changelog a {
            background-color: transparent;
            color: color(srgb 0.0382 0.4103 0.8567);
            text-decoration: none;
        }
        .app-changelog a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="app-header">
            <h1 class="app-title">{$appInfo["name"]}</h1>
            <div class="app-version">Version actuelle: {$appInfo["current_version"]}</div>
            <div class="app-changelog"><a href='{$versions_url}'>Voir les téléchargements</a></div>
        </div>
        <div id="changelog" class="markdown-body"></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/4.3.0/marked.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuration du parseur marked.js
            marked.setOptions({
                gfm: true,
                breaks: true,
                headerIds: true,
                highlight: function (code, lang) {
                    return code;
                }
            });

            // Contenu Markdown du changelog
            const markdownContent = `{$escapedChangelog}`;

            // Convertir le Markdown en HTML et l'insérer dans la page
            document.getElementById('changelog').innerHTML = marked.parse(markdownContent);
        });
    </script>
</body>
</html>
HTML;

    header("Content-Type: text/html; charset=UTF-8");
    echo $html;
}

/**
 * Mettre à jour le numéro de version d'une application
 * @param array $user Utilisateur authentifié
 * @param string $appId Identifiant de l'application
 */
function updateAppVersion($user, $appId)
{
    // Vérifier l'accès à l'application
    if (!hasAppAccess($user, $appId)) {
        sendResponse(403, [
            "error" => true,
            "message" => "Accès non autorisé à cette application",
        ]);
        return;
    }

    // Vérifier que l'application existe
    $appExist = isAppExisting($appId);
    if (!$appExist) {
        sendResponse(404, [
            "error" => true,
            "message" => "Application non trouvée",
        ]);
        return;
    }

    $appInfo = getAppInfo($appId);

    // Récupérer les données JSON de la requête
    $requestData = json_decode(file_get_contents("php://input"), true);

    // Vérifier que la version est spécifiée
    if (!isset($requestData["version"])) {
        sendResponse(400, [
            "error" => true,
            "message" => "Numéro de version non spécifié",
        ]);
        return;
    }

    $newVersion = $requestData["version"];

    // Vérifier que la version est au format valide (x.y.z)
    if (!preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $newVersion)) {
        sendResponse(400, [
            "error" => true,
            "message" => "Format de version invalide, doit être x.y.z",
        ]);
        return;
    }

    // Mettre à jour les informations de l'application
    $appInfo["current_version"] = $newVersion;
    $appInfo["updated_at"] = date("Y-m-d H:i:s");

    // Sauvegarder les modifications
    $infoFile = getAppDirectory($appId) . "/info.json";
    file_put_contents($infoFile, json_encode($appInfo, JSON_PRETTY_PRINT));

    // Retourner les informations mises à jour
    sendResponse(200, [
        "message" => "Version mise à jour avec succès",
        "app" => array_merge(["id" => $appId], $appInfo),
    ]);
}

/**
 * Mettre à jour le changelog d'une application
 * @param array $user Utilisateur authentifié
 * @param string $appId Identifiant de l'application
 */
function updateAppChangelog($user, $appId)
{
    // Vérifier l'accès à l'application
    if (!hasAppAccess($user, $appId)) {
        sendResponse(403, [
            "error" => true,
            "message" => "Accès non autorisé à cette application",
        ]);
        return;
    }

    // Vérifier que l'application existe
    $appExist = isAppExisting($appId);
    if (!$appExist) {
        sendResponse(404, [
            "error" => true,
            "message" => "Application non trouvée",
        ]);
        return;
    }

    $appInfo = getAppInfo($appId);

    // Récupérer les données JSON de la requête
    $requestData = json_decode(file_get_contents("php://input"), true);

    // Vérifier que le changelog est spécifié
    if (!isset($requestData["changelog"])) {
        sendResponse(400, [
            "error" => true,
            "message" => "Contenu du changelog non spécifié",
        ]);
        return;
    }

    $changelog = $requestData["changelog"];

    // Sauvegarder le changelog
    $changelogFile = getAppDirectory($appId) . "/changelog.md";
    file_put_contents($changelogFile, $changelog);

    // Mettre à jour la date de modification de l'application
    $appInfo["updated_at"] = date("Y-m-d H:i:s");
    $infoFile = getAppDirectory($appId) . "/info.json";
    file_put_contents($infoFile, json_encode($appInfo, JSON_PRETTY_PRINT));

    // Retourner les informations mises à jour
    sendResponse(200, [
        "message" => "Changelog mis à jour avec succès",
        "app" => array_merge(["id" => $appId], $appInfo),
        "changelog" => $changelog,
    ]);
}

/**
 * Uploader un fichier changelog pour une application
 * @param array $user Utilisateur authentifié
 * @param string $appId Identifiant de l'application
 */
function uploadAppChangelog($user, $appId)
{
    // Vérifier l'accès à l'application
    if (!hasAppAccess($user, $appId)) {
        sendResponse(403, [
            "error" => true,
            "message" => "Accès non autorisé à cette application",
        ]);
        return;
    }

    // Vérifier que l'application existe
    $appExist = isAppExisting($appId);
    if (!$appExist) {
        sendResponse(404, [
            "error" => true,
            "message" => "Application non trouvée",
        ]);
        return;
    }

    $appInfo = getAppInfo($appId);

    // Vérifier qu'un fichier a bien été envoyé
    if (
        !isset($_FILES["changelog_file"]) ||
        $_FILES["changelog_file"]["error"] !== UPLOAD_ERR_OK
    ) {
        sendResponse(400, [
            "error" => true,
            "message" => "Aucun fichier changelog valide n'a été envoyé",
        ]);
        return;
    }

    // Vérifier le type de fichier (optionnel)
    $fileType = mime_content_type($_FILES["changelog_file"]["tmp_name"]);
    $allowedTypes = ["text/plain", "text/markdown", "text/x-markdown"];

    if (
        !in_array($fileType, $allowedTypes) &&
        !preg_match('/\.md$/i', $_FILES["changelog_file"]["name"])
    ) {
        sendResponse(400, [
            "error" => true,
            "message" =>
                "Le fichier doit être au format texte ou markdown (.md)",
        ]);
        return;
    }

    // Lire le contenu du fichier
    $changelog = file_get_contents($_FILES["changelog_file"]["tmp_name"]);

    // Sauvegarder le changelog
    $changelogFile = getAppDirectory($appId) . "/changelog.md";
    file_put_contents($changelogFile, $changelog);

    // Mettre à jour la date de modification de l'application
    $appInfo["updated_at"] = date("Y-m-d H:i:s");
    $infoFile = getAppDirectory($appId) . "/info.json";
    file_put_contents($infoFile, json_encode($appInfo, JSON_PRETTY_PRINT));

    // Retourner les informations mises à jour
    sendResponse(200, [
        "message" => "Fichier changelog uploadé avec succès",
        "app" => array_merge(["id" => $appId], $appInfo),
        "changelog_size" => strlen($changelog),
        "filename" => $_FILES["changelog_file"]["name"],
    ]);
}

/**
 * Télécharger une nouvelle version d'application
 * @param array $user Utilisateur authentifié
 * @param string $appId Identifiant de l'application
 */
function uploadAppVersion($user, $appId)
{
    // Vérifier l'accès à l'application
    if (!hasAppAccess($user, $appId)) {
        sendResponse(403, [
            "error" => true,
            "message" => "Accès non autorisé à cette application",
        ]);
        return;
    }

    // Vérifier que l'application existe
    $appExist = isAppExisting($appId);
    if (!$appExist) {
        sendResponse(404, [
            "error" => true,
            "message" => "Application non trouvée",
        ]);
        return;
    }

    $appInfo = getAppInfo($appId);

    // Vérifier que les données nécessaires sont présentes
    if (!isset($_POST["version"]) || !isset($_FILES["file"])) {
        sendResponse(400, [
            "error" => true,
            "message" => "Données manquantes (version ou fichier)",
        ]);
        return;
    }

    $version = $_POST["version"];

    // Vérifier que la version est au format valide (x.y.z)
    if (!preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $version)) {
        sendResponse(400, [
            "error" => true,
            "message" => "Format de version invalide, doit être x.y.z",
        ]);
        return;
    }

    // Vérifier que le fichier est bien téléchargé
    if ($_FILES["file"]["error"] === UPLOAD_ERR_OK) {
        $uploaded_file = $_FILES["file"]["tmp_name"];
        $original_filename = $_FILES["file"]["name"];
        $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);

        // Créer le nom du fichier de version
        $version_filename = $version . "." . $file_extension;
        $versionsDir = getAppVersionsDirectory($appId);
        $destination = $versionsDir . "/" . $version_filename;

        // Déplacer le fichier téléchargé
        if (move_uploaded_file($uploaded_file, $destination)) {
            // Mettre à jour la version courante si spécifié
            // $updateCurrentVersion =
            //     isset($_POST["set_as_current"]) &&
            //     $_POST["set_as_current"] === "true";
            $updateCurrentVersion = true;

            if (
                $updateCurrentVersion ||
                version_compare($version, $appInfo["current_version"]) > 0
            ) {
                $appInfo["current_version"] = $version;
                $appInfo["updated_at"] = date("Y-m-d H:i:s");

                // Sauvegarder les modifications
                $infoFile = getAppDirectory($appId) . "/info.json";
                file_put_contents(
                    $infoFile,
                    json_encode($appInfo, JSON_PRETTY_PRINT)
                );
            }

            sendResponse(200, [
                "message" => "Version téléchargée avec succès",
                "app" => array_merge(["id" => $appId], $appInfo),
                "version" => $version,
                "filename" => $version_filename,
                "download_url" =>
                    "https://" .
                    config("hostname") .
                    "/api/apps/" .
                    $appId .
                    "/download/" .
                    $version,
            ]);
        } else {
            sendResponse(500, [
                "error" => true,
                "message" => "Échec du déplacement du fichier",
            ]);
        }
    } else {
        sendResponse(400, [
            "error" => true,
            "message" => "Erreur lors du téléchargement du fichier",
            "upload_error_code" => $_FILES["file"]["error"],
        ]);
    }
}

function getApiDocumentation()
{
    // Générer une documentation au format OpenAPI
    $swagger = [
        "openapi" => "3.0.0",
        "info" => [
            "title" => config("app_name") . " API",
            "version" => config("version"),
            "description" =>
                "API pour gérer les audits et fichiers par région, ainsi que les endpoints de mise à jour (changelog, version...) pour les apps gérant les audits",
        ],
        "servers" => [
            [
                "url" => "https://" . config("hostname") . "/api",
                "description" => "Serveur principal",
            ],
        ],
        "paths" => [
            "/regions" => [
                "get" => [
                    "tags" => ["audits"],
                    "summary" => "Liste toutes les régions",
                    "responses" => [
                        "200" => [
                            "description" => "Liste des régions",
                            "content" => [
                                "application/json" => [
                                    "schema" => [
                                        "type" => "object",
                                        "properties" => [
                                            "regions" => [
                                                "type" => "object",
                                                "additionalProperties" => [
                                                    "type" => "string",
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    "security" => [], // Route publique
                ],
            ],
            "/regions/{regionCode}" => [
                "get" => [
                    "tags" => ["audits"],
                    "summary" => 'Liste tous les audits d\'une région',
                    "parameters" => [
                        [
                            "name" => "regionCode",
                            "in" => "path",
                            "required" => true,
                            "schema" => ["type" => "string"],
                            "description" => "Code de la région (2 caractères)",
                        ],
                    ],
                    "responses" => [
                        "200" => [
                            "description" => "Liste des audits de la région",
                            "content" => [
                                "application/json" => [
                                    "schema" => [
                                        "type" => "object",
                                        "properties" => [
                                            "region" => [
                                                "type" => "object",
                                                "properties" => [
                                                    "code" => [
                                                        "type" => "string",
                                                    ],
                                                    "name" => [
                                                        "type" => "string",
                                                    ],
                                                ],
                                            ],
                                            "audits_count" => [
                                                "type" => "integer",
                                            ],
                                            "audits" => [
                                                "type" => "array",
                                                "items" => [
                                                    "type" => "object",
                                                    "properties" => [
                                                        "ecid" => [
                                                            "type" => "string",
                                                        ],
                                                        "modified_date" => [
                                                            "type" => "string",
                                                            "format" =>
                                                                "date-time",
                                                        ],
                                                        "files" => [
                                                            "type" => "array",
                                                            "items" => [
                                                                "type" =>
                                                                    "object",
                                                                "properties" => [
                                                                    "name" => [
                                                                        "type" =>
                                                                            "string",
                                                                    ],
                                                                    // 'size' => ['type' => 'integer'],
                                                                    // 'size_formatted' => ['type' => 'string'],
                                                                    "type" => [
                                                                        "type" =>
                                                                            "string",
                                                                        "enum" => [
                                                                            "file",
                                                                            "directory",
                                                                        ],
                                                                    ],
                                                                    "modified_date" => [
                                                                        "type" =>
                                                                            "string",
                                                                        "format" =>
                                                                            "date-time",
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        "403" => [
                            "description" =>
                                "Accès non autorisé à cette région",
                        ],
                        "404" => ["description" => "Région non trouvée"],
                    ],
                    "security" => [], // Route publique
                ],
            ],
            "/upload/zip" => [
                "post" => [
                    "tags" => ["audits"],
                    "summary" => "Envoyer un audit au format ZIP",
                    "requestBody" => [
                        "content" => [
                            "multipart/form-data" => [
                                "schema" => [
                                    "type" => "object",
                                    "properties" => [
                                        "ecid" => [
                                            "type" => "string",
                                            "description" =>
                                                "ID EC avec préfixe de région (ex: STPC29-0000)",
                                        ],
                                        "actual_file" => [
                                            "type" => "string",
                                            "format" => "binary",
                                            "description" =>
                                                "Fichier ZIP à envoyer",
                                        ],
                                    ],
                                    "required" => ["ecid", "actual_file"],
                                ],
                            ],
                        ],
                    ],
                    "responses" => [
                        "200" => [
                            "description" =>
                                "Fichier envoyé et extrait avec succès",
                        ],
                        "400" => ["description" => "Requête invalide"],
                        "403" => ["description" => "Accès non autorisé"],
                        "500" => ["description" => "Erreur serveur"],
                    ],
                    "security" => [["ApiKeyAuth" => []]],
                ],
            ],
            "/upload/audit" => [
                "post" => [
                    "tags" => ["audits"],
                    "summary" => "Envoyer un audit au format HTML",
                    "requestBody" => [
                        "content" => [
                            "multipart/form-data" => [
                                "schema" => [
                                    "type" => "object",
                                    "properties" => [
                                        "region" => [
                                            "type" => "string",
                                            "description" =>
                                                "Nom de la région (ex: STRASBOURG)",
                                        ],
                                        "actual_file" => [
                                            "type" => "string",
                                            "format" => "binary",
                                            "description" =>
                                                "Fichier audit HTML à envoyer",
                                        ],
                                    ],
                                    "required" => ["region", "actual_file"],
                                ],
                            ],
                        ],
                    ],
                    "responses" => [
                        "200" => [
                            "description" => "Fichier envoyé avec succès",
                            "content" => [
                                "application/json" => [
                                    "schema" => [
                                        "type" => "object",
                                        "properties" => [
                                            "message" => ["type" => "string"],
                                            "url" => ["type" => "string"],
                                            "region" => ["type" => "string"],
                                            "file" => ["type" => "string"],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        "400" => [
                            "description" =>
                                "Requête invalide ou aucun fichier envoyé",
                        ],
                        "403" => [
                            "description" =>
                                "Accès non autorisé à cette région",
                        ],
                        "500" => [
                            "description" =>
                                "Erreur lors du traitement du fichier",
                        ],
                    ],
                    "security" => [["ApiKeyAuth" => []]],
                ],
            ],
            "/apps" => [
                "get" => [
                    "tags" => ["apps"],
                    "summary" => "Liste toutes les applications",
                    "responses" => [
                        "200" => [
                            "description" => "Liste des applications",
                            "content" => [
                                "application/json" => [
                                    "schema" => [
                                        "type" => "object",
                                        "properties" => [
                                            "apps" => [
                                                "type" => "array",
                                                "items" => [
                                                    "type" => "object",
                                                    "properties" => [
                                                        "id" => [
                                                            "type" => "string",
                                                        ],
                                                        "name" => [
                                                            "type" => "string",
                                                        ],
                                                        "description" => [
                                                            "type" => "string",
                                                        ],
                                                        "current_version" => [
                                                            "type" => "string",
                                                        ],
                                                        "created_at" => [
                                                            "type" => "string",
                                                            "format" =>
                                                                "date-time",
                                                        ],
                                                        "updated_at" => [
                                                            "type" => "string",
                                                            "format" =>
                                                                "date-time",
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    "security" => [], // Route publique
                ],
            ],
            "/apps/web" => [
                "get" => [
                    "tags" => ["apps"],
                    "summary" =>
                        "Lister toutes les application dans une page web",
                    "parameters" => [],
                    "responses" => [
                        "200" => [
                            "description" =>
                                'Page HTML de toutes les applications, avec un bouton de téléchargement et de changelog, pour l\'utilisateur final',
                        ],
                        "404" => ["description" => "Application non trouvée"],
                    ],
                    "security" => [], // Route publique
                ],
            ],
            "/apps/{appId}" => [
                "get" => [
                    "tags" => ["apps"],
                    "summary" =>
                        'Lister toutes les versions d\'une application',
                    "parameters" => [
                        [
                            "name" => "appId",
                            "in" => "path",
                            "required" => true,
                            "schema" => ["type" => "string"],
                            "description" => 'Identifiant de l\'application',
                        ],
                    ],
                    "responses" => [
                        "200" => [
                            "description" =>
                                'Liste des versions de l\'application',
                        ],
                        "404" => ["description" => "Application non trouvée"],
                    ],
                    "security" => [], // Route publique
                ],
            ],
            "/apps/{appId}/web" => [
                "get" => [
                    "tags" => ["apps"],
                    "summary" =>
                        'Lister toutes les versions d\'une application dans une page web',
                    "parameters" => [
                        [
                            "name" => "appId",
                            "in" => "path",
                            "required" => true,
                            "schema" => ["type" => "string"],
                            "description" => 'Identifiant de l\'application',
                        ],
                    ],
                    "responses" => [
                        "200" => [
                            "description" =>
                                'Page HTML de liste des versions pour l\'utilisateur final',
                        ],
                        "404" => ["description" => "Application non trouvée"],
                    ],
                    "security" => [], // Route publique
                ],
            ],
            "/apps/{appId}/upload" => [
                "post" => [
                    "tags" => ["apps"],
                    "summary" => 'Envoyer une nouvelle version d\'application',
                    "parameters" => [
                        [
                            "name" => "appId",
                            "in" => "path",
                            "required" => true,
                            "schema" => ["type" => "string"],
                            "description" => 'Identifiant de l\'application',
                        ],
                    ],
                    "requestBody" => [
                        "content" => [
                            "multipart/form-data" => [
                                "schema" => [
                                    "type" => "object",
                                    "properties" => [
                                        "version" => [
                                            "type" => "string",
                                            "description" =>
                                                "Numéro de version (format x.y.z)",
                                        ],
                                        "file" => [
                                            "type" => "string",
                                            "format" => "binary",
                                            "description" =>
                                                'Fichier de l\'application',
                                        ],
                                        // "set_as_current" => [
                                        //     "type" => "boolean",
                                        //     "description" =>
                                        //         "Définir comme version courante",
                                        // ],
                                    ],
                                    "required" => ["version", "file"],
                                ],
                            ],
                        ],
                    ],
                    "responses" => [
                        "200" => [
                            "description" => "Version téléchargée avec succès",
                        ],
                        "400" => ["description" => "Requête invalide"],
                        "403" => ["description" => "Accès non autorisé"],
                        "404" => ["description" => "Application non trouvée"],
                    ],
                    "security" => [["ApiKeyAuth" => []]],
                ],
            ],
            "/apps/{appId}/latest" => [
                "get" => [
                    "tags" => ["apps"],
                    "summary" =>
                        'Obtenir les informations à propos de la dernière version d\'une application',
                    "parameters" => [
                        [
                            "name" => "appId",
                            "in" => "path",
                            "required" => true,
                            "schema" => ["type" => "string"],
                            "description" => 'Identifiant de l\'application',
                        ],
                    ],
                    "responses" => [
                        "200" => [
                            "description" =>
                                'Détails de la dernière version de l\'application',
                        ],
                        "404" => ["description" => "Application non trouvée"],
                    ],
                    "security" => [], // Route publique
                ],
            ],
            "/apps/{appId}/download/latest" => [
                "get" => [
                    "tags" => ["apps"],
                    "summary" =>
                        'Télécharger la dernière version d\'une application',
                    "parameters" => [
                        [
                            "name" => "appId",
                            "in" => "path",
                            "required" => true,
                            "schema" => ["type" => "string"],
                            "description" => 'Identifiant de l\'application',
                        ],
                    ],
                    "responses" => [
                        "200" => [
                            "description" => "Fichier téléchargé avec succès",
                        ],
                        "404" => ["description" => "Application non trouvée"],
                    ],
                    "security" => [], // Route publique
                ],
            ],
            "/apps/{appId}/download/{version}" => [
                "get" => [
                    "tags" => ["apps"],
                    "summary" =>
                        'Télécharger une version spécifique d\'une application',
                    "parameters" => [
                        [
                            "name" => "appId",
                            "in" => "path",
                            "required" => true,
                            "schema" => ["type" => "string"],
                            "description" => 'Identifiant de l\'application',
                        ],
                        [
                            "name" => "version",
                            "in" => "path",
                            "required" => true,
                            "schema" => ["type" => "string"],
                            "description" => "Numéro de version (format x.y.z)",
                        ],
                    ],
                    "responses" => [
                        "200" => [
                            "description" => "Fichier téléchargé avec succès",
                        ],
                        "404" => ["description" => "Application non trouvée"],
                    ],
                    "security" => [], // Route publique
                ],
            ],
            "/apps/{appId}/changelog" => [
                "post" => [
                    "tags" => ["apps • changelog"],
                    "summary" =>
                        "Envoyer et mettre à jour le changelog via un fichier",
                    "parameters" => [
                        [
                            "name" => "appId",
                            "in" => "path",
                            "required" => true,
                            "schema" => ["type" => "string"],
                            "description" => "Identifiant de l'application",
                        ],
                    ],
                    "requestBody" => [
                        "content" => [
                            "multipart/form-data" => [
                                "schema" => [
                                    "type" => "object",
                                    "properties" => [
                                        "changelog_file" => [
                                            "type" => "string",
                                            "format" => "binary",
                                            "description" =>
                                                "Fichier changelog au format texte ou markdown (.md)",
                                        ],
                                    ],
                                    "required" => ["changelog_file"],
                                ],
                            ],
                        ],
                        "description" =>
                            "Le fichier changelog à envoyer (format texte ou markdown recommandé)",
                    ],
                    "responses" => [
                        "200" => [
                            "description" =>
                                "Fichier changelog uploadé avec succès",
                            "content" => [
                                "application/json" => [
                                    "schema" => [
                                        "type" => "object",
                                        "properties" => [
                                            "message" => ["type" => "string"],
                                            "app" => [
                                                "type" => "object",
                                                "properties" => [
                                                    "id" => [
                                                        "type" => "string",
                                                    ],
                                                    "name" => [
                                                        "type" => "string",
                                                    ],
                                                    "description" => [
                                                        "type" => "string",
                                                    ],
                                                    "current_version" => [
                                                        "type" => "string",
                                                    ],
                                                    "created_at" => [
                                                        "type" => "string",
                                                        "format" => "date-time",
                                                    ],
                                                    "updated_at" => [
                                                        "type" => "string",
                                                        "format" => "date-time",
                                                    ],
                                                ],
                                            ],
                                            "changelog_size" => [
                                                "type" => "integer",
                                            ],
                                            "filename" => ["type" => "string"],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        "400" => [
                            "description" =>
                                "Aucun fichier changelog valide n'a été envoyé",
                        ],
                        "403" => [
                            "description" =>
                                "Accès non autorisé à cette application",
                        ],
                        "404" => ["description" => "Application non trouvée"],
                    ],
                    "security" => [["ApiKeyAuth" => []]],
                ],
                "put" => [
                    "tags" => ["apps • changelog"],
                    "summary" =>
                        "Envoyer et mettre à jour le changelog via un objet JSON",
                    "parameters" => [
                        [
                            "name" => "appId",
                            "in" => "path",
                            "required" => true,
                            "schema" => ["type" => "string"],
                            "description" => 'Identifiant de l\'application',
                        ],
                    ],
                    "requestBody" => [
                        "content" => [
                            "application/json" => [
                                "schema" => [
                                    "type" => "object",
                                    "properties" => [
                                        "changelog" => [
                                            "type" => "string",
                                            "description" =>
                                                "Nouveau changelog entier (prend en charge du texte multiligne avec Markdown)",
                                            "example" =>
                                                "# Version 2.0.0\n\n## Nouvelles fonctionnalités\n- Ajout de l'authentification OAuth\n- Interface utilisateur redessinée\n\n## Corrections de bugs\n- Correction du problème de chargement sur iOS",
                                        ],
                                    ],
                                    "required" => ["changelog"],
                                ],
                                "examples" => [
                                    "markdown_changelog" => [
                                        "summary" =>
                                            "Exemple de changelog au format Markdown",
                                        "value" => [
                                            "changelog" =>
                                                "# Version 2.0.0\n\n## Nouvelles fonctionnalités\n- Ajout de l'authentification OAuth\n- Interface utilisateur redessinée\n\n## Corrections de bugs\n- Correction du problème de chargement sur iOS",
                                        ],
                                    ],
                                    "simple_changelog" => [
                                        "summary" =>
                                            "Exemple de changelog simple",
                                        "value" => [
                                            "changelog" =>
                                                "Version 2.0.0\n\nNouvelles fonctionnalités:\n- Authentification OAuth\n- UI redesignée\n\nBugfixes:\n- Problème iOS résolu",
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        "description" =>
                            "Le changelog doit être fourni au format texte, peut inclure du Markdown pour un meilleur formatage",
                    ],
                    "responses" => [
                        "200" => [
                            "description" => "Changelog mise à jour",
                        ],
                        "400" => [
                            "description" =>
                                "Contenu du changelog non spécifié",
                        ],
                        "403" => [
                            "description" =>
                                "Accès non autorisé à cette application",
                        ],
                        "404" => ["description" => "Application non trouvée"],
                    ],
                    "security" => [["ApiKeyAuth" => []]],
                ],
                "get" => [
                    "tags" => ["apps • changelog"],
                    "summary" =>
                        'Obtenir le changelog d\'une application en JSON',
                    "parameters" => [
                        [
                            "name" => "appId",
                            "in" => "path",
                            "required" => true,
                            "schema" => ["type" => "string"],
                            "description" => 'Identifiant de l\'application',
                        ],
                    ],
                    "responses" => [
                        "200" => [
                            "description" => 'Changelog de l\'application',
                        ],
                        "404" => ["description" => "Application non trouvée"],
                    ],
                    "security" => [], // Route publique
                ],
            ],
            "/apps/{appId}/changelog/web" => [
                "get" => [
                    "tags" => ["apps • changelog"],
                    "summary" => "Afficher le changelog en HTML pour le web",
                    "parameters" => [
                        [
                            "name" => "appId",
                            "in" => "path",
                            "required" => true,
                            "schema" => ["type" => "string"],
                            "description" => 'Identifiant de l\'application',
                        ],
                    ],
                    "responses" => [
                        "200" => [
                            "description" => "Page HTML",
                        ],
                        "404" => ["description" => "Application non trouvée"],
                    ],
                    "security" => [], // Route publique
                ],
            ],
            "/apps/{appId}/changelog/raw" => [
                "get" => [
                    "tags" => ["apps • changelog"],
                    "summary" =>
                        'Obtenir le changelog d\'une application en texte brut',
                    "parameters" => [
                        [
                            "name" => "appId",
                            "in" => "path",
                            "required" => true,
                            "schema" => ["type" => "string"],
                            "description" => 'Identifiant de l\'application',
                        ],
                    ],
                    "responses" => [
                        "200" => [
                            "description" =>
                                "Contenue du fichier changelog brute (txt où md)",
                        ],
                        "404" => ["description" => "Application non trouvée"],
                    ],
                    "security" => [], // Route publique
                ],
            ],
            "/cpu" => [
                "get" => [
                    "tags" => ["benchmark"],
                    "summary" => "Récupère les données CPU en JSON",
                    "description" =>
                        "Ne pas utiliser la fonction Try It Out (crash de la page), appeler la page depuis votre navigateur directement pour tester. (eg: https://" .
                        config("hostname") .
                        "/api/cpu )",
                    "responses" => [
                        "200" => [
                            "description" => "Liste des benchmarks CPU",
                            "content" => [
                                "application/json" => [
                                    "schema" => [
                                        "type" => "array",
                                        "items" => [
                                            "type" => "object",
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    "security" => [],
                ],
            ],
            "/cpu/csv" => [
                "get" => [
                    "tags" => ["benchmark"],
                    "summary" => "Récupère les données CPU en CSV",
                    "description" =>
                        "Ne pas utiliser la fonction Try It Out (crash de la page), appeler la page depuis votre navigateur directement pour tester. (eg: https://" .
                        config("hostname") .
                        "/api/cpu/csv )",
                    "x-swagger-ui-disable-try-it-out" => "true",
                    "responses" => [
                        "200" => [
                            "description" => "Fichier CSV des benchmarks CPU",
                            "content" => [
                                "text/csv" => [
                                    "schema" => [
                                        "type" => "string",
                                        "format" => "binary",
                                    ],
                                ],
                            ],
                        ],
                    ],
                    "security" => [],
                ],
            ],
            "/version" => [
                "get" => [
                    "tags" => ["api"],
                    "summary" => 'Retourne la version actuelle de l\'API',
                    "description" => "",
                    "responses" => [
                        "200" => [
                            "description" =>
                                'Numéro de version actuelle de l\'API',
                        ],
                    ],
                ],
            ],
            "/logs" => [
                "get" => [
                    "tags" => ["api"],
                    "summary" => 'Consulter les logs d\'utilisation de l\'API',
                    "description" =>
                        "Accès réservé aux administrateurs (ACL 10+)",
                    "parameters" => [
                        [
                            "name" => "limit",
                            "in" => "query",
                            "required" => false,
                            "schema" => ["type" => "integer", "default" => 100],
                            "description" =>
                                'Nombre maximum d\'entrées à retourner',
                        ],
                        [
                            "name" => "filter",
                            "in" => "query",
                            "required" => false,
                            "schema" => ["type" => "string"],
                            "description" =>
                                "Filtre textuel sur les entrées de log",
                        ],
                        [
                            "name" => "date",
                            "in" => "query",
                            "required" => false,
                            "schema" => [
                                "type" => "string",
                                "format" => "date",
                            ],
                            "description" =>
                                "Filtre par date (format YYYY-MM-DD)",
                        ],
                    ],
                    "responses" => [
                        "200" => [
                            "description" => 'Liste des logs d\'utilisation',
                            "content" => [
                                "application/json" => [
                                    "schema" => [
                                        "type" => "object",
                                        "properties" => [
                                            "count" => ["type" => "integer"],
                                            "logs" => [
                                                "type" => "array",
                                                "items" => [
                                                    "type" => "object",
                                                    "properties" => [
                                                        "timestamp" => [
                                                            "type" => "string",
                                                        ],
                                                        "identifier" => [
                                                            "type" => "string",
                                                        ],
                                                        "endpoint" => [
                                                            "type" => "string",
                                                        ],
                                                        "ip" => [
                                                            "type" => "string",
                                                        ],
                                                        "user_agent" => [
                                                            "type" => "string",
                                                        ],
                                                    ],
                                                ],
                                            ],
                                            "filters" => [
                                                "type" => "object",
                                                "properties" => [
                                                    "limit" => [
                                                        "type" => "integer",
                                                    ],
                                                    "filter" => [
                                                        "type" => "string",
                                                    ],
                                                    "date" => [
                                                        "type" => "string",
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        "401" => ["description" => "Non authentifié"],
                        "403" => [
                            "description" => 'Niveau d\'accès insuffisant',
                        ],
                        "404" => ["description" => "Fichier de log non trouvé"],
                    ],
                    "security" => [["ApiKeyAuth" => []]],
                ],
            ],
        ],
        "components" => [
            "securitySchemes" => [
                "ApiKeyAuth" => [
                    "type" => "apiKey",
                    "in" => "header",
                    "name" => "X-API-Key",
                ],
            ],
        ],
    ];

    sendResponse(200, $swagger);
}

function fetchCpuBenchmarkData(): ?array
{
    $userAgent =
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.4 Safari/605.1.15";
    $timeout = 10;

    $ch = curl_init("https://www.cpubenchmark.net/CPU_mega_page.html");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_USERAGENT => $userAgent,
        CURLOPT_TIMEOUT => $timeout,
    ]);
    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if (!$response || !preg_match("/PHPSESSID=([^;]+)/", $response, $matches)) {
        sendResponse(504, [
            "error" => true,
            "message" => "Erreur lors de la récupération du PHPSESSID",
            "details" => $curlErr,
        ]);
        return null;
    }

    $phpsessid = $matches[1];
    $timestamp = round(microtime(true) * 1000);

    $ch = curl_init("https://www.cpubenchmark.net/data/?_=$timestamp");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => $userAgent,
        CURLOPT_HTTPHEADER => [
            "Accept: application/json, text/javascript, */*; q=0.01",
            "Accept-Encoding: text",
            "Cookie: PHPSESSID=$phpsessid",
            "Referer: https://www.cpubenchmark.net/CPU_mega_page.html",
            "X-Requested-With: XMLHttpRequest",
        ],
        CURLOPT_TIMEOUT => $timeout,
    ]);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if (!$data) {
        sendResponse(504, [
            "error" => true,
            "message" => "Erreur lors de la récupération des données",
            "details" => $curlErr,
        ]);
        return null;
    }

    if ($httpCode !== 200) {
        sendResponse($httpCode, [
            "error" => true,
            "message" => "Erreur HTTP $httpCode lors de la récupération des données",
        ]);
        return null;
    }

    $json = json_decode($data, true);
    if (!isset($json["data"])) {
        sendResponse(500, [
            "error" => true,
            "message" => "Champ 'data' introuvable dans la réponse",
        ]);
        return null;
    }

    return $json["data"];
}

function getCpuBenchmark(): void
{
    $data = fetchCpuBenchmarkData();
    if ($data !== null) {
        header("Content-Type: application/json");
        echo json_encode($data);
    }
}

function getCpuBenchmarkCSV(): void
{
    $data = fetchCpuBenchmarkData();
    if ($data === null) {
        return;
    }

    header("Content-Type: text/csv");
    header('Content-Disposition: attachment; filename="benchmark.csv"');

    $fp = fopen("php://output", "w");
    $first = true;

    foreach ($data as $row) {
        if ($first) {
            fputcsv($fp, array_keys($row));
            $first = false;
        }
        fputcsv($fp, $row);
    }

    fclose($fp);
}

// Fonctions utilitaires
function getApiKey()
{
    // Vérifier les en-têtes HTTP (case insensitive)
    $headers = getallheaders();
    foreach ($headers as $name => $value) {
        if (strtolower($name) === "x-api-key") {
            return $value;
        }
    }

    // Vérifier chaque paramètre dans $_GET
    $possibleParams = [
        "X-API-KEY",
        "X-API-Key",
        "X-Api-Key",
        "x-api-key",
        "x_api_key",
        "api_key",
        "apiKey",
        "apikey",
        "key",
    ];
    foreach ($possibleParams as $param) {
        if (isset($_GET[$param])) {
            return $_GET[$param];
        }
    }

    return null;
}

function authenticateApiKey($apiKey)
{
    if (empty($apiKey)) {
        return null;
    }

    // Check app keys first
    $apps = config("apps");
    foreach ($apps as $appName => $appInfo) {
        // Check if we're using the new multiple keys structure
        if (isset($appInfo["keys"]) && is_array($appInfo["keys"])) {
            if (isset($appInfo["keys"][$apiKey])) {
                $keyInfo = $appInfo["keys"][$apiKey];
                return [
                    "type" => "app",
                    "name" => $appName,
                    "acl" => $keyInfo["acl"],
                    "description" => $appInfo["description"],
                    "key_description" => $keyInfo["description"] ?? "App Key",
                    "all_regions_access" => true,
                ];
            }
        }

        // Backward compatibility with the existing single key structure
        if (isset($appInfo["api_key"]) && $apiKey === $appInfo["api_key"]) {
            return [
                "type" => "app",
                "name" => $appName,
                "acl" => $appInfo["acl"],
                "description" => $appInfo["description"],
                // No key_description for backward compatibility
                "all_regions_access" => true,
            ];
        }
    }

    // Check users (unchanged)
    $regions = config("auth");
    foreach ($regions as $region => $users) {
        foreach ($users as $user) {
            // Generate API key from username and password
            $generatedKey = hash(
                "sha256",
                $user["username"] . ":" . $user["pass"]
            );
            if ($apiKey === $generatedKey) {
                return [
                    "type" => "user",
                    "region" => $region,
                    "username" => $user["username"],
                    "email" => $user["email"],
                    "acl" => $user["acl"],
                    "prefix" => $user["prefix"],
                ];
            }
        }
    }

    return null;
}

function hasRegionAccess($entity, $region)
{
    if (!$entity) {
        return false;
    }

    // Si l'entité a accès à toutes les régions
    if ($entity["all_regions_access"] ?? false) {
        return true;
    }

    // Si c'est une application, elle peut avoir des restrictions spécifiques
    if ($entity["type"] === "app") {
        // Par défaut, les applications ont un accès complet
        // Mais vous pourriez implémenter des restrictions spécifiques ici
        return true;
    }

    if ($entity["acl"] >= 10) {
        return true;
    }

    // Sinon, vérifier si la région de l'utilisateur correspond
    return $entity["region"] === $region;
}

function logApiUsage($entity = null, $endpoint, $request_method)
{
    $logFile = config("project_root_path") . "/api/logs_txt/api_usage.log";
    $logDir = dirname($logFile);

    // Create log directory if needed
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    // Prepare log data
    $timestamp = date("Y-m-d H:i:s");
    $ipAddress = $_SERVER["REMOTE_ADDR"];
    $userAgent = $_SERVER["HTTP_USER_AGENT"] ?? "Unknown";

    // Identify if it's a user, application, or unauthenticated request
    if ($entity === null) {
        $identifier = "ANONYMOUS";
    } elseif ($entity["type"] === "user") {
        $identifier = "USER:{$entity["username"]}";
    } else {
        // For applications, check if key_description exists (new format)
        if (isset($entity["key_description"])) {
            $identifier = "APP:{$entity["name"]} KEY:{$entity["key_description"]}";
        } else {
            // Old format - no key description
            $identifier = "APP:{$entity["name"]}";
        }
    }

    // Log format: [TIMESTAMP] METHOD - IDENTIFIER - ENDPOINT - IP - USER_AGENT
    $logEntry = "[$timestamp] $request_method - $identifier - $endpoint - $ipAddress - $userAgent\n";

    // Write to log file
    if (
        !isset($_SERVER["HTTP_USER_AGENT"]) ||
        $_SERVER["HTTP_USER_AGENT"] !== "swagger-validator"
    ) {
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}

/**
 * Fonction pour récupérer et afficher les logs d'utilisation de l'API
 */
function getLogs()
{
    $logFile = config("project_root_path") . "/api/logs_txt/api_usage.log";

    // Vérifier si le fichier de log existe
    if (!file_exists($logFile)) {
        sendResponse(404, [
            "error" => true,
            "message" => "Aucun fichier de log disponible",
        ]);
    }

    // Paramètres optionnels pour filtrer les logs
    $limit = isset($_GET["limit"]) ? intval($_GET["limit"]) : 100;
    $filter = isset($_GET["filter"]) ? $_GET["filter"] : null;
    $date = isset($_GET["date"]) ? $_GET["date"] : null;

    // Lire le fichier de log
    $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Filtrer les logs si nécessaire
    if ($filter) {
        $logs = array_filter($logs, function ($line) use ($filter) {
            return stripos($line, $filter) !== false;
        });
    }

    // Filtrer par date si spécifié
    if ($date) {
        $logs = array_filter($logs, function ($line) use ($date) {
            return strpos($line, "[$date") === 0;
        });
    }

    // Prendre les N dernières entrées
    $logs = array_slice(array_values($logs), -$limit);

    // Analyser les logs en structure JSON
    $parsedLogs = [];
    foreach ($logs as $log) {
        // Format attendu: [TIMESTAMP] IDENTIFIER - ENDPOINT - IP - USER_AGENT
        if (
            preg_match(
                "/\[(.*?)\] (.*?) - (.*?) - (.*?) - (.*)/",
                $log,
                $matches
            )
        ) {
            $parsedLogs[] = [
                "timestamp" => $matches[1],
                "identifier" => $matches[2],
                "endpoint" => $matches[3],
                "ip" => $matches[4],
                "user_agent" => $matches[5],
            ];
        } else {
            // Si le format ne correspond pas, ajouter la ligne brute
            $parsedLogs[] = ["raw" => $log];
        }
    }

    // Envoyer la réponse
    sendResponse(200, [
        "count" => count($parsedLogs),
        "logs" => $parsedLogs,
        "filters" => [
            "limit" => $limit,
            "filter" => $filter,
            "date" => $date,
        ],
    ]);
}

function deleteDir($path)
{
    return is_file($path)
        ? @unlink($path)
        : array_map(__FUNCTION__, glob($path . "/*")) == @rmdir($path);
}

function getMimeTypes()
{
    $mimeTypes = [
        "exe" => "application/vnd.microsoft.portable-executable",
        "zip" => "application/zip",
        "pdf" => "application/pdf",
        "doc" => "application/msword",
        "docx" =>
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "xls" => "application/vnd.ms-excel",
        "xlsx" =>
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "ppt" => "application/vnd.ms-powerpoint",
        "pptx" =>
            "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "txt" => "text/plain",
        "csv" => "text/csv",
        "jpg" => "image/jpeg",
        "jpeg" => "image/jpeg",
        "png" => "image/png",
        "gif" => "image/gif",
        "mp3" => "audio/mpeg",
        "mp4" => "video/mp4",
        "rar" => "application/vnd.rar",
        "7z" => "application/x-7z-compressed",
        "tar" => "application/x-tar",
        "gz" => "application/gzip",
        "iso" => "application/x-iso9660-image",
        "svg" => "image/svg+xml",
        "xml" => "application/xml",
        "json" => "application/json",
        "html" => "text/html",
        "css" => "text/css",
        "js" => "application/javascript",
        "apk" => "application/vnd.android.package-archive",
        "dmg" => "application/x-apple-diskimage",
        "msi" => "application/x-msdownload",
    ];
    return $mimeTypes;
}
