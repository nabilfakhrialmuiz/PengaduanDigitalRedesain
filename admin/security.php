<?php
session_start();
if (!isset($_SESSION['id_petugas'])) {
    header("Location: login_petugas.php");
    exit();
}

include '../koneksi.php';

$id_petugas = $_SESSION['id_petugas'];
$query = "SELECT * FROM petugas WHERE id_petugas = '$id_petugas'";
$result = mysqli_query($koneksi, $query);
$petugas = mysqli_fetch_assoc($result);

// Definisikan status 2FA
$two_factor_enabled = $petugas['two_factor_enabled'] ?? false;

// Fungsi untuk mencatat aktivitas keamanan
function logSecurityActivity($koneksi, $id_petugas, $activity_type, $description) {
    $timestamp = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $query = "INSERT INTO security_logs (id_petugas, activity_type, description, ip_address, timestamp) 
              VALUES ('$id_petugas', '$activity_type', '$description', '$ip_address', '$timestamp')";
    mysqli_query($koneksi, $query);
}

// Proses request keamanan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        // Aktifkan Two-Factor Authentication
        case 'enable_2fa':
            $secret_key = bin2hex(random_bytes(16)); // Generate secret key
            $update_query = "UPDATE petugas 
            SET two_factor_secret = '$secret_key', 
                two_factor_enabled = 1 
            WHERE id_petugas = '$id_petugas'";
            
            if (mysqli_query($koneksi, $update_query)) {
                logSecurityActivity($koneksi, $id_petugas, 'TWO_FACTOR', 'Two-Factor Authentication Enabled');
                $_SESSION['success'] = "Two-Factor Authentication berhasil diaktifkan!";
            } else {
                $_SESSION['error'] = "Gagal mengaktifkan Two-Factor Authentication.";
            }
            break;

        // Nonaktifkan Two-Factor Authentication
        case 'disable_2fa':
            $update_query = "UPDATE petugas SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE id_petugas = '$id_petugas'";
            
            if (mysqli_query($koneksi, $update_query)) {
                logSecurityActivity($koneksi, $id_petugas, 'TWO_FACTOR', 'Two-Factor Authentication Disabled');
                $_SESSION['success'] = "Two-Factor Authentication berhasil dinonaktifkan!";
            } else {
                $_SESSION['error'] = "Gagal menonaktifkan Two-Factor Authentication.";
            }
            break;

        // Logout dari semua perangkat
        case 'logout_all_devices':
            $new_session_token = bin2hex(random_bytes(16));
            $update_query = "UPDATE petugas SET session_token = '$new_session_token' WHERE id_petugas = '$id_petugas'";
            
            if (mysqli_query($koneksi, $update_query)) {
                logSecurityActivity($koneksi, $id_petugas, 'LOGOUT', 'Logout dari semua perangkat');
                session_destroy();
                header("Location: ../login/login_petugas.php");
                exit();
            }
            break;

            // Tambahkan case baru dalam switch statement
            case 'delete_logs':
                if (isset($_POST['selected_logs']) && is_array($_POST['selected_logs'])) {
                    // Siapkan array ID log yang akan dihapus
                    $log_ids = array_map('mysqli_real_escape_string', array_fill(0, count($_POST['selected_logs']), $koneksi), $_POST['selected_logs']);
                    $ids_string = implode(',', $log_ids);

                    // Query untuk menghapus log yang dipilih
                    $delete_query = "DELETE FROM security_logs 
                                    WHERE id IN ($ids_string) 
                                    AND id_petugas = '$id_petugas'";
                    
                    if (mysqli_query($koneksi, $delete_query)) {
                        logSecurityActivity($koneksi, $id_petugas, 'LOG_MANAGEMENT', 'Menghapus log aktivitas terpilih');
                        $_SESSION['success'] = "Log berhasil dihapus!";
                    } else {
                        $_SESSION['error'] = "Gagal menghapus log.";
                    }
                }
                break;

            // Tambahan untuk hapus semua log
            case 'delete_all_logs':
                $delete_all_query = "DELETE FROM security_logs WHERE id_petugas = '$id_petugas'";
                
                if (mysqli_query($koneksi, $delete_all_query)) {
                    logSecurityActivity($koneksi, $id_petugas, 'LOG_MANAGEMENT', 'Menghapus semua log aktivitas');
                    $_SESSION['success'] = "Semua log berhasil dihapus!";
                } else {
                    $_SESSION['error'] = "Gagal menghapus semua log.";
                }
                break;
    }
}

// Ambil log aktivitas keamanan
$logs_query = "SELECT * FROM security_logs 
               WHERE id_petugas = '$id_petugas' 
               ORDER BY timestamp DESC 
               LIMIT 10";
$logs_result = mysqli_query($koneksi, $logs_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Petugas - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../utama/light-theme.css">
    <script src="../utama/theme-toggle.js"></script>
    <style>
        /* Using the same cyberpunk styling from profil_siswa.php */
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
            overflow-x: hidden;
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
            0% {
                transform: translateX(-50%) translateY(-50%) rotate(0deg);
            }
            100% {
                transform: translateX(-50%) translateY(-50%) rotate(360deg);
            }
        }

        .navbar {
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--primary-color);
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .nav-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .menu-left a {
            color: var(--text-color);
            text-decoration: none;
            padding: 0.5rem 1rem;
            position: relative;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .menu-left a::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .menu-left a:hover::before {
            width: 100%;
        }

         /* New Data Management Dropdown Styles */
         .data-management-dropdown {
            position: relative;
            display: inline-block;
        }

        .data-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            color: var(--text-color);
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .data-content {
            display: none;
            position: absolute;
            min-width: 200px;
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid var(--primary-color);
            border-radius: 15px;
            overflow: hidden;
            z-index: 1000;
            transform: translateY(10px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .data-content.active {
            display: block;
            transform: translateY(0);
            opacity: 1;
        }

        .data-content a {
            color: var(--text-color);
            padding: 1rem 2rem;
            text-decoration: none;
            display: block;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .data-content a:hover {
            background: rgba(0, 255, 136, 0.1);
        }

        .profile-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 0.3rem 1rem;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .profile-btn img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
        }

        .menu-right {
            display: flex;
            align-items: center;
            gap: 1rem; /* Adds some space between theme toggle and profile dropdown */
        }

        .theme-toggle-btn {
            background: transparent;
            color: var(--text-color);
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            transition: all 0.3s ease;
            transition: all 0.3s ease;
            
        }

        .theme-toggle-btn:hover {
            color: var(--primary-color);
            transform: rotate(180deg);
        }


        .dropdown-content {
            display: none;
            position: absolute;
            right: 4.5rem;
            min-width: 200px;
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid var(--primary-color);
            border-radius: 15px;
            overflow: hidden;
            z-index: 1000;
            transform: translateY(10px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .dropdown-content.active {
            display: block;
            transform: translateY(0);
            opacity: 1;
        }

        .dropdown-content a {
            color: var(--text-color);
            padding: 1rem 2rem;
            text-decoration: none;
            display: block;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dropdown-content a::before {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
            transform: translateX(-100%);
            transition: 0.5s;
        }

        .dropdown-content a:hover::before {
            transform: translateX(100%);
        }

        .dropdown-content a:hover {
            background: rgba(0, 255, 136, 0.1);
            padding-left: 2.5rem;
        }

        .dropdown-content a i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .main-content {
            padding: 9rem 2rem 2rem;
            max-width: 1250px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        .profile-sidebar {
            background: var(--card-bg);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 15px;
            padding: 2rem;
            height: fit-content;
            backdrop-filter: blur(10px);
        }

        .profile-photo {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            margin: 0 auto 2rem;
            display: block;
            object-fit: cover;
            border: 3px solid var(--primary-color);
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
        }

        .profile-nav {
            list-style: none;
        }

        .profile-nav a {
            display: flex;
            align-items: center;
            padding: 1rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            font-family: 'Orbitron', sans-serif;
        }

        .profile-nav a:hover,
        .profile-nav a.active {
            background: linear-gradient(45deg, rgba(0, 255, 136, 0.1), rgba(0, 204, 255, 0.1));
            transform: translateX(10px);
        }

        .profile-nav i {
            margin-right: 1rem;
            color: var(--primary-color);
        }

        .profile-content {
            background: var(--card-bg);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 15px;
            padding: 2rem;
            backdrop-filter: blur(10px);
        }

        .section-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-size: 1.8rem;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
        }

        .security-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .security-card {
            background: var(--card-bg);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            width: 380px;
        }

        .security-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 2rem;
            border-bottom: 1px solid rgba(0, 255, 136, 0.1);
            background: linear-gradient(45deg, rgba(0, 255, 136, 0.1), rgba(0, 204, 255, 0.1));
        }

        .security-header i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .security-content {
            padding: 2rem;
        }

        .security-description {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 1.5rem;
        }

        .security-form {
            margin-bottom: 1rem;
        }

        .btn-security {
            width: 100%;
            padding: 1rem;
            border-radius: 10px;
            font-family: 'Orbitron', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: var(--dark-bg);
        }

        .btn-danger {
            background: linear-gradient(45deg, #ff0055, #ff3366);
            color: var(--text-color);
        }

        .btn-security:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.4);
        }

        .security-status {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-family: 'Orbitron', sans-serif;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .status-badge.active {
            background: rgba(0, 255, 136, 0.2);
            color: var(--primary-color);
        }

        .status-badge.inactive {
            background: rgba(255, 0, 85, 0.2);
            color: #ff0055;
        }

        .security-logs-container .security-card {
            width: 100%;
        }

        .security-logs-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .security-logs-table thead {
            background: linear-gradient(45deg, rgba(0, 255, 136, 0.1), rgba(0, 204, 255, 0.1));
        }

        .security-logs-table th, 
        .security-logs-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 255, 136, 0.1);
        }

        .log-management-actions {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
            margin-bottom: 10px;
            padding: 10px;
            background: rgba(0, 255, 136, 0.05);
        }

        .log-management-actions button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #selectAllLogs {
            background-color: #4CAF50;
            color: white;
        }

        #deleteSelectedLogs {
            background-color: #FF5722;
            color: white;
        }

        #deleteAllLogs {
            background-color: #F44336;
            color: white;
        }

        .security-logs-table th:first-child,
        .security-logs-table td:first-child {
            width: 50px;
            text-align: center;
        }

        @media (max-width: 992px) {
            .security-container {
                grid-template-columns: 1fr;
            }
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 8px;
            color: var(--text-color);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.2);
        }

        .btn-update {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: var(--dark-bg);
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            margin: 2rem auto 0;
            min-width: 200px;
        }

        .btn-update:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.4);
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(5px);
        }

        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .alert-error {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid #ff0000;
            color: #ff0000;
        }

        :root {
            --primary-color: #3498db;
            --delete-color: #e74c3c;
            --select-color: #2ecc71;
            --text-color: #2c3e50;
            --background-light: #f4f6f7;
            --border-color: #ecf0f1;
        }

        /* Custom Checkbox Styling */
        .checkbox-container {
            display: block;
            position: relative;
            padding-left: 25px;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 18px;
            width: 18px;
            background-color: white;
            border: 2px solid var(--border-color);
            border-radius: 4px;
        }

        .checkbox-container:hover input ~ .checkmark {
            background-color: var(--background-light);
        }

        .checkbox-container input:checked ~ .checkmark {
            background-color: var(--select-color);
            border-color: var(--select-color);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .checkbox-container input:checked ~ .checkmark:after {
            display: block;
        }

        .checkbox-container .checkmark:after {
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        @keyframes logout-pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .btn-logout-animation {
            animation: logout-pulse 0.5s ease-in-out;
        }

        @media (max-width: 992px) {
            .main-content {
                grid-template-columns: 1fr;
                padding: 7rem 1rem 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                margin-bottom: 2rem;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                flex-direction: column;
                gap: 1rem;
            }

            .menu-left {
                flex-wrap: wrap;
                justify-content: center;
            }

            .menu-right {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    <nav class="navbar">
        <div class="nav-links">
        <div class="menu-left">
                <a href="home_admin.php"><i class="fas fa-home"></i> Home</a>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                <!-- New Data Management Dropdown -->
                <div class="data-management-dropdown">
                    <button onclick="toggleDataDropdown()" class="data-btn">
                        <i class="fas fa-database"></i> Data Management <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="dataDropdown" class="data-content">
                        <a href="../data_tanggapan.php">
                            <i class="fas fa-comments"></i> Data Tanggapan
                        </a>
                        <a href="../data_pengaduan.php">
                            <i class="fas fa-file-alt"></i> Data Pengaduan
                        </a>
                        <a href="../datasiswa/data_siswa.php">
                            <i class="fas fa-users"></i> Data Siswa
                        </a>
                        <a href="../datapetugas/data_petugas.php">
                            <i class="fas fa-user-tie"></i> Data Petugas
                        </a>
                    </div>
                </div>
            </div>

            <div class="menu-right">
                <button id="theme-toggle" class="theme-toggle-btn">
                    <i class="fas fa-sun"></i>
                </button>
                <div class="profile-dropdown">
                    <button onclick="toggleProfileDropdown()" class="profile-btn">
                        <?php
                        $id_petugas = $_SESSION['id_petugas'];
                        $query = "SELECT foto_profil FROM petugas WHERE id_petugas = '$id_petugas'";
                        $result = mysqli_query($koneksi, $query);
                        $user = mysqli_fetch_assoc($result);
                        $foto_profil = !empty($user['foto_profil']) ? $user['foto_profil'] : '../uploads/profil/default_profil.jpg';
                        ?>
                        <img src="<?php echo $foto_profil; ?>" alt="Profile" class="profile-image">
                        <?php echo $_SESSION['nama_petugas']; ?>
                    </button>
                    <div id="profileDropdown" class="dropdown-content">
                        <a href="profil_admin.php">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <aside class="profile-sidebar">
            <img src="<?php echo ! empty($petugas['foto_profil']) ? $petugas['foto_profil'] : '../uploads/profil/default_profil.jpg'; ?>" 
                 alt="Profile Photo" class="profile-photo">
            
            <ul class="profile-nav">
                <li><a href="profil_admin.php" ><i class="fas fa-user"></i> Personal Information</a></li>
                <li><a href="change_password.php"><i class="fas fa-lock"></i> Change Password</a></li>
                <li><a href="notifications_history.php"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="security.php" class="active"><i class="fas fa-shield-alt"></i> Security</a></li>
            </ul>
        </aside>

        <main class="profile-content">
            <h2 class="section-title">Keamanan Akun</h2>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="security-container">
                <div class="security-card">
                    <div class="security-header">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Autentikasi Dua Faktor (2FA)</h3>
                    </div>
                    <div class="security-content">
                        <p class="security-description">
                            Aktifkan Two-Factor Authentication untuk tingkatkan keamanan akun Anda.
                        </p>
                        <form method="POST" class="security-form">
                            <input type="hidden" name="action" value="<?php echo $two_factor_enabled ? 'disable_2fa' : 'enable_2fa'; ?>">
                            <button type="submit" class="btn-security <?php echo $two_factor_enabled ? 'btn-danger' : 'btn-primary'; ?>">
                                <?php echo $two_factor_enabled ? 'Nonaktifkan 2FA' : 'Aktifkan 2FA'; ?>
                            </button>
                        </form>
                        <div class="security-status">
                            Status: 
                            <span class="status-badge <?php echo $two_factor_enabled ? 'active' : 'inactive'; ?>">
                                <?php echo $two_factor_enabled ? 'Aktif' : 'Tidak Aktif'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="security-card">
                    <div class="security-header">
                        <i class="fas fa-sign-out-alt"></i>
                        <h3>Logout dari Semua Perangkat</h3>
                    </div>
                    <div class="security-content">
                        <p class="security-description">
                            Batalkan akses di semua perangkat yang sedang login.
                        </p>
                        <form method="POST" class="security-form">
                            <input type="hidden" name="action" value="logout_all_devices">
                            <button type="submit" class="btn-security btn-danger">
                                Logout Semua Perangkat
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="security-logs-container">
                <div class="security-card">
                    <div class="security-header">
                        <i class="fas fa-history"></i>
                        <h3>Riwayat Aktivitas</h3>
                    </div>
                    
                    <!-- Form untuk hapus log -->
                    <form id="logManagementForm" method="POST">
                        <div class="log-management-actions">
                            <button type="button" id="selectAllLogs">Pilih Semua</button>
                            <button type="submit" name="action" value="delete_logs" id="deleteSelectedLogs">Hapus Terpilih</button>
                            <button type="submit" name="action" value="delete_all_logs" id="deleteAllLogs">Hapus Semua</button>
                        </div>

                        <div class="security-logs-content">
                            <table class="security-logs-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAllCheckbox">
                                        </th>
                                        <th>Waktu</th>
                                        <th>Jenis Aktivitas</th>
                                        <th>Deskripsi</th>
                                        <th>Alamat IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($log = mysqli_fetch_assoc($logs_result)): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_logs[]" value="<?php echo $log['id']; ?>" class="log-checkbox">
                                            </td>
                                            <td><?php echo $log['timestamp']; ?></td>
                                            <td><?php echo $log['activity_type']; ?></td>
                                            <td><?php echo $log['description']; ?></td>
                                            <td><?php echo $log['ip_address']; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </main>

    <script>
        // Tambahkan fungsi toggleNotificationDropdown
       function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('active');
        }

        // Modifikasi event listener untuk menutup dropdown
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const profileDropdown = document.getElementById('profileDropdown');
            const notificationBtn = document.querySelector('.notification-btn');
            const profileBtn = document.querySelector('.profile-btn');
            
            if (!notificationBtn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }

            if (!profileBtn.contains(event.target) && !profileDropdown.contains(event.target)) {
                profileDropdown.classList.remove('active');
            }
        });

        // Prevent dropdown close when clicking inside
        document.getElementById('notificationDropdown').addEventListener('click', function(event) {
            event.stopPropagation();
        });
        
        // Dropdown toggle function
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const profileBtn = document.querySelector('.profile-btn');
            
            if (!profileBtn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });

        // Prevent dropdown close when clicking inside
        document.getElementById('profileDropdown').addEventListener('click', function(event) {
            event.stopPropagation();
        });

        document.querySelector('.btn-danger').addEventListener('click', function(e) {
            if (!confirm('Anda yakin ingin logout dari semua perangkat? Ini akan mengakhiri semua sesi aktif Anda.')) {
                e.preventDefault();
            } else {
                this.classList.add('btn-logout-animation');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const selectAllLogsBtn = document.getElementById('selectAllLogs');
        const logCheckboxes = document.querySelectorAll('.log-checkbox');
        const deleteSelectedLogsBtn = document.getElementById('deleteSelectedLogs');
        const deleteAllLogsBtn = document.getElementById('deleteAllLogs');

        // Fungsi untuk memilih/membatalkan semua log
        function toggleAllLogs(checked) {
            logCheckboxes.forEach(checkbox => {
                checkbox.checked = checked;
                checkbox.closest('tr').classList.toggle('selected', checked);
            });
        }

        // Event listener untuk tombol "Pilih Semua"
        selectAllLogsBtn.addEventListener('click', function() {
            const isCurrentlySelected = logCheckboxes[0].checked;
            toggleAllLogs(!isCurrentlySelected);
            selectAllCheckbox.checked = !isCurrentlySelected;
        });

        // Event listener untuk checkbox "Pilih Semua" di header
        selectAllCheckbox.addEventListener('change', function() {
            toggleAllLogs(this.checked);
        });

        // Event listener untuk setiap checkbox log
        logCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                this.closest('tr').classList.toggle('selected', this.checked);
                selectAllCheckbox.checked = Array.from(logCheckboxes).every(cb => cb.checked);
            });
        });

        // Konfirmasi sebelum menghapus log terpilih
        deleteSelectedLogsBtn.addEventListener('click', function(e) {
            const selectedLogs = document.querySelectorAll('.log-checkbox:checked');
            if (selectedLogs.length === 0) {
                alert('Pilih setidaknya satu log untuk dihapus');
                e.preventDefault();
            } else if (!confirm('Apakah Anda yakin ingin menghapus log yang dipilih?')) {
                e.preventDefault();
            }
        });

        // Konfirmasi sebelum menghapus semua log
        deleteAllLogsBtn.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus SEMUA log aktivitas?')) {
                e.preventDefault();
            }
        });
    });
        
    </script>
</body>
</html>