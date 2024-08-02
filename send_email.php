<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; 
    $mail->SMTPAuth   = true;
    $mail->Username   = 'austinbal28@gmail.com'; 
    $mail->Password   = 'ukhh odze ohnu xttu';  
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
    $mail->Port       = 465; 

    $mail->setFrom('austinbal28@gmail.com', 'Austin');
    $mail->addAddress('baloyimuxe567@gmail.com', 'Recipient Name');

    $mail->addAttachment(__DIR__ . '/view.pdf'); 

    $mail->isHTML(true); 
    $mail->Subject = 'Daily Report PDF';
    $mail->Body    = 'Dear recipient,<br><br>Please find the daily report attached.<br><br>Best regards,<br>Rasi';
    $mail->AltBody = 'Dear recipient, Please find the daily report attached. Best regards, Rasi';

    $mail->send();
    echo 'Email has been sent successfully.';
} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
