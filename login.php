<?php
if (!defined('ALLOW_ACCESS')) {
    header('Location: /');
    exit;
}

// Deprecated
$config = Config::getInstance()->all();

// Get the region from the URL (default is empty)
$regionFromGet = $_GET['region'] ?? '';

// if ($config['hostname'] !== 'audits.drop.tf') {
if ($config['hostname'] !== 'audits.emmaus-connect.org') {
    // echo '<pre>Connexion impossible pour le moment depuis ce domaine, vous allez être redirigé vers <a href="https://audits.drop.tf">https://audits.drop.tf</a>.</pre>';
    echo '<pre>Connexion impossible pour le moment depuis ce domaine, vous allez être redirigé vers <a href="https://audits.emmaus-connect.org">https://audits.emmaus-connect.org</a>.</pre>';
    // echo '<meta http-equiv="refresh" content="5;url=https://audits.drop.tf">';
    echo '<meta http-equiv="refresh" content="5;url=https://audits.emmaus-connect.org">';
    exit();
}

// Ensure the region from GET is valid
if ($regionFromGet && !isset($config['auth'][$regionFromGet])) {
    die('Invalid region provided.');
}

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $region = $_POST['region'] ?? '';
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';

    if (isset($config['auth'][$region])) {
        foreach ($config['auth'][$region] as $user) {
            // Check if the identifier matches either username or email
            if (($user['username'] === $identifier || $user['email'] === $identifier) && $user['pass'] === $password) {
                $_SESSION['user'] = [
                    'region' => $region,
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'prefix' => $user['prefix'],
                    'acl' => $user['acl']
                ];
                // header('Location: /' . $region);
                echo '<meta http-equiv="refresh" content="0;url=/'. $region .'">';
                exit;
            }
        }
    }
    $error = 'Identifiant ou mot de passe incorrect pour cette région.';
}
?>
    <p><a href='/'>Revenir à l'accueil</a></p>
    <h1>Authentification</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="region">Région :</label>
        <?php if ($regionFromGet): ?>
            <input type="text" id="region" name="region" value="<?= htmlspecialchars($regionFromGet) ?>" readonly>
        <?php else: ?>
            <select id="region" name="region" required>
                <option value="">Sélectionnez une région</option>
                <?php foreach (array_keys($config['auth']) as $region): ?>
                    <option value="<?= htmlspecialchars($region) ?>"><?= htmlspecialchars($region) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <br><br>
        <label for="identifier">Email ou nom d'utilisateur :</label>
        <input type="text" id="identifier" name="identifier" required>
        <br><br>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <button type="submit">Se connecter</button>
    </form>