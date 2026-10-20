<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['nik'])) {
    header("Location: ../login/login_siswa.php");
    exit();
}

// Ambil data siswa yang login
$nik = $_SESSION['nik'];
$query_siswa = "SELECT * FROM siswa WHERE nik = '$nik'";
$result_siswa = mysqli_query($koneksi, $query_siswa);
$data_siswa = mysqli_fetch_assoc($result_siswa);

// Ambil notifikasi yang belum dibaca
$query_notifikasi = "SELECT n.*, p.isi_laporan, t.tanggapan 
                     FROM tb_notifikasi_siswa n
                     JOIN pengaduan p ON n.id_pengaduan = p.id_pengaduan
                     JOIN tanggapan t ON p.id_pengaduan = t.id_pengaduan
                     WHERE n.nik = '$nik' AND n.status_tanggapan = 'belum_dibaca'
                     ORDER BY n.tanggal_notifikasi DESC";
$result_notifikasi = mysqli_query($koneksi, $query_notifikasi);

// Fungsi untuk menandai notifikasi sudah dibaca
if(isset($_GET['mark_read']) && isset($_GET['notif_id'])) {
    $notif_id = $_GET['notif_id'];
    $update_query = "UPDATE tb_notifikasi_siswa 
                     SET status_tanggapan = 'sudah_dibaca' 
                     WHERE id_notifikasi = '$notif_id'";
    mysqli_query($koneksi, $update_query);
    
    // Redirect ke halaman riwayat pengaduan
    header("Location: riwayat_pengaduan.php");
    exit();
}

// Hitung jumlah notifikasi belum dibaca
$total_notifikasi = mysqli_num_rows($result_notifikasi);




$nik = $_SESSION['nik'];
$query = "SELECT * FROM siswa WHERE nik = '$nik'";
$result = mysqli_query($koneksi, $query);
$siswa = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']); // Add this line
    $telp = mysqli_real_escape_string($koneksi, $_POST['telp']);
    
    
    $password_update = "";
    if (!empty($_POST['new_password'])) {
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $password_update = ", password = '$new_password'";
    }

    $update_success = true;
    $error_message = "";

    if (!empty($_FILES['foto']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024;
        
        if (!in_array($_FILES['foto']['type'], $allowed_types)) {
            $update_success = false;
            $error_message = "Tipe file tidak didukung. Gunakan JPG, PNG, atau GIF.";
        } elseif ($_FILES['foto']['size'] > $max_size) {
            $update_success = false;
            $error_message = "Ukuran file terlalu besar. Maksimal 5MB.";
        } else {
            $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            // Gunakan nama asli file dengan menambahkan NIK untuk menghindari konflik
            $original_filename = basename($_FILES['foto']['name']);
            $foto_name = $nik . '_' . $original_filename;
            $foto_path = '../uploads/profil/' . $foto_name;
            
            if (!empty($siswa['foto_profil']) && file_exists($siswa['foto_profil']) && $siswa['foto_profil'] != '../uploads/profil/default_profil.jpg') {
                unlink($siswa['foto_profil']);
            }
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)) {
                $update_query = "UPDATE siswa SET 
                                nama = '$nama', 
                                kelas = '$kelas',
                                username = '$username',
                                telp = '$telp',
                                foto_profil = '$foto_path'
                                $password_update 
                                WHERE nik = '$nik'";
            } else {
                $update_success = false;
                $error_message = "Gagal mengupload foto profil.";
            }
        }
    } else {
        $update_query = "UPDATE siswa SET 
                    nama = '$nama', 
                    kelas = '$kelas',
                    username = '$username',
                    email = '$email',
                    telp = '$telp'
                    $password_update 
                    WHERE nik = '$nik'";
    }

    if ($update_success && mysqli_query($koneksi, $update_query)) {
        $_SESSION['success'] = "Profil berhasil diperbarui!";
        header("Location: profil_siswa.php");
        exit();
    } else {
        $error = $error_message ?: "Gagal memperbarui profil: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Siswa - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
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

        .profile-dropdown {
            position: relative;
            display: inline-block;
            
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
            right: 0;
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

    <nav class="navbar">
        <div class="nav-links">
            <div class="menu-left">
                <a href="home_siswa.php"><i class="fas fa-home"></i> Home</a>
                <a href="contact_siswa.php"><i class="fas fa-envelope"></i> Contact</a>
                <a href="pengaduan.php"><i class="fas fa-edit"></i> Pengaduan</a>
                <a href="riwayat_pengaduan.php"><i class="fas fa-history"></i> Riwayat</a>
            </div>

            <div class="menu-right">

                <button id="theme-toggle" class="theme-toggle-btn">
                    <i class="fas fa-sun"></i>
                </button>

                <!-- Tambahkan di dalam menu-right setelah theme-toggle -->
                <div class="notification-dropdown">
                    <button onclick="toggleNotificationDropdown()" class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <?php if($total_notifikasi > 0): ?>
                            <span class="notification-badge"><?php echo $total_notifikasi; ?></span>
                        <?php endif; ?>
                    </button>
                    <div id="notificationDropdown" class="dropdown-content notification-content">
                        <div class="notification-header">
                            <h3>Notifikasi 
                                <?php if($total_notifikasi > 0): ?>
                                    <span class="notification-badge"><?php echo $total_notifikasi; ?></span>
                                <?php endif; ?>
                            </h3>
                        </div>
                        <?php 
                        // Reset pointer result
                        mysqli_data_seek($result_notifikasi, 0);
                        while($notifikasi = mysqli_fetch_assoc($result_notifikasi)): 
                        ?>
                            <a href="?mark_read=1&notif_id=<?php echo $notifikasi['id_notifikasi']; ?>" class="notification-item">
                                <div class="notification-icon">
                                    <i class="fas fa-comment-dots"></i>
                                </div>
                                <div class="notification-text">
                                    <p class="notification-title">Tanggapan Pengaduan</p>
                                    <p class="notification-description">
                                        <?php echo substr($notifikasi['isi_laporan'], 0, 50); ?>...
                                    </p>
                                    <small class="notification-time">
                                        <?php echo date('H:i', strtotime($notifikasi['tanggal_notifikasi'])); ?>
                                    </small>
                                </div>
                            </a>
                        <?php endwhile; ?>
                        
                        <?php if($total_notifikasi == 0): ?>
                            <div class="no-notifications">
                                <p>Tidak ada notifikasi baru</p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($total_notifikasi > 0): ?>
                            <div class="notification-footer">
                                <a href="riwayat_pengaduan.php">Lihat Semua</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="profile-dropdown">
                    <button onclick="toggleProfileDropdown()" class="profile-btn">
                        <?php
                        $nik = $_SESSION['nik'];
                        $query = "SELECT foto_profil FROM siswa WHERE nik = '$nik'";
                        $result = mysqli_query($koneksi, $query);
                        $user = mysqli_fetch_assoc($result);
                        $foto_profil = !empty($user['foto_profil']) ? $user['foto_profil'] : '../uploads/profil/default_profil.jpg';
                        ?>
                        <img src="<?php echo $foto_profil; ?>" alt="Profile" class="profile-image">
                        <?php echo $_SESSION['nama']; ?>
                    </button>
                    <div id="profileDropdown" class="dropdown-content">
                        <a href="profil_siswa.php">
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
            <img src="<?php echo !empty($siswa['foto_profil']) ? $siswa['foto_profil'] : '../uploads/profil/default_profil.jpg'; ?>" 
                 alt="Profile Photo" class="profile-photo">
            
            <ul class="profile-nav">
                <li><a href="profil_siswa.php" class="active"><i class="fas fa-user"></i> Personal Information</a></li>
                <li><a href="change_password.php"><i class="fas fa-lock"></i> Change Password</a></li>
                <li><a href="notifications_history.php"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="security.php"><i class="fas fa-shield-alt"></i> Security</a></li>
            </ul>
        </aside>

        <main class="profile-content">
            <h2 class="section-title">Personal Information</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Profile Photo</label>
                    <input type="file" name="foto" accept="image/*" class="form-control">
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>NIK</label>
                        <input type="text" value="<?php echo $siswa['nik']; ?>" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="nama" value="<?php echo $siswa['nama']; ?>" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas" class="form-control" required>
                            <option value="X" <?php echo ($siswa['kelas'] == 'X') ? 'selected' : ''; ?>>X</option>
                            <option value="XI" <?php echo ($siswa['kelas'] == 'XI') ? 'selected' : ''; ?>>XI</option>
                            <option value="XII" <?php echo ($siswa['kelas'] == 'XII') ? 'selected' : ''; ?>>XII</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo $siswa['username']; ?>" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo $siswa['email']; ?>" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="tel" name="telp" value="<?php echo $siswa['telp']; ?>" class="form-control" required>
                    </div>
            </div>

                <button type="submit" class="btn-update">Update Profile</button>
            </form>
</main>
    </div>

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

       
    </script>
</body>
</html>