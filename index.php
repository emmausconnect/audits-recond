<?php
if (!in_array($_SERVER['HTTP_HOST'], ['audit.drop.tf', 'audits.drop.tf'])) {
    // Vérifie si on est en HTTP ou si le sous-domaine est "audit" sur "emmaus-connect.org"
    if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') || strpos($_SERVER['HTTP_HOST'], 'audit.emmaus-connect.org') === 0) {
        // Remplace "audit" par "audits" dans le sous-domaine et force HTTPS
        $newHost = str_replace('audit.', 'audits.', $_SERVER['HTTP_HOST']);
        $url = "https://$newHost{$_SERVER['REQUEST_URI']}";
        header("Location: $url");
        exit;
    }
}
?>

<!doctype html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="Author" content="Made by 'tree'" />
        <meta
            name="GENERATOR"
            content="tree v2.1.0 © 1996 - 2022 by Steve Baker, Thomas Moore, Francesc Rocher, Florian Sesser, Kyosuke Tokoro"
        />
        <title>AUDITS PAR RÉGION</title>
        <style type="text/css">
            BODY {
                font-family: monospace, sans-serif;
                color: black;
            }
            P {
                font-family: monospace, sans-serif;
                color: black;
                margin: 0px;
                padding: 0px;
            }
            A:visited {
                text-decoration: none;
                margin: 0px;
                padding: 0px;
            }
            A:link {
                text-decoration: none;
                margin: 0px;
                padding: 0px;
            }
            A:hover {
                text-decoration: underline;
                background-color: yellow;
                margin: 0px;
                padding: 0px;
            }
            A:active {
                margin: 0px;
                padding: 0px;
            }
            .VERSION {
                font-size: small;
                font-family: arial, sans-serif;
            }
            .NORM {
                color: black;
            }
            .FIFO {
                color: purple;
            }
            .CHAR {
                color: yellow;
            }
            .DIR {
                color: blue;
            }
            .BLOCK {
                color: yellow;
            }
            .LINK {
                color: aqua;
            }
            .SOCK {
                color: fuchsia;
            }
            .EXEC {
                color: green;
            }
        </style>
    </head>
    <body>
        <h1>AUDITS PAR RÉGION</h1>
        <p>
            <?php
            $directories = glob('*', GLOB_ONLYDIR);
            sort($directories); // Trie les dossiers par ordre alphabétique
            $unknownRegion = null;

            foreach ($directories as $directory) {
                if (strcasecmp($directory, 'Région inconnue') === 0) {
                    $unknownRegion = $directory; // Garde en mémoire "RÉGION INCONNUE" (insensible à la casse)
                    continue; // Saute pour l'afficher en dernier
                }
                $encodedDir = htmlspecialchars($directory, ENT_QUOTES, 'UTF-8');
                echo "├── <a href=\"./{$encodedDir}/\">{$encodedDir}</a><br />";
            }

            // Affiche "RÉGION INCONNUE" en dernier s'il a été trouvé
            if ($unknownRegion !== null) {
                $encodedUnknown = htmlspecialchars($unknownRegion, ENT_QUOTES, 'UTF-8');
                echo "|";
                echo "<br/>";
                echo "├── <a href=\"./{$encodedUnknown}/\">RÉGION INCONNUE</a><br />";
            }
            ?>
        </p>
    </body>
</html>
