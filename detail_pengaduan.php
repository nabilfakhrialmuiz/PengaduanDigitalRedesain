<?php
session_start();
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    header("Location: login_petugas.php");
    exit();
}

include 'koneksi.php';

if(!isset($_GET['id'])) {
    header("Location: data_pengaduan.php");
    exit();
}

$id_pengaduan = $_GET['id'];
$id_petugas = $_SESSION['id_petugas'];
$query = "SELECT * FROM petugas WHERE id_petugas = '$id_petugas'";
$result = mysqli_query($koneksi, $query);
$petugas = mysqli_fetch_assoc($result);

// Get petugas data including profile photo
$id_petugas = $_SESSION['id_petugas'];
$query_petugas = "SELECT foto_profil FROM petugas WHERE id_petugas = ?";
$stmt = mysqli_prepare($koneksi, $query_petugas);
mysqli_stmt_bind_param($stmt, "i", $id_petugas);
mysqli_stmt_execute($stmt);
$result_petugas = mysqli_stmt_get_result($stmt);
$petugas_data = mysqli_fetch_assoc($result_petugas);

// Fetch pengaduan details with JOIN to get siswa information
$query = "SELECT p.*, s.nama, s.telp as siswa_telp 
          FROM pengaduan p 
          LEFT JOIN siswa s ON p.nik = s.nik 
          WHERE p.id_pengaduan = ?";
          
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id_pengaduan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pengaduan = mysqli_fetch_assoc($result);

// Fetch tanggapan if exists
$query_tanggapan = "SELECT t.*, pt.nama_petugas 
                    FROM tanggapan t 
                    LEFT JOIN petugas pt ON t.id_petugas = pt.id_petugas  
                    WHERE t.id_pengaduan = ? 
                    ORDER BY t.tgl_tanggapan DESC";
                    
$stmt_tanggapan = mysqli_prepare($koneksi, $query_tanggapan);
mysqli_stmt_bind_param($stmt_tanggapan, "i", $id_pengaduan);
mysqli_stmt_execute($stmt_tanggapan);
$result_tanggapan = mysqli_stmt_get_result($stmt_tanggapan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengaduan - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="utama/light-theme.css">
    <script src="utama/theme-toggle.js"></script>
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

        .main-content {
            padding: 9rem 2rem 2rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        .detail-card {
            background: var(--card-bg);
            border-radius: 15px;
            border: 1px solid var(--primary-color);
            padding: 2rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--primary-color);
            padding-bottom: 1rem;
        }

        .card-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-family: 'Orbitron', sans-serif;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .status-process {
            background: rgba(0, 123, 255, 0.2);
            color: #007bff;
        }

        .status-complete {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .info-group {
            margin-bottom: 1.5rem;
        }

        .info-label {
            font-family: 'Orbitron', sans-serif;
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1.1rem;
            word-break: break-word;
        }

        .complaint-photo {
            max-width: 100%;
            border-radius: 10px;
            border: 2px solid var(--primary-color);
        }

        .response-section {
            margin-top: 2rem;
        }

        .response-card {
            background: rgba(0, 255, 136, 0.05);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .response-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-family: 'Orbitron', sans-serif;
            color: var(--primary-color);
        }

        .btn-respond {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: var(--bg-color);
            border: none;
            padding: 0.7rem 2rem;
            border-radius: 25px;
            font-family: 'Orbitron', sans-serif;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-right: 30px;
        }

        .btn-respond:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.2);
        }

        .btn-back {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: var(--bg-color);
            border: none;
            padding: 0.7rem 2rem;
            border-radius: 25px;
            font-family: 'Orbitron', sans-serif;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.2);
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .card-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
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
                <?php if($_SESSION['level'] == 'petugas'): ?>
                    <a href="petugas/home_petugas.php"><i class="fas fa-home"></i> Home</a>
                    <a href="petugas/contact_petugas.php"><i class="fas fa-envelope"></i> Contact</a>
                    <a href="petugas/tanggapan.php"><i class="fas fa-comments"></i> Tanggapan</a>
                    <a href="data_pengaduan.php"><i class="fas fa-file-alt"></i> Data Pengaduan</a>
                <?php elseif($_SESSION['level'] == 'admin'): ?>
                    <a href="admin/home_admin.php"><i class="fas fa-home"></i> Home</a>
                    <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                    
                    <div class="data-management-dropdown">
                        <button onclick="toggleDataDropdown()" class="data-btn">
                            <i class="fas fa-database"></i> Data Management <i class="fas fa-chevron-down"></i>
                        </button>
                        <div id="dataDropdown" class="data-content">
                            <a href="data_tanggapan.php">
                                <i class="fas fa-comments"></i> Data Tanggapan
                            </a>
                            <a href="data_pengaduan.php">
                                <i class="fas fa-file-alt"></i> Data Pengaduan
                            </a>
                            <a href="datasiswa/data_siswa.php">
                                <i class="fas fa-users"></i> Data Siswa
                            </a>
                            <a href="datapetugas/data_petugas.php">
                                <i class="fas fa-user-tie"></i> Data Petugas
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="menu-right">
                <button id="theme-toggle" class="theme-toggle-btn">
                    <i class="fas fa-sun"></i>
                </button>
                <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                </div>
        </div>
    </nav>
    
    <main class="main-content">
        <div class="detail-card">
            <div class="card-header">
                <h1 class="card-title">Detail Pengaduan #<?php echo $pengaduan['id_pengaduan']; ?></h1>
                <span class="status-badge status-<?php echo $pengaduan['status'] == '0' ? 'pending' : ($pengaduan['status'] == 'proses' ? 'process' : 'complete'); ?>">
                    <?php echo $pengaduan['status'] == '0' ? 'Pending' : ($pengaduan['status'] == 'proses' ? 'Proses' : 'Selesai'); ?>
                </span>
            </div>

            <div class="detail-grid">
                <div class="left-column">
                    <div class="info-group">
                        <div class="info-label">Tanggal Pengaduan</div>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($pengaduan['tgl_pengaduan'])); ?></div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">Pelapor</div>
                        <div class="info-value">
                            <?php echo $pengaduan['nama'] ?? 'Nama tidak tersedia'; ?><br>
                            NIK: <?php echo $pengaduan['nik']; ?><br>
                            Telp: <?php echo $pengaduan['siswa_telp'] ?? 'Tidak tersedia'; ?>
                        </div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">Isi Laporan</div>
                        <div class="info-value"><?php echo nl2br(htmlspecialchars($pengaduan['isi_laporan'])); ?></div>
                    </div>
                </div>

                <div class="right-column">
                    <?php if($pengaduan['foto']) { ?>
                        <div class="info-group">
                            <div class="info-label">Bukti Foto</div>
                            <img src="uploads/<?php echo $pengaduan['foto']; ?>" alt="Bukti Pengaduan" class="complaint-photo">
                        </div>
                    <?php } ?>
                </div>
            </div>

            <?php if(mysqli_num_rows($result_tanggapan) > 0) { ?>
                <div class="response-section">
                    <h2 class="card-title">Riwayat Tanggapan</h2>
                    <?php while($tanggapan = mysqli_fetch_assoc($result_tanggapan)) { ?>
                        <div class="response-card">
                            <div class="response-header">
                                <span><?php echo $tanggapan['nama_petugas']; ?></span>
                                <span><?php echo date('d/m/Y H:i', strtotime($tanggapan['tgl_tanggapan'])); ?></span>
                            </div>
                            <div class="info-value">
                                <?php echo nl2br(htmlspecialchars($tanggapan['tanggapan'])); ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <div style="text-align: center; margin-top: 2rem;">
            <?php if($pengaduan['status'] != 'selesai' && $_SESSION['level'] == 'petugas') { ?>
                <a href="petugas/tanggapan.php?id=<?php echo $pengaduan['id_pengaduan']; ?>" class="btn-respond">
                    <i class="fas fa-comment"></i> Beri Tanggapan
                </a>
            <?php } ?>
                <a href="data_pengaduan.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </main>

    <!-- Tombol Back to Top -->
        <button id="backToTop" class="back-to-top" title="Kembali ke Atas">
            <i class="fas fa-arrow-up"></i>
        </button>

    <script>

        // Add this new function for Data Management dropdown
        function toggleDataDropdown() {
            const dropdown = document.getElementById('dataDropdown');
            dropdown.classList.toggle('active');
        }

        // Update click outside listener to include data management dropdown
        document.addEventListener('click', function(event) {
            const dataDropdown = document.getElementById('dataDropdown');
            const dataBtn = document.querySelector('.data-btn');
            const profileDropdown = document.getElementById('profileDropdown');
            const profileBtn = document.querySelector('.profile-btn');
            
            if (!dataBtn.contains(event.target) && !dataDropdown.contains(event.target)) {
                dataDropdown.classList.remove('active');
            }
            
            if (!profileBtn.contains(event.target) && !profileDropdown.contains(event.target)) {
                profileDropdown.classList.remove('active');
            }
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

        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            window.location.href = `data_pengaduan.php?search=${search}&status=${status}`;
        }

        function exportToExcel() {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            window.location.href = `export_pengaduan.php?search=${search}&status=${status}`;
        }

        // Add event listener for enter key on search input
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
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