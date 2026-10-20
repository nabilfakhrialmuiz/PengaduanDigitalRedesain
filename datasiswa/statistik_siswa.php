<?php
session_start();
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    header("Location: login_petugas.php");
    exit();
}

include '../koneksi.php';

// Fetch total student statistics
$total_siswa_query = "SELECT 
    COUNT(*) as total_students,
    COUNT(DISTINCT kelas) as total_classes,
    AVG(LENGTH(telp)) as avg_contact_length
FROM siswa";
$total_siswa_result = mysqli_query($koneksi, $total_siswa_query);
$total_siswa_stats = mysqli_fetch_assoc($total_siswa_result);

// Fetch students per class
$kelas_query = "SELECT 
    kelas, 
    COUNT(*) as student_count, 
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM siswa), 2) as percentage 
FROM siswa 
GROUP BY kelas 
ORDER BY student_count DESC";
$kelas_result = mysqli_query($koneksi, $kelas_query);

// Fetch email domain distribution
$email_domain_query = "SELECT 
    SUBSTRING_INDEX(email, '@', -1) as email_domain, 
    COUNT(*) as domain_count, 
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM siswa), 2) as percentage 
FROM siswa 
GROUP BY email_domain 
ORDER BY domain_count DESC 
LIMIT 5";
$email_domain_result = mysqli_query($koneksi, $email_domain_query);

// Prepare data for chart
$kelas_chart_data = [];
while ($row = mysqli_fetch_assoc($kelas_result)) {
    $kelas_chart_data[] = $row;
}

$email_domain_chart_data = [];
while ($row = mysqli_fetch_assoc($email_domain_result)) {
    $email_domain_chart_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Siswa - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../utama/light-theme.css">
    <script src="../utama/theme-toggle.js"></script>
    <style>
        /* Reuse the same CSS from data_siswa.php with some modifications */
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

        /* Include the same background animation from data_siswa.php */
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

        /* Reuse navbar styles */
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

        .main-content {
            padding: 9rem 2rem 2rem;
            max-width: 1250px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--primary-color);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: scale(1.03);
            box-shadow: 0 0 15px rgba(0, 255, 136, 0.2);
        }

        .stat-value {
            color: var(--primary-color);
            font-size: 2.5rem;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 0.5rem;
        }
        
        .charts-layout {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1rem;
        }

        .chart-container {
            flex: 1;
            background: var(--card-bg);
            border: 1px solid var(--primary-color);
            border-radius: 10px;
            padding: 1.5rem;
            max-width: calc(50% - 0.5rem);
        }

        .back-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 0.5rem 1.5rem;
            color: var(--bg-color);
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            position: absolute;
            top: 2rem;
            right: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
                <a href="../admin/home_admin.php"><i class="fas fa-home"></i> Home</a>
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
                        <a href="data_siswa.php">
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
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>     
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="stats-grid">
            <div class="stat-card">
                <h3 style="color: var(--secondary-color);">Total Siswa</h3>
                <div class="stat-value"><?php echo $total_siswa_stats['total_students']; ?></div>
                <p>Jumlah siswa terdaftar</p>
            </div>
            <div class="stat-card">
                <h3 style="color: var(--secondary-color);">Total Kelas</h3>
                <div class="stat-value"><?php echo $total_siswa_stats['total_classes']; ?></div>
                <p>Jumlah kelas berbeda</p>
            </div>
            <div class="stat-card">
                <h3 style="color: var(--secondary-color);">Rata-rata Kontak</h3>
                <div class="stat-value"><?php echo round($total_siswa_stats['avg_contact_length'], 2); ?></div>
                <p>Panjang nomor telepon rata-rata</p>
            </div>
        </div>

        <div class="charts-layout">
        <div class="chart-container">
            <h3 style="color: var(--secondary-color); text-align: center; margin-bottom: 1rem;">Distribusi Siswa per Kelas</h3>
            <canvas id="kelasChart"></canvas>
        </div>

        <div class="chart-container">
            <h3 style="color: var(--secondary-color); text-align: center; margin-bottom: 1rem;">Top 5 Email Domain</h3>
            <canvas id="emailDomainChart"></canvas>
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
        
        // Kelas Distribution Chart
        var ctx1 = document.getElementById('kelasChart').getContext('2d');
        var kelasChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return "'".htmlspecialchars($item['kelas'])."'"; }, $kelas_chart_data)); ?>],
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: [<?php echo implode(',', array_map(function($item) { return $item['student_count']; }, $kelas_chart_data)); ?>],
                    backgroundColor: 'rgba(0, 255, 136, 0.6)',
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

        // Email Domain Distribution Chart
        var ctx2 = document.getElementById('emailDomainChart').getContext('2d');
        var emailDomainChart = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return "'".htmlspecialchars($item['email_domain'])."'"; }, $email_domain_chart_data)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_map(function($item) { return $item['domain_count']; }, $email_domain_chart_data)); ?>],
                    backgroundColor: [
                        'rgba(0, 255, 136, 0.6)',
                        'rgba(0, 204, 255, 0.6)',
                        'rgba(255, 0, 255, 0.6)',
                        'rgba(255, 193, 7, 0.6)',
                        'rgba(40, 167, 69, 0.6)'
                    ],
                    borderColor: 'var(--bg-color)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
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