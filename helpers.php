<?php

function config($key) {
    static $config;
    if (!$config) {
        $config = require 'config.php';
    }
    return $config[$key] ?? null;
}
