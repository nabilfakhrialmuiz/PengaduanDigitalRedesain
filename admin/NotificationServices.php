<?php
// File: NotificationServices.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

class SMSNotificationService {
    private $api_key;
    private $api_endpoint;

    public function __construct($api_key, $api_endpoint) {
        $this->api_key = $api_key;
        $this->api_endpoint = $api_endpoint;
    }

    public function sendCode($phone_number, $code) {
        $message = "Kode verifikasi PENGADUAN DIGITAL Anda adalah: {$code}";
        
        // Implementasi pengiriman SMS (sesuaikan dengan gateway SMS Anda)
        $ch = curl_init($this->api_endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'phone' => $phone_number,
            'message' => $message,
            'api_key' => $this->api_key
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

class EmailNotificationService {
    private $smtp_host;
    private $smtp_username;
    private $smtp_password;

    public function __construct($smtp_host, $smtp_username, $smtp_password) {
        $this->smtp_host = $smtp_host;
        $this->smtp_username = $smtp_username;
        $this->smtp_password = $smtp_password;
    }

    public function sendCode($email, $code) {
        require __DIR__ . '/../vendor/autoload.php';


        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $this->smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtp_username;
            $mail->Password   = $this->smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('noreply@pengaduandigital.com', 'PENGADUAN DIGITAL');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Kode Verifikasi Anda';
            $mail->Body    = "Kode verifikasi Anda adalah: <strong>{$code}</strong>";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Pengiriman email gagal: " . $mail->ErrorInfo);
            return false;
        }
    }
}