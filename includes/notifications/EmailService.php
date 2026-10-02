<?php

class EmailService {
    public static function sendEmail($to, $subject, $message) {
        $mode = getenv('NOTIFICATION_MODE') ?: 'development';
        
        if ($mode === 'development') {
            return ['status' => true, 'provider_id' => 'DEV_EMAIL_' . uniqid(), 'error' => null];
        }

        $host = getenv('MAIL_HOST');
        $user = getenv('MAIL_USERNAME');
        $pass = getenv('MAIL_PASSWORD');
        $from = getenv('MAIL_FROM_EMAIL');
        $fromName = getenv('MAIL_FROM_NAME');

        if (empty($host) || empty($user) || empty($pass)) {
            return ['status' => false, 'provider_id' => null, 'error' => 'Email credentials not configured'];
        }

        // Mock SMTP send. In real life we'd use PHPMailer here.
        // We'd do:
        // $mail = new PHPMailer(true);
        // $mail->isSMTP(); ...
        // $mail->send();
        
        return ['status' => true, 'provider_id' => 'PROD_EMAIL_' . uniqid(), 'error' => null];
    }
}
