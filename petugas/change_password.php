<?php
session_start();
if (!isset($_SESSION['id_petugas'])) {
    header("Location: login_petugas.php");
    exit();
}

include '../koneksi.php';

function getPetugasNotifications($koneksi, $id_petugas) {
    $query = "SELECT np.*, p.isi_laporan, s.nama 
              FROM tb_notifikasi_petugas np
              JOIN pengaduan p ON np.id_pengaduan = p.id_pengaduan
              JOIN siswa s ON p.nik = s.nik
              WHERE np.id_petugas = '$id_petugas' 
              AND np.status_notifikasi = 'baru'
              ORDER BY np.tanggal_notifikasi DESC";
    
    return mysqli_query($koneksi, $query);
}

// Dalam halaman home_petugas.php
// Ambil ID notifikasi dari URL atau parameter request

$notifications = getPetugasNotifications($koneksi, $_SESSION['id_petugas']);
$notification_count = mysqli_num_rows($notifications);

if(isset($_GET['id_notifikasi'])) {
    $id_notifikasi = $_GET['id_notifikasi'];
    markNotificationAsRead($koneksi, $id_notifikasi);
}

function markNotificationAsRead($koneksi, $id_notifikasi) {
    $query = "UPDATE tb_notifikasi_petugas 
              SET status_notifikasi = 'dibaca'
              WHERE id_notifikasi = '$id_notifikasi'";
    mysqli_query($koneksi, $query);
}

$id_petugas = $_SESSION['id_petugas'];
$query = "SELECT * FROM petugas WHERE id_petugas = '$id_petugas'";
$result = mysqli_query($koneksi, $query);
$petugas = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (password_verify($current_password, $petugas['password'])) {
        // Check if new passwords match
        if ($new_password === $confirm_password) {
            // Check password strength
            if (strlen($new_password) >= 8) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE petugas SET password = '$hashed_password' WHERE id_petugas = '$id_petugas'";
                
                if (mysqli_query($koneksi, $update_query)) {
                    $_SESSION['success'] = "Password berhasil diubah!";
                    header("Location: change_password.php");
                    exit();
                } else {
                    $error = "Gagal mengubah password: " . mysqli_error($koneksi);
                }
            } else {
                $error = "Password baru harus minimal 8 karakter!";
            }
        } else {
            $error = "Password baru tidak cocok dengan konfirmasi password!";
        }
    } else {
        $error = "Password saat ini tidak valid!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../utama/light-theme.css">
    <script src="../utama/theme-toggle.js"></script>
    <style>
        /* Using the same cyberpunk styling from profil_petugas.php */
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

       /* Navbar Styles */
        /* Navbar Styles */
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

        .nav-links a {
            color: var(--text-color);
            text-decoration: none;
            padding: 0.5rem 1rem;
            position: relative;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .nav-links a::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::before {
            width: 100%;
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

        .notification-dropdown {
            position: relative;
        }

        .notification-btn {
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
            position: relative;
        }

        .notification-btn:hover {
            color: var(--primary-color);
        }

        .notification-badge {
            position: absolute;
            top: 3.5px;
            right: 3px;
            background-color: var(--primary-color);
            color: var(--bg-color);
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
        }

        .notification-content {
            min-width: 300px;
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 255, 136, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            text-decoration: none;
            color: var(--text-color);
            transition: background-color 0.3s ease;
            border-bottom: 1px solid rgba(0, 255, 136, 0.1);
        }

        .notification-item:hover {
            background-color: rgba(0, 255, 136, 0.1);
        }

        .notification-icon {
            margin-right: 1rem;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .notification-text {
            flex-grow: 1;
        }

        .notification-title {
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        .notification-description {
            font-size: 0.9rem;
            color: var(--secondary-color);
            margin-bottom: 0.25rem;
        }

        .notification-time {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .no-notifications {
            text-align: center;
            padding: 1rem;
            color: var(--secondary-color);
        }

        .notification-footer {
            padding: 1rem;
            text-align: center;
            border-top: 1px solid rgba(0, 255, 136, 0.2);
        }

        .notification-footer a {
            color: var(--primary-color);
            text-decoration: none;
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

        /* Navbar and other styles remain the same as in profil_petugas.php */
        /* ... Copy all navbar and common styles from profil_petugas.php ... */

        .password-field {
            position: relative;
        }

        .password-field .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--primary-color);
            background: none;
            border: none;
            padding: 5px;
        }

        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.8rem;
            font-family: 'Orbitron', sans-serif;
            color: rgba(255, 255, 255, 0.7);
        }

        .requirement {
            display: flex;
            align-items: center;
            margin: 0.2rem 0;
        }

        .requirement i {
            margin-right: 0.5rem;
            font-size: 0.7rem;
        }

        .requirement.valid {
            color: var(--primary-color);
        }

        .requirement.invalid {
            color: #ff4444;
        }

        .password-strength {
            height: 5px;
            background: rgba(255, 255, 255, 0.1);
            margin-top: 0.5rem;
            border-radius: 3px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            background: linear-gradient(90deg, #ff4444, #ffbb33, #00C851, var(--primary-color));
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

    <!-- Copy navbar section from profil_petugas.php -->
    <nav class="navbar">
        <div class="nav-links">
            <div class="menu-left">
                <a href="home_petugas.php"><i class="fas fa-home"></i> Home</a>
                <a href="contact_petugas.php"><i class="fas fa-envelope"></i> Contact</a>
                <a href="tanggapan.php"><i class="fas fa-comments"></i> Tanggapan</a>
                <a href="../data_pengaduan.php"><i class="fas fa-file-alt"></i> Data Pengaduan</a>
            </div>

            <div class="menu-right">
                <button id="theme-toggle" class="theme-toggle-btn">
                    <i class="fas fa-sun"></i>
                </button>
                <div class="notification-dropdown">
                    <button onclick="toggleNotificationDropdown()" class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <?php if($notification_count > 0): ?>
                            <span class="notification-badge"><?php echo $notification_count; ?></span>
                        <?php endif; ?>
                    </button>
                    
                    <div id="notificationDropdown" class="dropdown-content notification-content">
                        <div class="notification-header">
                            <h3>Notifikasi</h3>
                            <span class="badge"><?php echo $notification_count; ?></span>
                        </div>
                        
                        <?php 
                        // Reset pointer untuk query notifikasi
                        mysqli_data_seek($notifications, 0);
                        if($notification_count > 0): 
                            while($notif = mysqli_fetch_assoc($notifications)): 
                        ?>
                            <a href="tanggapan.php?id_notifikasi=<?php echo $notif['id_notifikasi']; ?>" class="notification-item">
                                <div class="notification-icon">
                                    <i class="fas fa-comment"></i>
                                </div>
                                <div class="notification-text">
                                    <div class="notification-title">Pengaduan Baru</div>
                                    <div class="notification-description">
                                        Dari <?php echo $notif['nama']; ?>
                                    </div>
                                    <div class="notification-time">
                                        <?php 
                                        // Tambahkan fungsi untuk menampilkan waktu relatif
                                        $tanggal_notifikasi = strtotime($notif['tanggal_notifikasi']);
                                        $selisih = time() - $tanggal_notifikasi;
                                        
                                        if($selisih < 60) {
                                            echo "Baru saja";
                                        } elseif($selisih < 3600) {
                                            echo floor($selisih / 60) . " menit yang lalu";
                                        } elseif($selisih < 86400) {
                                            echo floor($selisih / 3600) . " jam yang lalu";
                                        } else {
                                            echo floor($selisih / 86400) . " hari yang lalu";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </a>
                        <?php 
                            endwhile; 
                        else:
                        ?>
                            <div class="no-notifications">
                                Tidak ada notifikasi baru
                            </div>
                        <?php endif; ?>
                        
                        <div class="notification-footer">
                            <a href="../data_pengaduan.php">Lihat Semua Pengaduan</a>
                        </div>
                    </div>
                </div>
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
                        <a href="profil_petugas.php">
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
            <img src="<?php echo !empty($petugas['foto_profil']) ? $petugas['foto_profil'] : '../uploads/profil/default_profil.jpg'; ?>" 
                 alt="Profile Photo" class="profile-photo">
            
            <ul class="profile-nav">
                <li><a href="profil_petugas.php"><i class="fas fa-user"></i> Personal Information</a></li>
                <li><a href="change_password.php" class="active"><i class="fas fa-lock"></i> Change Password</a></li>
                <li><a href="notifications_history.php"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="security.php"><i class="fas fa-shield-alt"></i> Security</a></li>
            </ul>
        </aside>

        <main class="profile-content">
            <h2 class="section-title">Change Password</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label>Current Password</label>
                    <div class="password-field">
                        <input type="password" name="current_password" class="form-control" required>
                        <button type="button" class="toggle-password" onclick="togglePassword(this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <div class="password-field">
                        <input type="password" name="new_password" class="form-control" id="new_password" 
                               required onkeyup="checkPasswordStrength(this.value)">
                        <button type="button" class="toggle-password" onclick="togglePassword(this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="password-requirements">
                        <div class="requirement" id="length">
                            <i class="fas fa-circle"></i> Minimal 8 karakter
                        </div>
                        <div class="requirement" id="uppercase">
                            <i class="fas fa-circle"></i> Minimal 1 huruf besar
                        </div>
                        <div class="requirement" id="lowercase">
                            <i class="fas fa-circle"></i> Minimal 1 huruf kecil
                        </div>
                        <div class="requirement" id="number">
                            <i class="fas fa-circle"></i> Minimal 1 angka
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="password-field">
                        <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword(this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-update">Update Password</button>
            </form>
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

        function togglePassword(button) {
            const input = button.parentElement.querySelector('input');
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function checkPasswordStrength(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password)
            };

            // Update requirement indicators
            Object.keys(requirements).forEach(req => {
                const element = document.getElementById(req);
                if (requirements[req]) {
                    element.classList.add('valid');
                    element.classList.remove('invalid');
                    element.querySelector('i').className = 'fas fa-check-circle';
                } else {
                    element.classList.add('invalid');
                    element.classList.remove('valid');
                    element.querySelector('i').className = 'fas fa-times-circle';
                }
            });

            // Calculate strength percentage
            const strength = Object.values(requirements).filter(Boolean).length * 25;
            document.getElementById('strengthBar').style.width = `${strength}%`;
        }

        function validateForm() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                alert('Password baru dan konfirmasi password tidak cocok!');
                return false;
            }

            if (newPassword.length < 8) {
                alert('Password baru harus minimal 8 karakter!');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>