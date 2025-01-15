<?php

class ErrorBuffer {
    private static $instance = null;
    private $errorFile;
    private $emailSent = false;
    private $emailDebounceTime = 10; // 5 minutes in seconds
    private $lockFile;

    private function __construct() {
        $this->errorFile = sys_get_temp_dir() . '/php_errors_' . date('Y-m-d') . '.tmp';
        $this->lockFile = sys_get_temp_dir() . '/php_errors_lock_' . date('Y-m-d') . '.tmp';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function acquireLock() {
        $lockFp = fopen($this->lockFile, 'c+');
        if (!$lockFp) return false;

        $locked = flock($lockFp, LOCK_EX);
        if (!$locked) {
            fclose($lockFp);
            return false;
        }

        return $lockFp;
    }

    private function releaseLock($lockFp) {
        flock($lockFp, LOCK_UN);
        fclose($lockFp);
    }

    public function addError($error) {
        $lockFp = $this->acquireLock();
        if (!$lockFp) return;

        $errors = $this->loadErrors();
        $errors[] = $error;
        file_put_contents($this->errorFile, json_encode($errors));

        // Check if we should send email
        $lastEmailTime = $this->getLastEmailTime();
        if (time() - $lastEmailTime >= $this->emailDebounceTime) {
            $this->sendEmail($errors);
            file_put_contents($this->errorFile, '[]'); // Clear errors after sending
            $this->updateLastEmailTime();
        }

        $this->releaseLock($lockFp);
    }

    private function loadErrors() {
        if (!file_exists($this->errorFile)) {
            return [];
        }
        $content = file_get_contents($this->errorFile);
        return $content ? json_decode($content, true) : [];
    }

    private function getLastEmailTime() {
        $timeFile = sys_get_temp_dir() . '/last_email_time.tmp';
        return file_exists($timeFile) ? (int)file_get_contents($timeFile) : 0;
    }

    private function updateLastEmailTime() {
        $timeFile = sys_get_temp_dir() . '/last_email_time.tmp';
        file_put_contents($timeFile, time());
    }

    private function formatErrors($errors) {
        $errorSummary = "Multiple errors occurred:\n\n";
        foreach ($errors as $index => $error) {
            $errorSummary .= ($index + 1) . ". " . $error . "\n";
        }
        return $errorSummary;
    }

    private function sendEmail($errors) {
        if (empty($errors)) {
            return;
        }

        $errorSummary = $this->formatErrors($errors);
        exec("php " . escapeshellarg(__DIR__ . "/error-notifier.php") . " " .
             escapeshellarg($errorSummary) . " > /dev/null 2>&1 &");
    }

    public function __destruct() {
        // Check for remaining errors before script ends
        $lockFp = $this->acquireLock();
        if (!$lockFp) return;

        $errors = $this->loadErrors();
        if (!empty($errors)) {
            $lastEmailTime = $this->getLastEmailTime();
            if (time() - $lastEmailTime >= $this->emailDebounceTime) {
                $this->sendEmail($errors);
                file_put_contents($this->errorFile, '[]');
                $this->updateLastEmailTime();
            }
        }

        $this->releaseLock($lockFp);
    }
}

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    $error = "Error [$errno]: $errstr in $errfile on line $errline";
    error_log($error);
    ErrorBuffer::getInstance()->addError($error);
    return true;
});

set_exception_handler(function ($exception) {
    $error = "Uncaught Exception: " . $exception->getMessage() .
             " in " . $exception->getFile() .
             " on line " . $exception->getLine();
    error_log($error);
    ErrorBuffer::getInstance()->addError($error);
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $errorMessage = "Fatal Error [{$error['type']}]: {$error['message']} in {$error['file']} on line {$error['line']}";
        error_log($errorMessage);
        ErrorBuffer::getInstance()->addError($errorMessage);
    }
});

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
        } elseif (file_exists('./audits/config.php')) {
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