<?php

class ErrorBuffer {
    private static $instance = null;
    private $errorFile;
    private $emailSent = false;
    private $emailDebounceTime = 10;
    private $lockFile;
    private $hostname;

    private function __construct() {
        $this->errorFile = sys_get_temp_dir() . '/php_errors_' . date('Y-m-d') . '.tmp';
        $this->lockFile = sys_get_temp_dir() . '/php_errors_lock_' . date('Y-m-d') . '.tmp';
        $this->hostname = config('hostname', 'unknown');  // Capture hostname at instantiation
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
        // Store error with metadata
        $errors[] = [
            'message' => $error,
            'hostname' => $this->hostname,
            'timestamp' => time()
        ];
        file_put_contents($this->errorFile, json_encode($errors));

        $lastEmailTime = $this->getLastEmailTime();
        if (time() - $lastEmailTime >= $this->emailDebounceTime) {
            $this->sendEmail($errors);
            file_put_contents($this->errorFile, '[]');
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
            $errorSummary .= ($index + 1) . ". ";

            // Handle both new and old format errors
            if (is_array($error) && isset($error['message'])) {
                // New format with timestamp
                $timestamp = isset($error['timestamp']) ? date('Y-m-d H:i:s', $error['timestamp']) : date('Y-m-d H:i:s');
                $errorSummary .= "[" . $timestamp . "] " . $error['message'] . "\n";
            } else {
                // Old format (direct string)
                $errorSummary .= "[" . date('Y-m-d H:i:s') . "] " . $error . "\n";
            }
        }
        return $errorSummary;
    }

    private function isExecEnabled() {
        if (function_exists('exec')) {
            $disabled = explode(',', ini_get('disable_functions'));
            return !in_array('exec', array_map('trim', $disabled));
        }
        return false;
    }

    private function getPhpBinary() {
        // Try 'php' first
        exec("which php 2>/dev/null", $output, $returnValue);
        if ($returnValue === 0 && !empty($output[0])) {
            return "php";
        }

        // Common PHP installation paths
        $possiblePaths = [
            '/usr/local/php8.0/bin/php',
            '/usr/local/bin/php',
            '/usr/bin/php'
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        // Fallback to default PHP path if nothing else works
        return '/usr/local/php8.0/bin/php';
    }

    private function sendEmail($errors) {
        if (empty($errors)) {
            return;
        }

        $errorSummary = $this->formatErrors($errors);
        $errorNotifierPath = __DIR__ . "/error-notifier.php";

        if (!file_exists($errorNotifierPath)) {
            error_log("Error notifier script not found at: " . $errorNotifierPath);
            return;
        }

        // Pass both error summary and hostname
        $data = json_encode([
            'summary' => $errorSummary,
            'hostname' => $this->hostname
        ]);

        if ($this->isExecEnabled()) {
            $phpBin = $this->getPhpBinary();
            if (!config('debug')) {
                exec($phpBin . " " . escapeshellarg($errorNotifierPath) . " " . escapeshellarg($data) . " > /dev/null 2>&1 &");
            }
            else {
                $cmd = $phpBin . " " . escapeshellarg($errorNotifierPath) . " " . escapeshellarg($data);
                $descriptorspec = array(
                0 => array("pipe", "r"),  // stdin
                1 => array("pipe", "w"),  // stdout
                2 => array("pipe", "w")   // stderr
                );

                $process = proc_open($cmd, $descriptorspec, $pipes);
                if (is_resource($process)) {
                    $stdout = stream_get_contents($pipes[1]);
                    $stderr = stream_get_contents($pipes[2]);
                    fclose($pipes[0]);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    $return_value = proc_close($process);

                    var_dump('STDOUT:', $stdout);
                    var_dump('STDERR:', $stderr);
                    var_dump('Return value:', $return_value);
                }
            }

        } else {
            global $errorNotifierData;
            $errorNotifierData = $data;
            include $errorNotifierPath;
        }
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
    if (config('debug')) {
        echo "<div style='color: red; background-color: #ffe6e6; padding: 10px; margin: 10px; border: 1px solid red;'>";
        echo "<b>Erreur PHP:</b><br>";
        echo "Message: " . htmlspecialchars($errstr) . "<br>";
        echo "Fichier: " . htmlspecialchars($errfile) . "<br>";
        echo "Ligne: " . $errline . "<br>";
        echo "</div>";
    }
    $error = "Error [$errno]: $errstr in $errfile on line $errline";
    error_log($error);
    ErrorBuffer::getInstance()->addError($error);
    return true;
});

set_exception_handler(function ($exception) {
    if (config('debug')) {
        echo "<div style='color: red; background-color: #ffe6e6; padding: 10px; margin: 10px; border: 1px solid red;'>";
        echo "<b>Exception PHP:</b><br>";
        echo "Message: " . htmlspecialchars($exception->getMessage()) . "<br>";
        echo "Fichier: " . htmlspecialchars($exception->getFile()) . "<br>";
        echo "Ligne: " . $exception->getLine() . "<br>";
        echo "</div>";
    }
    $error = "Uncaught Exception: " . $exception->getMessage() .
             " in " . $exception->getFile() .
             " on line " . $exception->getLine();
    error_log($error);
    ErrorBuffer::getInstance()->addError($error);
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (config('debug')) {
            echo "<div style='color: red; background-color: #ffe6e6; padding: 10px; margin: 10px; border: 1px solid red;'>";
            echo "<b>Erreur Fatale PHP:</b><br>";
            echo "Message: " . htmlspecialchars($error['message']) . "<br>";
            echo "Fichier: " . htmlspecialchars($error['file']) . "<br>";
            echo "Ligne: " . $error['line'] . "<br>";
            echo "</div>";
        }
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