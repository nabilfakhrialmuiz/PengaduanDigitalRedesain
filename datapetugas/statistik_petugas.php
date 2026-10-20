<?php
session_start();
if(!isset($_SESSION['id_petugas']) || $_SESSION['level'] != 'admin') {
    header("Location: ../login/login_petugas.php");
    exit();
}

include '../koneksi.php';

// Statistik Jumlah Petugas per Level
$level_stats = [];
$level_query = "SELECT level, COUNT(*) as jumlah FROM petugas GROUP BY level";
$level_result = mysqli_query($koneksi, $level_query);
while ($row = mysqli_fetch_assoc($level_result)) {
    $level_stats[$row['level']] = $row['jumlah'];
}

// Statistik Petugas Terbaru
$latest_petugas_query = "SELECT id_petugas, nama_petugas, username, email, level, foto_profil, DATE_FORMAT(created_at, '%d %M %Y') as tanggal_bergabung 
                         FROM petugas 
                         ORDER BY created_at DESC 
                         LIMIT 5";
$latest_petugas_result = mysqli_query($koneksi, $latest_petugas_query);

// Total Jumlah Petugas
$total_petugas_query = "SELECT COUNT(*) as total FROM petugas";
$total_petugas_result = mysqli_query($koneksi, $total_petugas_query);
$total_petugas = mysqli_fetch_assoc($total_petugas_result)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Petugas - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <!-- Reuse existing CSS from data_petugas.php -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js">
    <link rel="stylesheet" href="../utama/light-theme.css">
    <script src="../utama/theme-toggle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .main-content {
            padding: 9rem 2rem 2rem;
            max-width: 1250px;
            margin: 0 auto;
        }

        /* Tambahkan gaya untuk statistik */
        .statistics-container {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .total-stats {
            display: flex;
            gap: 1rem;
            justify-content: space-between;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--primary-color);
            border-radius: 10px;
            padding: 1.5rem;
            flex: 1;
        }

        .stat-card h3 {
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--text-color);
        }

        .level-breakdown {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .level-item {
            display: flex;
            justify-content: space-between;
        }

        .charts-container {
            display: flex;
            gap: 1rem;
        }

        .chart-card, .latest-petugas-card {
            background: var(--card-bg);
            border: 1px solid var(--primary-color);
            border-radius: 10px;
            padding: 1.5rem;
            flex: 1;
        }

        .latest-petugas-table {
            width: 100%;
            border-collapse: collapse;
        }

        .latest-petugas-table th, 
        .latest-petugas-table td {
            padding: 0.5rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .profile-thumb {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 0.5rem;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <!-- Navbar (Reuse from data_petugas.php) -->
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
                        <a href="../datasiswa/data_siswa.php">
                            <i class="fas fa-users"></i> Data Siswa
                        </a>
                        <a href="data_petugas.php">
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
        <div class="statistics-container">
            <div class="total-stats">
                <div class="stat-card">
                    <h3>Total Petugas</h3>
                    <div class="stat-value"><?php echo $total_petugas; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Petugas per Level</h3>
                    <div class="level-breakdown">
                        <div class="level-item">
                            <span>Admin:</span> 
                            <strong><?php echo $level_stats['admin'] ?? 0; ?></strong>
                        </div>
                        <div class="level-item">
                            <span>Petugas:</span> 
                            <strong><?php echo $level_stats['petugas'] ?? 0; ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="charts-container">
                <div class="chart-card">
                    <h3>Distribusi Level Petugas</h3>
                    <canvas id="levelChart"></canvas>
                </div>
                
                <div class="latest-petugas-card">
                    <h3>Petugas Terbaru</h3>
                    <table class="latest-petugas-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Level</th>
                                <th>Tanggal Bergabung</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($petugas = mysqli_fetch_assoc($latest_petugas_result)): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo !empty($petugas['foto_profil']) ? $petugas['foto_profil'] : '../uploads/profil/default_profil.jpg'; ?>" 
                                         alt="Foto Profil" 
                                         class="profile-thumb">
                                    <?php echo htmlspecialchars($petugas['nama_petugas']); ?>
                                </td>
                                <td><?php echo ucfirst($petugas['level']); ?></td>
                                <td><?php echo $petugas['tanggal_bergabung']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        
        // Chart.js untuk Distribusi Level Petugas
        var ctx = document.getElementById('levelChart').getContext('2d');
        var levelChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Admin', 'Petugas'],
                datasets: [{
                    data: [
                        <?php echo $level_stats['admin'] ?? 0; ?>, 
                        <?php echo $level_stats['petugas'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        'rgba(0, 255, 136, 0.6)', 
                        'rgba(0, 204, 255, 0.6)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Reuse your existing JavaScript functions for dropdown toggles
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
        
    </script>

    
</body>
</html>