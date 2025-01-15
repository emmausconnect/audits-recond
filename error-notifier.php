<?php
if ($argc < 2) {
    die("No error details provided.\n");
}

require_once 'helpers.php';
$error = $argv[1];

use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = config('smtp_host'); // SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = config('smtp_username'); // SMTP username / Votre Email GMAIL
    $mail->Password = config('smtp_password'); // SMTP password / Votre MDP Unique Généré ici :  https://myaccount.google.com/apppasswords
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Encryption (TLS/SSL)
    $mail->Port = config('smtp_port'); // SMTP port (e.g., 587 for TLS, 465 for SSL)

    // Email settings
    $mail->setFrom(config('smtp_mail_from'), 'PHP Error'); // Votre email gmail
    $mail->addAddress(config('smtp_send_to')); //  A quel email envoyer l'erreur
    $mail->Subject = 'Script Error Notification'; // Sujet du mail
    $mail->Body = $error;

    $mail->send();
    error_log("Mail sent !");
    echo "Error notification sent.\n";
} catch (Exception $e) {
    error_log("Failed to send error notification: {$mail->ErrorInfo}");
}
?>
