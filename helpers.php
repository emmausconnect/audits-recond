<?php

class Config {
    private static $instance = null;
    private $settings = [];

    private function __construct() {
        // Déterminer le chemin du fichier de configuration
        $configPath = $this->getConfigPath();
        
        if ($configPath) {
            $this->settings = require $configPath;
        }
    }
    
    private function getConfigPath() {
        if (file_exists('../config.php')) {
            return '../config.php';
        } elseif (file_exists('config.php')) {
            return 'config.php';
        }
        throw new Exception('Fichier de configuration introuvable');
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get($key, $default = null) {
        return $this->settings[$key] ?? $default;
    }
    
    public function set($key, $value) {
        $this->settings[$key] = $value;
    }
    
    public function all() {
        return $this->settings;
    }
}

function config($key, $default = null) {
    return Config::getInstance()->get($key, $default);
}