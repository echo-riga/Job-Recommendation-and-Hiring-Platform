<?php
// mail_config.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure your PHPMailer files are included for the autoloader to work
require 'PHPMailerAutoload.php'; 

function sendOfferEmail($recipient_email, $recipient_name, $subject, $body_html) {
    
    // Using your uploaded class files
    $mail = new PHPMailer(true); 

    try {
        // Server settings (You MUST fill in your own SMTP details)
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com';                // e.g., 'smtp.gmail.com'
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@example.com';          // Your Email
        $mail->Password   = 'your_email_password';             // Your Email Password
        $mail->SMTPSecure = 'tls';                             // 'ssl' or 'tls'
        $mail->Port       = 587;                               // Port (e.g., 587 for TLS, 465 for SSL)

        // Sender and Recipient
        $mail->setFrom('no-reply@philkoei-international.com', 'Philkoei International HR');
        $mail->addAddress($recipient_email, $recipient_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        $mail->AltBody = strip_tags($body_html);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>