<?php
session_start();
include 'koneksi.php';

// Total Tanggapan
$total_tanggapan_query = "SELECT COUNT(*) as total FROM tanggapan";
$total_tanggapan_result = mysqli_query($koneksi, $total_tanggapan_query);
$total_tanggapan = mysqli_fetch_assoc($total_tanggapan_result)['total'];

// Tanggapan by Petugas
$petugas_tanggapan_query = "
    SELECT p.nama_petugas, COUNT(t.id_tanggapan) as jumlah_tanggapan 
    FROM tanggapan t
    JOIN petugas p ON t.id_petugas = p.id_petugas
    GROUP BY p.id_petugas
    ORDER BY jumlah_tanggapan DESC
";
$petugas_tanggapan_result = mysqli_query($koneksi, $petugas_tanggapan_query);

// Average Response Time
$avg_response_query = "
    SELECT AVG(TIMESTAMPDIFF(HOUR, pg.tgl_pengaduan, t.tgl_tanggapan)) as avg_response_time 
    FROM pengaduan pg
    JOIN tanggapan t ON pg.id_pengaduan = t.id_pengaduan
";
$avg_response_result = mysqli_query($koneksi, $avg_response_query);
$avg_response_time = mysqli_fetch_assoc($avg_response_result)['avg_response_time'];

// Tanggapan Distribution by Month
$monthly_tanggapan_query = "
    SELECT 
        MONTH(tgl_tanggapan) as bulan, 
        YEAR(tgl_tanggapan) as tahun, 
        COUNT(*) as jumlah_tanggapan 
    FROM tanggapan 
    GROUP BY bulan, tahun 
    ORDER BY tahun, bulan
";
$monthly_tanggapan_result = mysqli_query($koneksi, $monthly_tanggapan_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Tanggapan - PENGADUAN DIGITAL</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js for advanced visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="utama/light-theme.css">
    <script src="utama/theme-toggle.js"></script>
    <style>
        /* Using the same styling as data_siswa.php */
        :root {
            --primary-color: #00ff88;
            --secondary-color: #00ccff;
            --bg-dark: #121212;
            --bg-light: #1e1e1e;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --accent-color: #ff00ff;
            --card-bg: rgba(30, 30, 30, 0.8);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-primary);
            line-height: 1.6;
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .stats-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: var(--bg-light);
            border-radius: 10px;
            margin-top: 4rem;
        }

        .stats-header h1 {
            font-family: 'Space Mono', monospace;
            color: var(--primary-color);
            font-size: 2.5rem;
        }

        .stats-overview {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(0, 255, 136, 0.2);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 255, 136, 0.1);
        }

        .stat-card h3 {
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .stat-card p {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .chart-wrapper {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 1.5rem;
            border: 1px solid rgba(0, 204, 255, 0.2);
        }

        .back-btn {
            position: absolute;
            top: 120px;
            right: 128px;
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

        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }

    </style>
</head>
<body>

<div class="bg-animation"></div>
    
    <!-- Navbar structure remains the same -->
    <nav class="navbar">
        <div class="nav-links">
            <div class="menu-left">
                <a href="admin/home_admin.php"><i class="fas fa-home"></i> Home</a>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                <!-- New Data Management Dropdown -->
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
            </div>


            <div class="menu-right">
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>     
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="stats-header">
            <h1>Statistik Tanggapan</h1>
            <a href="data_tanggapan.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Tanggapan</h3>
                <p><?php echo $total_tanggapan; ?></p>
            </div>
            <div class="stat-card">
                <h3>Rata-rata Waktu Tanggapan</h3>
                <p><?php echo round($avg_response_time, 2); ?> jam</p>
            </div>
            <div class="stat-card">
                <h3>Petugas Terbaik</h3>
                <p><?php 
                    mysqli_data_seek($petugas_tanggapan_result, 0);
                    $best_petugas = mysqli_fetch_assoc($petugas_tanggapan_result);
                    echo $best_petugas['nama_petugas']; 
                ?></p>
            </div>
        </div>

        <div class="charts-container">
            <div class="chart-wrapper">
                <canvas id="petugasTanggapanChart"></canvas>
            </div>
            <div class="chart-wrapper">
                <canvas id="monthlyTanggapanChart"></canvas>
            </div>
        </div>
    </div>


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
        
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: 'white'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: 'white'
                    }
                },
                x: {
                    ticks: {
                        color: 'white'
                    }
                }
            }
        };

        // Petugas Tanggapan Chart
        const petugasTanggapanCtx = document.getElementById('petugasTanggapanChart').getContext('2d');
        new Chart(petugasTanggapanCtx, {
            type: 'bar',
            data: {
                // [Existing data code]
            },
            options: {
                ...chartOptions,
                plugins: {
                    title: {
                        display: true,
                        text: 'Tanggapan per Petugas',
                        color: 'white'
                    }
                }
            }
        });

        // Monthly Tanggapan Chart
        const monthlyTanggapanCtx = document.getElementById('monthlyTanggapanChart').getContext('2d');
        new Chart(monthlyTanggapanCtx, {
            type: 'line',
            data: {
                // [Existing data code]
            },
            options: {
                ...chartOptions,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribusi Tanggapan Bulanan',
                        color: 'white'
                    }
                }
            }
        });
        </script>
    </div>
</body>
</html>