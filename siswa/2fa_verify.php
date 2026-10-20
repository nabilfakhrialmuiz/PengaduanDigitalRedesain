<?php
session_start();
include '../koneksi.php';
require_once '../vendor/autoload.php';

use PragRX\Google2FA\Google2FA;

// Ensure user came through login process
if (!isset($_SESSION['pending_user_nik'])) {
    header("Location: ../login/login_siswa.php");
    exit();
}

$nik = $_SESSION['pending_user_nik'];
$google2fa = new Google2FA();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userCode = $_POST['verification_code'];

    // Fetch user's 2FA secret
    $query = "SELECT two_factor_secret FROM siswa WHERE nik = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($google2fa->verifyKey($user['two_factor_secret'], $userCode)) {
        // Clear pending session
        unset($_SESSION['pending_user_nik']);

        // Fetch full user details
        $query = "SELECT * FROM siswa WHERE nik = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("s", $nik);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Set full login session
        $_SESSION['nik'] = $nik;
        $_SESSION['nama'] = $user['nama'];

        header("Location: home_siswa.php");
        exit();
    } else {
        $error = "Invalid verification code";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>2FA Verification</title>
    <!-- Include your existing CSS -->
</head>
<body>
    <div class="2fa-verify-container">
        <h2>Two-Factor Authentication</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <p>Please enter the 6-digit code from your Google Authenticator app</p>
            <input type="text" name="verification_code" placeholder="6-digit code" required>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>