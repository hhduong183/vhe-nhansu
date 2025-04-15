<?php
// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

class EmailNotification {
    private $smtp_host = 'smtp.office365.com';
    private $smtp_port = 587;
    private $smtp_username = 'info@vhe.com.vn';
    private $smtp_password = 'vhe@2022-1';
    private $smtp_secure = 'StartTLS';

    public function sendEmail($to, $subject, $content, $fromName = 'HR System') {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = $this->smtp_secure;
            $mail->Port = $this->smtp_port;
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom($this->smtp_username, $fromName);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $content;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    }
}