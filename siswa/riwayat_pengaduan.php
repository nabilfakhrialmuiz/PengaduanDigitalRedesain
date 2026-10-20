<?php
session_start();
include '../koneksi.php';

$nik = $_SESSION['nik'];

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
// Modifikasi query untuk mendapatkan foto bersama dengan data lainnya
$query = "SELECT p.*, t.tanggapan, t.tgl_tanggapan 
          FROM pengaduan p 
          LEFT JOIN tanggapan t ON p.id_pengaduan = t.id_pengaduan 
          WHERE p.nik = '$nik' 
          ORDER BY p.tgl_pengaduan DESC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengaduan - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Tambahkan Lightbox CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
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

        /* Navbar Styles (Same as home_siswa.php) */
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
            padding: 9rem 0.5rem 0.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .riwayat-container {
            background: var(--card-bg);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 10px;
            padding: 3rem;
            backdrop-filter: blur(5px);
        }

        .riwayat-table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-color);
        }

        .riwayat-table th {
            background: rgba(0, 255, 136, 0.1);
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 255, 136, 0.2);
            font-family: 'Orbitron', sans-serif;
        }

        .riwayat-table td {
            padding: 1.5rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .status-proses {
            color: #ffcc00;
            font-weight: bold;
        }

        .status-selesai {
            color: #00ff88;
            font-weight: bold;
        }

        .tanggapan-section {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--primary-color);
            padding: 1rem;
            margin-top: 1rem;
            font-style: italic;
        }

        .tanggapan-date {
            color: var(--secondary-color);
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
        }

        .pengaduan-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid var(--primary-color);
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .pengaduan-image:hover {
            transform: scale(1.05);
        }

        .image-preview {
            padding: 0.5rem;
            background: rgba(0, 255, 136, 0.1);
            border-radius: 10px;
            display: inline-block;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90vh;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            color: var(--primary-color);
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
            z-index: 2001;
            background: rgba(0, 0, 0, 0.5);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--primary-color);
        }

        .close:hover {
            color: var(--secondary-color);
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .riwayat-table {
                font-size: 0.8rem;
            }
            .riwayat-table th, .riwayat-table td {
                padding: 0.5rem;
            }
            .pengaduan-image {
                width: 80px;
                height: 80px;
            }
        }

        /* Tombol Back to Top */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: var(--bg-color);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .back-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
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
                        $query_foto = "SELECT foto_profil FROM siswa WHERE nik = '$nik'";
                        $result_foto = mysqli_query($koneksi, $query_foto);
                        $user = mysqli_fetch_assoc($result_foto);
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
        <div class="riwayat-container">
            <h2 style="margin-bottom: 1rem; font-family: 'Orbitron', sans-serif;">Riwayat Pengaduan</h2>
            <table class="riwayat-table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>ID Pengaduan</th>
                        <th>Tanggal</th>
                        <th>Isi Laporan</th>
                        <th>Status</th>
                        <th>Tanggapan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <div class="image-preview">
                                    <?php if($row['foto']): ?>
                                        <a href="../uploads/<?php echo $row['foto']; ?>" data-lightbox="pengaduan" data-title="Pengaduan ID: <?php echo $row['id_pengaduan']; ?>">
                                            <img src="../uploads/<?php echo $row['foto']; ?>" 
                                                 alt="Bukti Pengaduan" 
                                                 class="pengaduan-image">
                                        </a>
                                    <?php else: ?>
                                        <img src="../../uploads/no-image.jpg" 
                                             alt="No Image" 
                                             class="pengaduan-image">
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo $row['id_pengaduan']; ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tgl_pengaduan'])); ?></td>
                            <td><?php echo substr($row['isi_laporan'], 0, 100) . (strlen($row['isi_laporan']) > 100 ? '...' : ''); ?></td>
                            <td>
                                <span class="status-<?php echo $row['status']; ?>">
                                    <?php 
                                    echo $row['status'] == 'proses' ? 'Sedang Diproses' : 'Selesai'; 
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if($row['tanggapan']): ?>
                                    <div class="tanggapan-section">
                                        <div class="tanggapan-date">
                                            <?php echo date('d M Y', strtotime($row['tgl_tanggapan'])); ?>
                                        </div>
                                        <?php echo $row['tanggapan']; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="status-proses">Belum ada tanggapan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <p>Belum ada pengaduan yang dibuat</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>


    <!-- Tombol Back to Top -->
    <button id="backToTop" class="back-to-top" title="Kembali ke Atas">
        <i class="fas fa-arrow-up"></i>
    </button>

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

        // Konfigurasi Lightbox
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'albumLabel': "Gambar %1 dari %2",
            'fadeDuration': 300
        });

        // Fungsi Back to Top
        document.addEventListener('DOMContentLoaded', function() {
            const backToTopButton = document.getElementById('backToTop');

            // Tampilkan/sembunyikan tombol berdasarkan scroll
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.add('show');
                } else {
                    backToTopButton.classList.remove('show');
                }
            });

            // Fungsi scroll smooth ke atas
            backToTopButton.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>