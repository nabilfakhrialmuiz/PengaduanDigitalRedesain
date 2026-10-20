<?php
session_start();

include '../koneksi.php';

if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    header("Location: login_petugas.php");
    exit(); 
}

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

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Petugas - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
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

        /* Profile Button Styles */
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
        /* Hero Section */
        .hero {
            height: 50vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            overflow: hidden;
            background: linear-gradient(45deg, rgba(0, 255, 136, 0.1), rgba(0, 204, 255, 0.1));
            padding-top: 80px;
        }

        .hero-content h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: glow 2s ease-in-out infinite alternate;
        }


        /* Contact Container */
        .contact-container {
            max-width: 1200px;
            margin: -20px auto 4rem;
            position: relative;
            z-index: 2;
            padding: 0 2rem;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .contact-item {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 15px;
            border: 1px solid rgba(0, 255, 136, 0.2);
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .contact-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.2);
        }

        .contact-item i {
            font-size: 2.5rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
        }

        .contact-item h3 {
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        /* Map Container */
        .map-container {
            background: var(--card-bg);
            padding: 1rem;
            border-radius: 15px;
            border: 1px solid rgba(0, 255, 136, 0.2);
            backdrop-filter: blur(5px);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .map-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.2);
        }

        .map-container iframe {
            width: 100%;
            height: 450px;
            border-radius: 10px;
            border: none;
        }

        /* Footer */
        footer {
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(10px);
            padding: 2rem;
            text-align: center;
            border-top: 1px solid var(--primary-color);
        }

        @media screen and (max-width: 768px) {
            .nav-links {
                flex-direction: column;
                gap: 1rem;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .contact-container {
                margin-top: -50px;
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>

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

    <div class="hero">
        <div class="hero-content">
            <h1>Hubungi Kami</h1>
            <p>Informasi kontak SMK Negeri 6 Kota Bekasi</p>
        </div>
    </div>

    <div class="contact-container">
        <div class="contact-info">
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Alamat</h3>
                <p>Jl. Kusuma Utara X No.1, RT.007/RW.011, Duren Jaya, Kec. Bekasi Tim., Kota Bks, Jawa Barat 17111</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <h3>Telepon</h3>
                <p>(021) 88397040</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <h3>Email</h3>
                <p>info@smkn6kotabekasi.sch.id</p>
            </div>
        </div>

        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.2878014459998!2d107.00738731476914!3d-6.229636995493865!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e698c71cf814d97%3A0x88c422fe72939789!2sSMK%20Negeri%206%20Kota%20Bekasi!5e0!3m2!1sen!2sid!4v1634558530000!5m2!1sen!2sid" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Layanan Pengaduan Digital | SMK NEGERI 6 KOTA BEKASI</p>
    </footer>

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