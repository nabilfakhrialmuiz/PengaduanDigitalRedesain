<?php
session_start();
require_once '../vendor/autoload.php';
include '../koneksi.php';

use PragRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorAuth {
    private $google2fa;

    public function __construct() {
        $this->google2fa = new Google2FA();
    }

    public function generateSecretKey() {
        return $this->google2fa->generateSecretKey();
    }

    public function generateQRCode($email, $secretKey) {
        $company = 'PENGADUAN DIGITAL';
        $qrCodeUrl = $this->google2fa->getQRCodeUrl($company, $email, $secretKey);

        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new ImagickImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeImage = $writer->writeString($qrCodeUrl);

        return base64_encode($qrCodeImage);
    }

    public function verifyCode($secretKey, $userCode) {
        return $this->google2fa->verifyKey($secretKey, $userCode);
    }
}

$nik = $_SESSION['nik'];
$twoFA = new TwoFactorAuth();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['generate_secret'])) {
        // Generate and save secret key
        $secretKey = $twoFA->generateSecretKey();
        $qrCode = $twoFA->generateQRCode($_SESSION['email'], $secretKey);

        // Store secret key temporarily in session
        $_SESSION['temp_2fa_secret'] = $secretKey;

        echo json_encode([
            'secret' => $secretKey, 
            'qr_code' => $qrCode
        ]);
        exit;
    }

    if (isset($_POST['verify_code'])) {
        $secretKey = $_SESSION['temp_2fa_secret'];
        $userCode = $_POST['verification_code'];

        if ($twoFA->verifyCode($secretKey, $userCode)) {
            // Update database with new 2FA secret and enable 2FA
            $updateQuery = "UPDATE siswa SET 
                two_factor_secret = ?, 
                two_factor_enabled = TRUE 
                WHERE nik = ?";
            
            $stmt = $koneksi->prepare($updateQuery);
            $stmt->bind_param("ss", $secretKey, $nik);
            $stmt->execute();

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid verification code']);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>2FA Setup</title>
    <!-- Include your existing CSS -->
</head>
<body>
    <div class="2fa-setup-container">
        <h2>Two-Factor Authentication Setup</h2>
        
        <div id="qr-code-section" style="display:none;">
            <p>Scan this QR code with Google Authenticator:</p>
            <img id="qr-code-image" src="" alt="QR Code">
            <p>Or manually enter this secret key: <span id="secret-key"></span></p>
            
            <form id="verify-code-form">
                <input type="text" name="verification_code" placeholder="Enter 6-digit code" required>
                <button type="submit">Verify and Enable 2FA</button>
            </form>
        </div>

        <button id="generate-secret-btn">Start 2FA Setup</button>
    </div>

    <script>
        document.getElementById('generate-secret-btn').addEventListener('click', function() {
            fetch('', {
                method: 'POST',
                body: new URLSearchParams({generate_secret: 1})
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('qr-code-image').src = 'data:image/png;base64,' + data.qr_code;
                document.getElementById('secret-key').textContent = data.secret;
                document.getElementById('qr-code-section').style.display = 'block';
                this.style.display = 'none';
            });
        });

        document.getElementById('verify-code-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('verify_code', 1);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('2FA successfully enabled!');
                    window.location.href = 'security.php';
                } else {
                    alert('Verification failed: ' + data.message);
                }
            });
        });
    </script>
</body>
</html>