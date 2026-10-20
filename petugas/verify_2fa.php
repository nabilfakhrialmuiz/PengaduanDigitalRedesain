<?php
session_start();
include '../koneksi.php';
include 'two_factor_helper.php'; // Tambahkan include untuk fungsi generate_two_factor_code
include 'NotificationServices.php'; // Include notification services

// Konfigurasi (sebaiknya dipindahkan ke file konfigurasi terpisah)
$SMS_API_KEY = 'your_sms_gateway_api_key';
$SMS_API_ENDPOINT = 'https://sms-gateway.example.com/send';
$SMTP_HOST = 'smtp.example.com';
$SMTP_USERNAME = 'your_smtp_username';
$SMTP_PASSWORD = 'your_smtp_password';

// Pastikan pengguna sudah melalui tahap login pertama
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: ../login/login_petugas.php");
    exit();
}

// Jika tidak ada kode 2FA di sesi, generate dan kirim kode baru
if (!isset($_SESSION['two_factor_code'])) {
    $user_id = $_SESSION['temp_user_id'];

    // Ambil detail pengguna
    $query = "SELECT telp, email FROM petugas WHERE id_petugas = '$user_id' AND two_factor_enabled = 1";
    $result = mysqli_query($koneksi, $query);
    $user = mysqli_fetch_assoc($result);

    // Jika 2FA diaktifkan
    if ($user) {
        // Generate kode 2FA
        $two_factor_code = generate_two_factor_code();

        // Kirim SMS
        $sms_service = new SMSNotificationService($SMS_API_KEY, $SMS_API_ENDPOINT);
        $sms_result = $sms_service->sendCode($user['telp'], $two_factor_code);

        // Kirim Email
        $email_service = new EmailNotificationService($SMTP_HOST, $SMTP_USERNAME, $SMTP_PASSWORD);
        $email_result = $email_service->sendCode($user['email'], $two_factor_code);

        // Simpan kode dan waktu kedaluwarsa di database
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $update_query = "UPDATE petugas 
                         SET two_factor_code = '$two_factor_code', 
                             two_factor_code_expiry = '$expiry' 
                         WHERE id_petugas = '$user_id'";
        mysqli_query($koneksi, $update_query);

        // Simpan kode di sesi untuk verifikasi
        $_SESSION['two_factor_code'] = $two_factor_code;
        $_SESSION['two_factor_code_expiry'] = $expiry;
    }
}

// Proses verifikasi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = mysqli_real_escape_string($koneksi, $_POST['verification_code']);
    $user_id = $_SESSION['temp_user_id'];

    // Periksa kode verifikasi
    $query = "SELECT * FROM petugas 
              WHERE id_petugas = '$user_id' 
              AND two_factor_code = '$entered_code' 
              AND two_factor_code_expiry > NOW()
              AND two_factor_enabled = 1";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) == 1) {
        // Kode valid, set session variables
        $petugas = mysqli_fetch_assoc($result);
        
        $_SESSION['id_petugas'] = $petugas['id_petugas'];
        $_SESSION['nama_petugas'] = $petugas['nama_petugas'];
        $_SESSION['level'] = $_SESSION['temp_user_level'];
    
        // Hapus kode 2FA setelah digunakan
        $clear_query = "UPDATE petugas 
                        SET two_factor_code = NULL, 
                            two_factor_code_expiry = NULL 
                        WHERE id_petugas = '$user_id'";
        mysqli_query($koneksi, $clear_query);
    
        // Hapus sesi sementara
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_user_level']);
        unset($_SESSION['two_factor_code']);
        unset($_SESSION['two_factor_code_expiry']);
    
        // Set flag untuk menampilkan modal
        $_SESSION['show_success_modal'] = true;
        
        // Return JSON response for AJAX
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'level' => $_SESSION['level']]);
            exit;
        }
    } else {
        $error = "Kode verifikasi salah atau sudah kadaluarsa";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dua Faktor - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../utama/light-theme.css">
    <script src="../utama/theme-toggle.js"></script>
    <style>
        :root {
            --primary-color: #00ff88;
            --secondary-color: #00ccff;
            --bg-color: #0a0a0a;
            --text-color: #ffffff;
            --accent-color: #ff00ff;
            --dark-bg: #111111;
            --card-bg: rgba(255, 255, 255, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(45deg, #000000, #1a1a1a);
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 20px,
                rgba(0, 255, 136, 0.1) 20px,
                rgba(0, 255, 136, 0.1) 40px
            );
            animation: grid-move 20s linear infinite;
        }

        @keyframes grid-move {
            0% { transform: translateX(-50%) translateY(-50%) rotate(0deg); }
            100% { transform: translateX(-50%) translateY(-50%) rotate(360deg); }
        }

        .verify-container {
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid var(--primary-color);
            border-radius: 15px;
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
        }

        .verify-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .verify-title h2 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 8px;
            color: var(--text-color);
            font-family: 'Roboto', sans-serif;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .input-group label {
            position: absolute;
            top: 1rem;
            left: 1rem;
            color: rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
            pointer-events: none;
            z-index: 0;
        }

        .input-group input:focus + label,
        .input-group input:valid + label {
            top: -1rem;
            left: -0.3rem;
            font-size: 0.75rem;
            color: var(--primary-color);
            background: var(--bg-color);
            padding: 0 0.5rem;
        }

        .input-group input:focus, 
        .input-group input:valid {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.3);
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: var(--dark-bg);
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
        }

        .error {
            color: #ff4444;
            text-align: center;
            margin-bottom: 1rem;
            background: rgba(255, 68, 68, 0.1);
            padding: 1rem;
            border-radius: 8px;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 25px;
            font-family: 'Orbitron', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-btn:hover {
            background: rgba(0, 255, 136, 0.2);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.3);
        }

        .verification-text {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(10px);
            z-index: 1000;
            animation: modalFadeIn 0.3s ease-out;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(10, 10, 10, 0.95);
            border: 2px solid var(--primary-color);
            border-radius: 15px;
            padding: 2rem;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 0 30px rgba(0, 255, 136, 0.3);
        }

        .modal-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            animation: iconPulse 2s infinite;
        }

        .modal-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .modal-message {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .modal-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: var(--dark-bg);
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes iconPulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
    </style>
</head>
<body class="login-page">
    <div class="bg-animation"></div>
    
    <a href="../petugas/login_petugas.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    <div class="verify-container">
        <div class="verify-title">
            <h2>Verifikasi Dua Faktor</h2>
        </div>

        <p class="verification-text">
            Untuk keamanan akun Anda, silakan masukkan kode verifikasi yang telah dikirim ke kontak Anda.
        </p>

        <?php if(isset($error)): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <input type="text" name="verification_code" maxlength="6" pattern="\d{6}" required>
                <label>Masukkan Kode Verifikasi (6 digit)</label>
            </div>
            <button type="submit" class="btn">Verifikasi</button>
        </form>
    </div>
    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="modal-title">Verifikasi Berhasil!</h3>
            <p class="modal-message">Kode verifikasi yang Anda masukkan benar. Klik OK untuk melanjutkan.</p>
            <button id="modalOkBtn" class="modal-btn">OK</button>
        </div>
    </div>
    <script>
        // Fungsi untuk submit form dengan AJAX
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('successModal').style.display = 'block';
                } else {
                    // Handle error case
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                location.reload();
            });
        });

        // Event listener untuk tombol OK pada modal
        document.getElementById('modalOkBtn').addEventListener('click', function() {
            const level = '<?php echo isset($_SESSION["level"]) ? $_SESSION["level"] : ""; ?>';
            if(level === 'admin') {
                window.location.href = '../admin/home_admin.php';
            } else {
                window.location.href = '../petugas/home_petugas.php';
            }
        });
    </script>
</body>
</html>