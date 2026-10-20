<?php
session_start();
include '../koneksi.php';

// Tambahkan parameter asal halaman
$asal = isset($_GET['asal']) ? $_GET['asal'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nik = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $telp = mysqli_real_escape_string($koneksi, $_POST['telp']);

    $query = "INSERT INTO siswa (nik, nama, kelas, username, email, password, telp) 
              VALUES ('$nik', '$nama', '$kelas', '$username', '$email', '$password', '$telp')";

    if (mysqli_query($koneksi, $query)) {
        $_SESSION['pesan'] = "Pendaftaran petugas berhasil!";
        
        // Redirect berdasarkan asal halaman
        if ($asal == 'data_siswa') {
            header("Location: ../datasiswa/data_siswa.php");
        } else {
            header("Location: login_siswa.php");
        }
        exit();
    } else {
        $error = "Error: " . $query . "<br>" . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Siswa - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
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
            overflow-y: visible;
            overflow-y: auto; 
            padding: 20px;
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

        .login-container {
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid var(--primary-color);
            border-radius: 15px;
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
        }

        .login-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-title h2 {
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

        .signup-link {
            text-align: center;
            margin-top: 1rem;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
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
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <a href="<?php 
        // Cek apakah parameter asal sudah di-set
        $asal = isset($_GET['asal']) ? $_GET['asal'] : '';
        
        // Jika berasal dari data_petugas, kembalikan ke data_petugas
        if ($asal == 'data_siswa') {
            echo '../datasiswa/data_siswa.php';
        } else {
            // Default kembali ke halaman utama atau login
            echo '../utama/index.php';
        }
    ?>" class="back-btn">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    <div class="login-container">
        <div class="login-title">
            <h2>Pendaftaran Siswa</h2>
        </div>

        <?php if(isset($error)): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <input type="text" name="nik" required>
                <label>NIK</label>
            </div>
            <div class="input-group">
                <input type="text" name="nama" required>
                <label>Nama Lengkap</label>
            </div>
            <div class="input-group">
                <input type="text" name="kelas" required>
                <label>Kelas</label>
            </div>
            <div class="input-group">
                <input type="text" name="username" required>
                <label>Username</label>
            </div>
            <div class="input-group">
                <input type="email" name="email" required>
                <label>Email</label>
            </div>
            <div class="input-group">
                <input type="password" name="password" required>
                <label>Password</label>
            </div>
            <div class="input-group">
                <input type="tel" name="telp" required>
                <label>Nomor Telepon</label>
            </div>
            <button type="submit" class="btn">Daftar</button>
        </form>

        <div class="signup-link">
            <p>Sudah punya akun? <a href="login_siswa.php">Login</a></p>
        </div>
    </div>
</body>
</html>