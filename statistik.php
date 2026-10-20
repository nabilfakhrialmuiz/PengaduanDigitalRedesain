<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['id_petugas']) || $_SESSION['level'] != 'admin') {
    header("Location: login_petugas.php");
    exit();
}

// Query untuk mendapatkan statistik tanggapan per bulan
$query_monthly = "SELECT 
    DATE_FORMAT(tgl_tanggapan, '%Y-%m') as bulan,
    COUNT(*) as jumlah_tanggapan
    FROM tanggapan 
    GROUP BY DATE_FORMAT(tgl_tanggapan, '%Y-%m')
    ORDER BY bulan DESC 
    LIMIT 12";
$result_monthly = mysqli_query($koneksi, $query_monthly);
$monthly_data = mysqli_fetch_all($result_monthly, MYSQLI_ASSOC);

// Query untuk mendapatkan statistik tanggapan per petugas
$query_petugas = "SELECT 
    p.nama_petugas,
    COUNT(t.id_tanggapan) as jumlah_tanggapan
    FROM petugas p
    LEFT JOIN tanggapan t ON p.id_petugas = t.id_petugas
    GROUP BY p.id_petugas, p.nama_petugas
    ORDER BY jumlah_tanggapan DESC";
$result_petugas = mysqli_query($koneksi, $query_petugas);
$petugas_data = mysqli_fetch_all($result_petugas, MYSQLI_ASSOC);

// Query untuk rata-rata tanggapan per hari
$query_avg_daily = "SELECT AVG(daily_count) as avg_daily FROM (
    SELECT DATE(tgl_tanggapan) as tanggal, COUNT(*) as daily_count
    FROM tanggapan
    GROUP BY DATE(tgl_tanggapan)
) as daily_stats";
$result_avg_daily = mysqli_query($koneksi, $query_avg_daily);
$avg_daily = mysqli_fetch_assoc($result_avg_daily)['avg_daily'];

// Query untuk total tanggapan
$query_total = "SELECT COUNT(*) as total FROM tanggapan";
$result_total = mysqli_query($koneksi, $query_total);
$total_tanggapan = mysqli_fetch_assoc($result_total)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Tanggapan - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --text-color: #333;
            --bg-color: #f4f4f4;
        }
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: var(--text-color);
        }

        /* Navbar styles - sama dengan data_tanggapan.php */
        .navbar {
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
        }
        
        .navbar.hidden {
            transform: translateY(-100%);
        }
        
        .nav-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 0 1rem;
        }

        .menu-left {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .menu-right {
            margin-left: auto;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            margin: 0 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }

        .nav-links a:hover {
            background-color: var(--hover-color);
            transform: translateY(-2px);
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: var(--secondary-color);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .nav-links a:hover::after {
            transform: scaleX(1);
        }
        


        /* Sidebar styles - sama dengan data_tanggapan.php */
        .sidebar {
            flex: 0 0 250px;
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 10px;
            transition: transform 0.3s ease-in-out;
            position: fixed;
            
            left: -232px;
            top: 80px;
            bottom: 0;
            overflow-y: visible;
            overflow-y: auto; /* Mengaktifkan scroll jika konten lebih panjang */
            max-height: 100vh; /* Membatasi tinggi sidebar agar sesuai dengan viewport */
            scrollbar-width: thin; /* (Opsional) Menyesuaikan lebar scroll bar */
        }

        
       
        .sidebar.active {
            transform: translateX(250px);
        }
        
        .sidebar a {
            display: block;
            color: white;
            padding: 1rem;
            text-decoration: none;
            transition: background-color 0.3s ease;
            border-radius: 5px;
        }
        
        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .main-content {
            flex: 1;
            padding: 2rem;
            margin-left: 10px;
            transition: margin-left 0.3s ease-in-out;
            border-radius: 10px;
        }
        
        .main-content.shifted {
            margin-left: 260px;
        }
        
        .toggle-sidebar {
            position: fixed;
            left: 20px;
            top: 80px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1001;
            transition: left 0.3s ease-in-out;
        }
        
        .toggle-sidebar.active {
            left: 100px;
        }
        
        /* Konten Statistik */
        .main-content {
            margin-left: 10px;
            padding: 2rem;
            padding-top: 100px;
            transition: margin-left 0.3s ease-in-out;
        }

        .main-content.shifted {
            margin-left: 260px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stats-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stats-card h3 {
            margin: 0 0 1rem 0;
            color: var(--primary-color);
        }

        .chart-container {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .toggle-sidebar {
            position: fixed;
            left: 20px;
            top: 80px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1001;
            transition: left 0.3s ease-in-out;
        }

        .toggle-sidebar.active {
            left: 270px;
        }

        @media screen and (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .main-content.shifted {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-links">
            <div class="menu-left">
                <a href="home_admin.php"><i class="fas fa-home"></i> Home</a>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                <a href="data_tanggapan.php"><i class="fas fa-comments"></i> Data Tanggapan</a>
                <a href="data_pengaduan.php"><i class="fas fa-file-alt"></i> Data Pengaduan</a>
                <a href="data_siswa.php"><i class="fas fa-users"></i> Data Siswa</a>
                <a href="data_petugas.php"><i class="fas fa-user-tie"></i> Data Petugas</a>
            </div>
            <div class="menu-right">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <button class="toggle-sidebar" id="toggleSidebar">
        <i class="fas fa-bars"></i>
    </button>

    <div class="main-container">
        <aside class="sidebar" id="sidebar">
            <a href="data_tanggapan.php"><i class="fas fa-comments"></i> Data Tanggapan</a>
            <a href="#" id="searchLink"><i class="fas fa-search"></i> Pencarian</a>
            <a href="statistik.php" class="active"><i class="fas fa-chart-bar"></i> Statistik</a>
            <a href="#add"><i class="fas fa-plus"></i> Tambah Data</a>
            <a href="#settings"><i class="fas fa-cog"></i> Setting</a>
            <a href="#help"><i class="fas fa-question-circle"></i> Bantuan</a>
        </aside>

        <main class="main-content" id="mainContent">
            <h1>Statistik Tanggapan</h1>

            <div class="stats-container">
                <div class="stats-card">
                    <h3><i class="fas fa-comments"></i> Total Tanggapan</h3>
                    <p class="stats-number"><?php echo number_format($total_tanggapan); ?></p>
                </div>
                
                <div class="stats-card">
                    <h3><i class="fas fa-chart-line"></i> Rata-rata Tanggapan per Hari</h3>
                    <p class="stats-number"><?php echo number_format($avg_daily, 1); ?></p>
                </div>
            </div>

            <div class="chart-container">
                <h3>Jumlah Tanggapan per Bulan</h3>
                <canvas id="monthlyChart"></canvas>
            </div>

            <div class="chart-container">
                <h3>Distribusi Tanggapan per Petugas</h3>
                <canvas id="petugasChart"></canvas>
            </div>
        </main>
    </div>

    <script>
        // Navbar scroll behavior
        let lastScrollTop = 0;
        const navbar = document.querySelector('.navbar');
        const navbarHeight = navbar.offsetHeight;

        window.addEventListener('scroll', () => {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > navbarHeight) {
                // Scrolling down
                navbar.classList.add('hidden');
            } else {
                // Scrolling up
                navbar.classList.remove('hidden');
            }
            
            lastScrollTop = scrollTop;
        });

         // Collapsible sidebar
         const toggleSidebar = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('shifted');
            toggleSidebar.classList.toggle('active');
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !toggleSidebar.contains(e.target) && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                mainContent.classList.remove('shifted');
                toggleSidebar.classList.remove('active');
            }
        });

        // Responsive behavior
        function handleResponsive() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
                mainContent.classList.remove('shifted');
                toggleSidebar.classList.remove('active');
            }
        }

        window.addEventListener('resize', handleResponsive);
        handleResponsive(); // Call on initial load

        // Chart data
        const monthlyData = <?php echo json_encode($monthly_data); ?>;
        const petugasData = <?php echo json_encode($petugas_data); ?>;

        // Monthly chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => {
                    const date = new Date(item.bulan + '-01');
                    return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Jumlah Tanggapan',
                    data: monthlyData.map(item => item.jumlah_tanggapan),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Petugas chart
        const petugasCtx = document.getElementById('petugasChart').getContext('2d');
        new Chart(petugasCtx, {
            type: 'bar',
            data: {
                labels: petugasData.map(item => item.nama_petugas),
                datasets: [{
                    label: 'Jumlah Tanggapan',
                    data: petugasData.map(item => item.jumlah_tanggapan),
                    backgroundColor: '#764ba2',
                    borderColor: '#667eea',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>