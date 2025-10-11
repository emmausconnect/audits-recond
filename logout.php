<?php
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
]);
session_start();

// Destroy all session variables
$_SESSION = [];

$regionFromGet = $_GET['region'] ?? '';

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

// Destroy the session itself
session_destroy();

// Redirect to the login page (or any other page)
header('Location: /' . $regionFromGet);
exit;
