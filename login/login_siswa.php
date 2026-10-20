<?php
session_start();
include '../koneksi.php';

// Tambahkan fitur anti-brute force
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
    $error = "Terlalu banyak percobaan login. Silakan coba lagi nanti.";
    if (time() - $_SESSION['last_login_attempt'] < 300) {
        $remaining_time = 300 - (time() - $_SESSION['last_login_attempt']);
        $error .= " Tunggu {$remaining_time} detik.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Reset login attempts jika sudahlewat waktu
    if (isset($_SESSION['last_login_attempt']) && 
        (time() - $_SESSION['last_login_attempt'] > 300)) {
        unset($_SESSION['login_attempts']);
        unset($_SESSION['last_login_attempt']);
    }

    // Increment login attempts
    $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? 
        $_SESSION['login_attempts'] + 1 : 1;
    $_SESSION['last_login_attempt'] = time();

    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM siswa WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) == 1) {
        $siswa = mysqli_fetch_assoc($result);
        if (password_verify($password, $siswa['password'])) {
            // Reset login attempts saat berhasil login
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_login_attempt']);

            $_SESSION['nik'] = $siswa['nik'];
            $_SESSION['nama'] = $siswa['nama'];
            
            // Tambahkan log aktivitas login
            $log_query = "INSERT INTO log_aktivitas (nik, aktivitas, waktu) VALUES ('{$siswa['nik']}', 'Login Siswa', NOW())";
            mysqli_query($koneksi, $log_query);

            header("Location: ../siswa/home_siswa.php");
            exit();
        } else {
            $error = "Password salah";
        }
    } else {
        $error = "Username tidak ditemukan";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Siswa - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            font-family: 'Poppins', sans-serif;
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

        .login-container {
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid var(--primary-color);
            border-radius: 15px;
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
            box-shadow: 0 15px 35px rgba(0, 255, 136, 0.1);
            transition: all 0.3s ease;
        }

        .login-container:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 45px rgba(0, 255, 136, 0.2);
        }

        .login-title {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .login-title h2 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
        }

        .login-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
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

        .input-group .input-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            transition: color 0.3s ease;
            z-index: 2;
        }

        .input-group input:focus + .input-icon,
        .input-group input:valid + .input-icon{
            color: var(--primary-color);
        }

        .input-group input:focus, 
        .input-group input:valid {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.3);
            padding-left: 3rem;
        }

        .input-group label {
            position: absolute;
            top: 25%;
            left: 3rem;
            color: rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
            pointer-events: none;
            z-index: 0;
        }

        .input-group input:focus + label,
        .input-group input:valid + label,
        .input-group select:focus + label,
        .input-group select:valid + label {
            top: -1.1rem;
            left: -0.3rem;
            font-size: 0.75rem;
            color: var(--primary-color);
            background: var(--bg-color);
            padding: 0 0.5rem;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
        }

        .btn i {
            transition: transform 0.3s ease;
        }

        .btn:hover i {
            transform: translateX(5px);
        }

        .error {
            color: #ff4444;
            text-align: center;
            margin-bottom: 1rem;
            background: rgba(255, 68, 68, 0.1);
            padding: 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .error i {
            font-size: 1.5rem;
        }

        .signup-link {
            text-align: center;
            margin-top: 1rem;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .signup-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255, 255, 255, 0.5);
            transition: color 0.3s ease;
            z-index: 3;
        }

        .password-toggle:hover {
            color: var(--primary-color);
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
    <a href="../utama/index.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    
    <div class="login-container">
        <div class="login-title">
            <h2>Login Siswa</h2>
        </div>

        <?php if(isset($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <i class="fas fa-user input-icon"></i>
                <input type="text" name="username" required autocomplete="username">
                <label>Username</label>
            </div>
            <div class="input-group">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="password" required id="password" autocomplete="current-password">
                <label>Password</label>
                <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
            </div>
            <button type="submit" class="btn">
                Login
                <i class="fas fa-sign-in-alt"></i>
            </button>
        </form>

        <div class="signup-link">
            <p>Belum punya akun? <a href="../login/signup.php">
                Daftar Sekarang 
                <i class="fas fa-arrow-right"></i>
            </a></p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.remove('fa-eye');
                passwordToggle.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.remove('fa-eye-slash');
                passwordToggle.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>