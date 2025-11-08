<?php

return [
    "version" => "1.6.0",
    "debug" => false,
    "hostname" =>
        php_sapi_name() === "cli" ? "localhost" : $_SERVER["HTTP_HOST"],
    "project_root_path" => __DIR__,
    "db_host" => "localhost",
    "db_user" => "root",
    "db_password" => "password",
    "app_name" => "EmCoDits",
    "smtp_host" => "smtp.gmail.com",
    "smtp_auth" => true,
    'smtp_username' => "", // Votre email gmail
    'smtp_password' => "", // Votre MDP Unique Généré ici :  https://myaccount.google.com/apppasswords
    'smtp_mail_from' => "", // Votre email gmail
    "smtp_send_to" => "php-emcotech@drop.tf",
    "smtp_port" => 587,
    // Path des modules
    "cpumarks_path" => '/home/connexio/recond/cpumarks',
    // Applications avec clés prédéfinies
    "apps" => [
        "jeanjacques" => [
            "description" => "Windows • Application d'audit PC par Jean-Jacques FOUGÈRE (Bordeaux).",
            "keys" => [
                // Client key with limited permissions
                "xxx" => [
                    "acl" => 3,
                    "description" => "App",
                ],
                // Client key with limited permissions
                "xxx" => [
                    "acl" => 3,
                    "description" => "App SendZip",
                ],
            ],
        ],
        "emcotech" => [
            "description" => "Windows • Application d'audit téléphones et tablettes iOS et Android par Joffrey SCHROEDER (Strasbourg).",
            "keys" => [
                // Admin key with full permissions
                "xxx" => [
                    "acl" => 8,
                    "description" => "Admin",
                ],
                // Client key with limited permissions
                "xxx" => [
                    "acl" => 3,
                    "description" => "App",
                ],
            ],
        ],
        "linux" => [
            "description" => "Linux • Application d'audit pour PC tournant sur Linux par Bernard MAISON (Grenoble).",
            "keys" => [
                // Admin key with full permissions
                "xxx" => [
                    "acl" => 8,
                    "description" => "Admin",
                ],
                // Client key with limited permissions
                "xxx" => [
                    "acl" => 3,
                    "description" => "App",
                ],
            ],
        ],
        "linuxdev" => [
            "description" => "Audit Linux Développement",
            "keys" => [
                // Admin key with full permissions
                "xxx" => [
                    "acl" => 8,
                    "description" => "Admin",
                ],
                // Client key with limited permissions
                "xxx" => [
                    "acl" => 3,
                    "description" => "App",
                ],
            ],
        ],
    ],
    'auth' => [
        'CRÉTEIL' => [
            [ 'username' => 'user1', 'email' => '', 'pass' => 'password1', 'acl' => 1, 'prefix' => 'CR'],
            [ 'username' => 'user2', 'email' => '', 'pass' => 'password2', 'acl' => 1, 'prefix' => 'CR'],
        ],
        'LYON' => [
            [ 'username' => 'user3', 'email' => '', 'pass' => 'password3', 'acl' => 1, 'prefix' => 'LY'],
        ],
        'LILLE' => [
            [ 'username' => 'user4', 'email' => '', 'pass' => 'password4', 'acl' => 1, 'prefix' => 'LI'],
        ],
        'BORDEAUX' => [
            [ 'username' => 'user5', 'email' => '', 'pass' => 'password5', 'acl' => 1, 'prefix' => 'BX'],
        ],
        'STRASBOURG' => [
            [ 'username' => 'joffrey', 'email' => '', 'pass' => '', 'acl' => 10, 'prefix' => 'ST'],
        ],
        'VICTOIRES' => [
            [ 'username' => 'user7', 'email' => '', 'pass' => 'password7', 'acl' => 1, 'prefix' => 'VI'],
        ],
        'SAINT-DENIS' => [
            [ 'username' => 'user8', 'email' => '', 'pass' => 'password8', 'acl' => 1, 'prefix' => 'SD'],
        ],
        'MAISON BLANCHE' => [
            [ 'username' => 'user9', 'email' => '', 'pass' => 'password9', 'acl' => 1, 'prefix' => 'MB'],
        ],
        'GRENOBLE' => [
            [ 'username' => 'user10', 'email' => '', 'pass' => 'password10', 'acl' => 1, 'prefix' => 'GR'],
        ],
        'LA VILLETTE' => [
            [ 'username' => 'user11', 'email' => '', 'pass' => 'password11', 'acl' => 1, 'prefix' => 'LV'],
        ],
        'MARSEILLE' => [
            [ 'username' => 'user12', 'email' => '', 'pass' => 'password12', 'acl' => 1, 'prefix' => 'MA'],
        ],
        'ROUBAIX' => [
            [ 'username' => 'glochet', 'email' => '', 'pass' => '', 'acl' => 5, 'prefix' => 'RO'],
        ],
    ],
    "regions" => [
        "ST" => "STRASBOURG",
        "SD" => "SAINT-DENIS",
        "MB" => "MAISON BLANCHE",
        "CR" => "CRÉTEIL",
        "MA" => "MARSEILLE",
        "GR" => "GRENOBLE",
        "LV" => "LA VILLETTE",
        "LY" => "LYON",
        "RO" => "ROUBAIX",
        "BX" => "BORDEAUX",
        "LI" => "LILLE",
        "VI" => "VICTOIRES",
        "TE" => "TEST",
    ],
];
