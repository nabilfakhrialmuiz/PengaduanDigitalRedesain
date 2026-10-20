<?php
session_start();
include '../koneksi.php';

// Tambahkan parameter asal halaman
$asal = isset($_GET['asal']) ? $_GET['asal'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_petugas = mysqli_real_escape_string($koneksi, $_POST['id_petugas']);
    $nama_petugas = mysqli_real_escape_string($koneksi, $_POST['nama_petugas']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $telp = mysqli_real_escape_string($koneksi, $_POST['telp']);
    $level = mysqli_real_escape_string($koneksi, $_POST['level']);

    $query = "INSERT INTO petugas (id_petugas, nama_petugas, username, email,  password, telp, level) 
              VALUES ('$id_petugas', '$nama_petugas', '$username', '$email', '$password', '$telp', '$level')";

    
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['pesan'] = "Pendaftaran petugas berhasil!";
        
        // Redirect berdasarkan asal halaman
        if ($asal == 'data_petugas') {
            header("Location: ../datapetugas/data_petugas.php");
        } else {
            header("Location: login_petugas.php");
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
    <title>Pendaftaran Petugas - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
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
            padding: 2rem;
            width: 100%;
            max-width: 800px;
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

        form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .input-group {
            position: relative;
        }

        .input-group input, 
        .input-group select {
            width: 100%;
            padding: 0.8rem;
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
            top: 0.8rem;
            left: 1rem;
            color: rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
            pointer-events: none;
            z-index: 0;
        }

        .input-group input:focus + label,
        .input-group input:valid + label,
        .input-group select:focus + label,
        .input-group select:valid + label {
            top: -1rem;
            left: 0.1rem;
            font-size: 0.75rem;
            color: var(--primary-color);
            background: var(--bg-color);
            padding: 0 0.5rem;
        }

        .btn-container {
            grid-column: span 2;
            text-align: center;
        }

        .btn {
            width: 50%;
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

        .signup-link {
            grid-column: span 2;
            text-align: center;
            margin-top: 1rem;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
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

        .error {
            grid-column: span 2;
            color: #ff4444;
            text-align: center;
            margin-bottom: 1rem;
            background: rgba(255, 68, 68, 0.1);
            padding: 1rem;
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            form {
                grid-template-columns: 1fr;
            }
            
            .btn-container,
            .signup-link,
            .error {
                grid-column: span 1;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <a href="<?php 
        $asal = isset($_GET['asal']) ? $_GET['asal'] : '';
        if ($asal == 'data_petugas') {
            echo '../datapetugas/data_petugas.php';
        } else {
            echo '../utama/index.php';
        }
    ?>" class="back-btn">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    <div class="login-container">
        <div class="login-title">
            <h2>Pendaftaran Petugas</h2>
        </div>

        <?php if(isset($error)): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <input type="text" name="id_petugas" required>
                <label>ID Petugas</label>
            </div>
            <div class="input-group">
                <input type="text" name="nama_petugas" required>
                <label>Nama Petugas</label>
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
            <div class="input-group">
                <select name="level" required>
                    <option value="" disabled selected></option>
                    <option value="admin">Admin</option>
                    <option value="petugas">Petugas</option>
                </select>
                <label>Level</label>
            </div>
            <div class="btn-container">
                <button type="submit" class="btn">Daftar</button>
            </div>
            <div class="signup-link">
                <p>Sudah punya akun? <a href="login_petugas.php">Login</a></p>
            </div>
        </form>
    </div>
</body>
</html>