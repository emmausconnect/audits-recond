<?php

if ($argc < 2) {
    die("No error details provided.\n");
}
require_once 'helpers.php';
$error = $argv[1];
use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php';

function sendErrorNotification($input) {
    // Try to decode as JSON first
    $data = json_decode($input, true);

    // If not JSON or decoding failed, treat as direct error message
    if (!$data) {
        $data = [
            'summary' => $input,
            'hostname' => config('hostname', 'unknown')
        ];
    }

    $errorSummary = $data['summary'];
    $hostname = $data['hostname'];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = config('smtp_host');
        $mail->SMTPAuth = true;
        $mail->Username = config('smtp_username');
        $mail->Password = config('smtp_password');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = config('smtp_port');

        $mail->setFrom(config('smtp_mail_from'), 'PHP Error');
        $mail->addAddress(config('smtp_send_to'));
        $mail->Subject = "Script Error Notification - Host: $hostname";
        $mail->Body = $errorSummary;

        $mail->send();
        error_log("Mail sent for host: $hostname!");
        echo "Error notification sent.\n";
    } catch (Exception $e) {
       $timestamp = date('Y-m-d H:i:s');
       $logMessage = "[{$timestamp}] Error on host: {$hostname}\n";
       $logMessage .= "Subject: Script Error Notification - Host: {$hostname}\n";
       $logMessage .= "Message: {$errorSummary}\n";
       $logMessage .= "Mail Error: {$mail->ErrorInfo}\n\n";

       // Définir le chemin du fichier de log
       $logFile = __DIR__ . '/../php-errors.log';

       // Écrire dans le fichier de log
       if (file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX) === false) {
           error_log("Impossible d'écrire dans le fichier de log: $logFile");
       }

       error_log("Failed to send error notification: {$mail->ErrorInfo}");
   }
}

// Handle both execution methods
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    // Called via CLI (exec)
    sendErrorNotification($argv[1]);
} elseif (isset($GLOBALS['errorNotifierData'])) {
    // Called via include
    sendErrorNotification($GLOBALS['errorNotifierData']);
}
?>