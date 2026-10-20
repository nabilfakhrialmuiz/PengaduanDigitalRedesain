<?php
session_start();
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    header("Location: login_petugas.php");
    exit();
}

include 'koneksi.php';

// Fetch petugas data for profile
$id_petugas = $_SESSION['id_petugas'];
$query_petugas = "SELECT * FROM petugas WHERE id_petugas = ?";
$stmt = mysqli_prepare($koneksi, $query_petugas);
mysqli_stmt_bind_param($stmt, "i", $id_petugas);
mysqli_stmt_execute($stmt);
$result_petugas = mysqli_stmt_get_result($stmt);
$petugas = mysqli_fetch_assoc($result_petugas);

// Statistics Queries
$total_pengaduan_query = "SELECT COUNT(*) as total FROM pengaduan";
$status_count_query = "SELECT status, COUNT(*) as count FROM pengaduan GROUP BY status";
$monthly_pengaduan_query = "SELECT 
    YEAR(tgl_pengaduan) as year, 
    MONTH(tgl_pengaduan) as month, 
    COUNT(*) as total 
    FROM pengaduan 
    GROUP BY YEAR(tgl_pengaduan), MONTH(tgl_pengaduan) 
    ORDER BY year, month";

$total_pengaduan = mysqli_fetch_assoc(mysqli_query($koneksi, $total_pengaduan_query))['total'];
$status_result = mysqli_query($koneksi, $status_count_query);
$monthly_result = mysqli_query($koneksi, $monthly_pengaduan_query);

$status_counts = [
    'pending' => 0,
    'proses' => 0,
    'selesai' => 0
];

while($row = mysqli_fetch_assoc($status_result)) {
    $status = $row['status'] == '0' ? 'pending' : $row['status'];
    $status_counts[$status] = $row['count'];
}

$monthly_data = [];
while($row = mysqli_fetch_assoc($monthly_result)) {
    $monthly_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Pengaduan - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="utama/light-theme.css">
    <script src="utama/theme-toggle.js"></script>
    <style>
        /* Reuse all the styles from data_pengaduan.php */
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


        /* ... (copy all the existing CSS from data_pengaduan.php) ... */
        .main-content {
            padding: 4.5rem 2rem 2rem;
            max-width: 1250px;
            margin: 0 auto;
        }

        .stats-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: var(--bg-light);
            border-radius: 10px;
        }

        .stats-header h1 {
            font-family: 'Space Mono', monospace;
            color: var(--primary-color);
            font-size: 2.5rem;
        }


        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stats-card {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid rgba(0, 255, 136, 0.2);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 255, 136, 0.1);
        }

        .stats-card-title {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .stats-card-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--text-color);
        }

        .chart-container {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            border: 1px solid rgba(0, 255, 136, 0.2);
        }

        .back-btn {
            position: absolute;
            top: 150px;
            right: 85px;
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
                            <a href="data_petugas.php">
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
    
        <!-- Copy the entire navbar section from data_pengaduan.php here -->
        <!-- This ensures complete consistency -->
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
                <div class="profile-dropdown">
                    <button onclick="toggleDropdown()" class="profile-btn">
                        <img src="<?php echo !empty($petugas['foto_profil']) ? $petugas['foto_profil'] : 'uploads/profil/default_profil.jpg'; ?>" 
                            alt="Profile" class="profile-image">
                        <?php echo $petugas['nama_petugas']; ?>
                    </button>
                    <div id="profileDropdown" class="dropdown-content">
                        <?php if($_SESSION['level'] == 'petugas'): ?>
                            <a href="profil_petugas.php">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                        <?php elseif($_SESSION['level'] == 'admin'): ?>
                            <a href="profil_admin.php">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                        <?php endif; ?>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="stats-header">
            <h1>Statistik Pengaduan</h1>
            <a href="data_pengaduan.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        
        <div class="stats-grid">
            <div class="stats-card">
                <div class="stats-card-title">Total Pengaduan</div>
                <div class="stats-card-number"><?php echo $total_pengaduan; ?></div>
            </div>
            <div class="stats-card">
                <div class="stats-card-title">Pengaduan Proses</div>
                <div class="stats-card-number"><?php echo $status_counts['proses']; ?></div>
            </div>
            <div class="stats-card">
                <div class="stats-card-title">Pengaduan Selesai</div>
                <div class="stats-card-number"><?php echo $status_counts['selesai']; ?></div>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="monthlyPengaduanChart"></canvas>
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

        function toggleExportDropdown() {
            const dropdown = document.getElementById('exportDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        // Tambahkan event listener untuk menutup dropdown saat klik di luar
        document.addEventListener('click', function(event) {
            const exportDropdown = document.getElementById('exportDropdown');
            const exportBtn = document.querySelector('.export-dropdown-btn');
            
            if (!exportBtn.contains(event.target) && !exportDropdown.contains(event.target)) {
                exportDropdown.style.display = 'none';
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

        // Monthly Pengaduan Chart
        const monthlyData = <?php echo json_encode($monthly_data); ?>;
        const ctx = document.getElementById('monthlyPengaduanChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(item => `${item.year}-${item.month}`),
                datasets: [{
                    label: 'Jumlah Pengaduan per Bulan',
                    data: monthlyData.map(item => item.total),
                    backgroundColor: 'rgba(0, 255, 136, 0.5)',
                    borderColor: 'var(--primary-color)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: 'var(--text-color)'
                        }
                    },
                    x: {
                        ticks: {
                            color: 'var(--text-color)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'var(--text-color)'
                        }
                    }
                }
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