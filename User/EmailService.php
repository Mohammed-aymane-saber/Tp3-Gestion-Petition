<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->setupSMTP();
    }

    private function setupSMTP() {
        // Charger la configuration depuis un fichier externe
        $config = require __DIR__ . '/../smtp_config.php';
       
        $this->mailer->isSMTP();
        $this->mailer->Host = $config['host']; 
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $config['username'];
        $this->mailer->Password = $config['password'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $config['port'];
        $this->mailer->setFrom($config['from_address'], $config['from_name']);
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->SMTPDebug = 0; 
    }

    private function wrapInTemplate($content, $subject) {
        return "
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <title>$subject</title>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f7; color: #333; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: auto; background-color: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
                .header { background-color: #4f46e5; color: #fff; padding: 20px; text-align: center; }
                .content { padding: 30px; line-height: 1.6; }
                .footer { background-color: #f8f9fa; text-align: center; padding: 15px; font-size: 12px; color: #6c757d; }
                .code-box { background-color: #e9ecef; border-radius: 5px; padding: 20px; text-align: center; margin: 20px 0; }
                .code { font-size: 32px; font-weight: bold; color: #4f46e5; letter-spacing: 8px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'><h1>$subject</h1></div>
                <div class='content'>$content</div>
                <div class='footer'>&copy; " . date('Y') . " PétitionsEnLigne. Tous droits réservés.</div>
            </div>
        </body>
        </html>";
    }

    public function sendAccountConfirmationCode($email, $name, $code) {
        $subject = 'Confirmez votre inscription';
        $content = "
            <h2>Bonjour $name,</h2>
            <p>Merci de vous être inscrit sur notre plateforme de pétitions. Pour finaliser votre inscription, veuillez utiliser le code de vérification ci-dessous :</p>
            <div class='code-box'>
                <span class='code'>$code</span>
            </div>
            <p>Ce code expirera dans 15 minutes.</p>
            <p>Si vous n'êtes pas à l'origine de cette inscription, veuillez ignorer cet email.</p>
            <p>L'équipe PétitionsEnLigne</p>
        ";

        try {
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->wrapInTemplate($content, $subject);
            $this->mailer->isHTML(true);
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email error to $email: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}