<?php

return [
    'debug' => false,
    'hostname' => $_SERVER['HTTP_HOST'],
    'project_root_path' => __DIR__,
    'db_host' => 'localhost',
    'db_user' => 'root',
    'db_password' => 'password',
    'app_name' => 'MonApplication',
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
    ],
    'regions' => [
        "ST" => "STRASBOURG",
        "SD" => "SAINT-DENIS",
        "MB" => "MAISON BLANCHE",
        "CR" => "CRÉTEIL",
        "MA" => "MARSEILLE",
        "GR" => "GRENOBLE",
        "LV" => "LA VILLETTE",
        "LY" => "LYON",
        "BX" => "BORDEAUX",
        "LI" => "LILLE",
        "VI" => "VICTOIRES",
        "TE" => "TEST",
    ]
];
