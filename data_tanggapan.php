<?php
session_start();
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    header("Location: login/login_petugas.php");
    exit();
}

include 'koneksi.php';

$id_petugas = $_SESSION['id_petugas'];
$query = "SELECT * FROM petugas WHERE id_petugas = '$id_petugas'";
$result = mysqli_query($koneksi, $query);
$petugas = mysqli_fetch_assoc($result);

// Handle update tanggapan if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_tanggapan'])) {
    $id_tanggapan = mysqli_real_escape_string($koneksi, $_POST['edit_id_tanggapan']);
    $tanggapan = mysqli_real_escape_string($koneksi, $_POST['edit_tanggapan']);
    
    $update_query = "UPDATE tanggapan SET tanggapan = '$tanggapan', tgl_tanggapan = NOW() WHERE id_tanggapan = '$id_tanggapan'";
    
    if (mysqli_query($koneksi, $update_query)) {
        $_SESSION['success_message'] = "Tanggapan berhasil diupdate!";
        header("Location: data_tanggapan.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Gagal mengupdate tanggapan: " . mysqli_error($koneksi);
    }
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';
$petugas_filter = isset($_GET['petugas']) ? $_GET['petugas'] : '';

// Modify base query to include date and petugas filters
$query = "SELECT t.*, p.nama_petugas, pg.isi_laporan 
          FROM tanggapan t
          LEFT JOIN petugas p ON t.id_petugas = p.id_petugas
          LEFT JOIN pengaduan pg ON t.id_pengaduan = pg.id_pengaduan
          WHERE 1=1";

// Apply search filter (existing)
if (!empty($search)) {
    $search = mysqli_real_escape_string($koneksi, $search);
    $query .= " AND (t.tanggapan LIKE '%$search%' OR 
                     p.nama_petugas LIKE '%$search%' OR 
                     pg.isi_laporan LIKE '%$search%')";
}

// Apply date range filter
if (!empty($date_start) && !empty($date_end)) {
    $query .= " AND DATE(t.tgl_tanggapan) BETWEEN '$date_start' AND '$date_end'";
}

// Apply petugas filter
if (!empty($petugas_filter)) {
    $query .= " AND p.id_petugas = '$petugas_filter'";
}

// Tambahkan setelah bagian filter
$active_filters = [];
if (!empty($search)) $active_filters[] = "Pencarian: $search";
if (!empty($date_start)) $active_filters[] = "Date Start: $date_start";
if (!empty($date_end)) $active_filters[] = "Date End: $date_end";
if (!empty($petugas_filter)) $active_filters[] = "Id Petugas: $petugas_filter";

$query .= " ORDER BY t.tgl_tanggapan DESC";

$result = mysqli_query($koneksi, $query);
// Fetch petugas list for dropdown
$petugas_query = "SELECT id_petugas, nama_petugas FROM petugas WHERE level IN ('petugas', 'admin')";
$petugas_result = mysqli_query($koneksi, $petugas_query);
// Check if query was successful
if (!$result) {
    die("Query failed: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tanggapan - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="utama/light-theme.css">
    <script src="utama/theme-toggle.js"></script>
    <style>
        /* Using the same styling as data_siswa.php */
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

        .main-content {
            padding: 9rem 2rem 2rem;
            max-width: 1250px;
            margin: 0 auto;
        }

        .controls-section {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 2rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-box {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            flex: 1;
        }

        input[type="search"], select {
            background: var(--card-bg);
            border: 1px solid var(--primary-color);
            color: var(--text-color);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-family: 'Orbitron', sans-serif;
        }

        .date-filters {
            display: flex;
            gap: 0.5rem;
        }

        .date-filters input[type="date"] {
            background: var(--card-bg);
            border: 1px solid var(--primary-color);
            color: var(--text-color);
            padding: 0.5rem;
            border-radius: 5px;
            font-family: 'Orbitron', sans-serif;
        }

        .btn-reset {
            background: linear-gradient(45deg, #6c757d, #495057);
            color: var(--text-color);
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.2);
        }

        
        .active-filters {
            display: flex;
            align-items: center;
            gap: 1rem;
            
            color: var(--text-color);
            font-family: 'Orbitron', sans-serif;
        }

        .filter-tag {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid var(--primary-color);
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.9rem;
        }

        .export-dropdown {
            position: relative;
            display: inline-block;
        }

        .export-dropdown-content {
            display: none;
            position: absolute;
            background: var(--card-bg);
            width : 9rem;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 5px;
            border: 1px solid var(--primary-color);
        }

        .export-dropdown-content a {
            color: var(--text-color);
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s;
        }

        .export-dropdown-content a:hover {
            background: rgba(0, 255, 136, 0.1);
        }

        .export-dropdown-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 0.5rem 1.5rem;
            color: var(--bg-color);
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .export-dropdown-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.2);
        }

        .btn {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 0.5rem 1.5rem;
            color: var(--bg-color);
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn i {
            font-size: 1.1rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.2);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 10px;
            overflow: hidden;
        }

        .data-table td {
            white-space: wrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 80%; /* Adjust based on your needs */
        }

        .data-table td:nth-child(5), /* Isi Laporan column */
        .data-table td:nth-child(8) { /* Tanggapan column */
            max-width: 300px;
            white-space: normal;
        }

        .data-table th, .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .data-table th {
            background: rgba(0, 255, 136, 0.1);
            font-family: 'Orbitron', sans-serif;
        }

        .data-table tr:hover {
            background: rgba(0, 255, 136, 0.05);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.9rem;
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

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .modal {
    display: none;
    position: fixed;
    z-index: 1100;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.8);
    backdrop-filter: blur(5px);
    animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.modal-content {
    background: linear-gradient(
        135deg, 
        rgba(0, 255, 136, 0.1), 
        rgba(0, 204, 255, 0.1)
    );
    margin: 5% auto;
    padding: 2rem;
    border: 2px solid var(--primary-color);
    width: 90%;
    max-width: 700px;
    border-radius: 15px;
    position: relative;
    box-shadow: 
        0 15px 25px rgba(0, 0, 0, 0.5),
        0 5px 10px rgba(0, 255, 136, 0.2);
    overflow: hidden;
}

.modal-content::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: repeating-linear-gradient(
        transparent,
        transparent 20px,
        rgba(0, 255, 136, 0.05) 20px,
        rgba(0, 255, 136, 0.05) 40px
    );
    animation: gridAnimation 10s linear infinite;
    z-index: -1;
}

@keyframes gridAnimation {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(0, 255, 136, 0.2);
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}

.modal-header h2 {
    font-family: 'Orbitron', sans-serif;
    color: var(--primary-color);
    margin: 0;
    font-size: 1.5rem;
    letter-spacing: 1px;
}

.close-button {
    color: var(--text-color);
    background: linear-gradient(45deg, #ff00ff, #00ccff);
    width: 40px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
}

.close-button:hover {
    transform: rotate(90deg) scale(1.1);
    background: linear-gradient(45deg, #00ccff, #ff00ff);
}

.modal-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.modal-details p {
    background: rgba(10, 10, 10, 0.5);
    border: 1px solid rgba(0, 255, 136, 0.2);
    padding: 1rem;
    border-radius: 10px;
    margin: 0;
    display: flex;
    flex-direction: column;
}

.modal-details p strong {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    font-family: 'Orbitron', sans-serif;
    letter-spacing: 1px;
}

.modal-details p span {
    word-break: break-word;
}

@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 10% auto;
        padding: 1rem;
    }

    .modal-details {
        grid-template-columns: 1fr;
    }
}

        .btn-view {
            background: linear-gradient(45deg, #00ccff, #007bff);
        }

        .btn-edit {
            background: linear-gradient(45deg, #ffc107, #ff9800);
        }

        .btn-delete {
            background: linear-gradient(45deg, #dc3545, #c82333);
        }

        .btn-stats {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        }

        @media (max-width: 768px) {
            .controls-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                flex-direction: column;
                width: 100%;
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
            }

            .date-filters {
                width: 100%;
            }
            
            .export-buttons {
                width: 100%;
                justify-content: space-between;
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

                <?php if (!empty($active_filters)): ?>
                    <div class="active-filters">
                        <strong>Filter Aktif:</strong>
                        <?php foreach ($active_filters as $filter): ?>
                            <span class="filter-tag"><?php echo htmlspecialchars($filter); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

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
        <div class="controls-section">
            <div class="search-box">
                <input type="search" id="searchInput" placeholder="Cari tanggapan..." value="<?php echo htmlspecialchars($search); ?>">
                 <!-- Tambahkan di bagian controls-section -->
                <div class="date-filters">
                    <input type="date" id="dateStart" name="date_start" value="<?php echo htmlspecialchars($date_start); ?>">
                    <input type="date" id="dateEnd" name="date_end" value="<?php echo htmlspecialchars($date_end); ?>">
                    
                    <select id="petugasFilter">
                        <option value="">Semua Petugas</option>
                        <?php while($petugas = mysqli_fetch_assoc($petugas_result)) { ?>
                            <option value="<?php echo $petugas['id_petugas']; ?>" 
                                <?php echo ($petugas_filter == $petugas['id_petugas']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($petugas['nama_petugas']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <button class="btn" onclick="applyFilters()">
                    <i class="fas fa-search"></i> Terapkan Filter
                </button>
                <button class="btn btn-reset" onclick="resetFilters()">
                    <i class="fas fa-sync"></i> Reset Filter
                </button>
            </div>
            

            <div class="action-buttons">
                <button class="btn btn-stats" onclick="showTanggapanStats()">
                    <i class="fas fa-chart-bar"></i> Statistik
                </button>
            </div>
            
            <div class="export-dropdown">
                <button onclick="toggleExportDropdown()" class="export-dropdown-btn">
                    <i class="fas fa-file-export"></i> Ekspor <i class="fas fa-chevron-down"></i>
                </button>
                <div id="exportDropdown" class="export-dropdown-content">
                    <a href="#" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> Ekspor PDF
                    </a>
                    <a href="#" onclick="exportToCSV()">
                        <i class="fas fa-file-csv"></i> Ekspor CSV
                    </a>
                </div>
            </div>

        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID Tanggapan</th>
                    <th>Pengaduan</th>
                    <th>Tanggapan</th>
                    <th>Petugas</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id_tanggapan']); ?></td>
                    <td><?php echo htmlspecialchars(substr($row['isi_laporan'], 0, 100)) . '...'; ?></td>
                    <td><?php echo htmlspecialchars(substr($row['tanggapan'], 0, 100)) . '...'; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_petugas']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['tgl_tanggapan'])); ?></td>

                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-view" onclick="viewTanggapan(
                                '<?php echo $row['id_tanggapan']; ?>',
                                '<?php echo htmlspecialchars($row['isi_laporan']); ?>',
                                '<?php echo htmlspecialchars($row['tanggapan']); ?>',
                                '<?php echo $row['nama_petugas']; ?>',
                                '<?php echo date('d/m/Y H:i', strtotime($row['tgl_tanggapan'])); ?>'
                            )">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-edit" onclick="editTanggapan(
                                '<?php echo $row['id_tanggapan']; ?>',
                                '<?php echo htmlspecialchars($row['isi_laporan']); ?>',
                                '<?php echo htmlspecialchars($row['tanggapan']); ?>',
                                '<?php echo $row['nama_petugas']; ?>',
                                '<?php echo date('d/m/Y H:i', strtotime($row['tgl_tanggapan'])); ?>'
                            )">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-delete" onclick="confirmDeleteTanggapan(
                                '<?php echo $row['id_pengaduan']; ?>', 
                                '<?php echo $row['id_tanggapan']; ?>'
                            )">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>

    <!-- Tambahkan di akhir body, sebelum penutup </body> -->
    <div id="tanggapanModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>View Tanggapan</h2>
                <div class="close-button" onclick="closeEditModal()">&times;</div>
            </div>
            <div class="modal-details">
                <p><strong>ID Tanggapan:</strong> <span id="modalIdTanggapan"></span></p>
                <p><strong>Isi Laporan:</strong> <span id="modalIsiLaporan"></span></p>
                <p><strong>Tanggapan:</strong> <span id="modalTanggapan"></span></p>
                <p><strong>Petugas:</strong> <span id="modalPetugas"></span></p>
                <p><strong>Tanggal:</strong> <span id="modalTanggal"></span></p>
            </div>
        </div>
    </div>


    <!-- Add this modal for editing after the view modal -->
    <div id="editTanggapanModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Tanggapan</h2>
                <div class="close-button" onclick="closeEditModal()">&times;</div>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="editIdTanggapan" name="edit_id_tanggapan">
                <div class="modal-details">
                    <p>
                        <strong>Isi Laporan</strong>
                        <span id="editModalIsiLaporan"></span>
                    </p>
                    <p>
                        <strong>Tanggapan</strong>
                        <textarea id="editModalTanggapan" name="edit_tanggapan" rows="4" 
                            style="width: 100%; background: rgba(10, 10, 10, 0.5); 
                                border: 1px solid rgba(0, 255, 136, 0.2); 
                                color: var(--text-color); 
                                padding: 1rem; 
                                border-radius: 10px;"   
                        ></textarea>
                    </p>
                    <p>
                        <strong>Petugas</strong>
                        <span id="editModalPetugas"></span>
                    </p>
                    <p>
                        <strong>Tanggal</strong>
                        <span id="editModalTanggal"></span>
                    </p>
                </div>
                <div class="modal-footer" style="display: flex; justify-content: flex-end; padding: 1rem;">
                    <button type="submit" name="update_tanggapan" class="btn btn-edit" style="margin-right: 1rem;">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <button type="button" class="btn btn-delete" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

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

        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const dateStart = document.getElementById('dateStart').value;
            const dateEnd = document.getElementById('dateEnd').value;
            const petugasFilter = document.getElementById('petugasFilter').value;
            
            window.location.href = `data_tanggapan.php?search=${encodeURIComponent(search)}` +
                                    `&date_start=${encodeURIComponent(dateStart)}` +
                                    `&date_end=${encodeURIComponent(dateEnd)}` +
                                    `&petugas=${encodeURIComponent(petugasFilter)}`;
        }

        // Tambahkan validasi tanggal
        document.addEventListener('DOMContentLoaded', function() {
            const dateStart = document.getElementById('dateStart');
            const dateEnd = document.getElementById('dateEnd');

            dateStart.addEventListener('change', function() {
                dateEnd.min = this.value;
            });

            dateEnd.addEventListener('change', function() {
                dateStart.max = this.value;
            });
        });

        function resetFilters() {
            // Reset input dan select
            document.getElementById('searchInput').value = '';
            document.getElementById('dateStart').value = '';
            document.getElementById('dateEnd').value = '';
            document.getElementById('petugasFilter').selectedIndex = 0;
            
            // Redirect ke halaman tanpa parameter
            window.location.href = 'data_tanggapan.php';
        }

        function exportData(format) {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            const dateStart = document.getElementById('dateStart').value;
            const dateEnd = document.getElementById('dateEnd').value;
            
            window.location.href = `data_pengaduan.php?export=${format}` +
                                `&search=${encodeURIComponent(search)}` +
                                `&status=${encodeURIComponent(status)}` +
                                `&date_start=${encodeURIComponent(dateStart)}` +
                                `&date_end=${encodeURIComponent(dateEnd)}`;
        }

        // Add event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Add enter key listener for search
            document.getElementById('searchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyFilters();
                }
            });
            
            // Add change listeners for date inputs
            document.getElementById('dateStart').addEventListener('change', function() {
                document.getElementById('dateEnd').min = this.value;
            });
            
            document.getElementById('dateEnd').addEventListener('change', function() {
                document.getElementById('dateStart').max = this.value;
            });
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

        function exportToPDF() {
            const search = document.getElementById('searchInput').value;
            const dateStart = document.getElementById('dateStart').value;
            const dateEnd = document.getElementById('dateEnd').value;
            const petugasFilter = document.getElementById('petugasFilter').value;
            
            window.location.href = `export_tanggapan_pdf.php?search=${encodeURIComponent(search)}` +
                                    `&date_start=${encodeURIComponent(dateStart)}` +
                                    `&date_end=${encodeURIComponent(dateEnd)}` +
                                    `&petugas=${encodeURIComponent(petugasFilter)}`;
        }   

        function exportToCSV() {
            const search = document.getElementById('searchInput').value;
            const dateStart = document.getElementById('dateStart').value;
            const dateEnd = document.getElementById('dateEnd').value;
            const petugasFilter = document.getElementById('petugasFilter').value;
            
            window.location.href = `export_tanggapan_csv.php?search=${encodeURIComponent(search)}` +
                                    `&date_start=${encodeURIComponent(dateStart)}` +
                                    `&date_end=${encodeURIComponent(dateEnd)}` +
                                    `&petugas=${encodeURIComponent(petugasFilter)}`;
        }

        function showTanggapanStats() {
            window.location.href = 'tanggapan_stats.php';
        }

        function viewTanggapan(id, isiLaporan, tanggapan, petugas, tanggal) {
            const modal = document.getElementById('tanggapanModal');
            document.getElementById('modalIdTanggapan').textContent = id;
            document.getElementById('modalIsiLaporan').textContent = isiLaporan;
            document.getElementById('modalTanggapan').textContent = tanggapan;
            document.getElementById('modalPetugas').textContent = petugas;
            document.getElementById('modalTanggal').textContent = tanggal;
            
            modal.style.display = 'block';
        }

        // Tambahkan event listener untuk menutup modal
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('tanggapanModal');
            const closeButton = document.querySelector('.close-button');

            closeButton.onclick = function() {
                modal.style.display = 'none';
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        });

        // Add this function to handle edit modal
        function editTanggapan(id, isiLaporan, tanggapan, petugas, tanggal) {
            const editModal = document.getElementById('editTanggapanModal');
            document.getElementById('editIdTanggapan').value = id;
            document.getElementById('editModalIsiLaporan').textContent = isiLaporan;
            document.getElementById('editModalTanggapan').value = tanggapan;
            document.getElementById('editModalPetugas').textContent = petugas;
            document.getElementById('editModalTanggal').textContent = tanggal;
            
            editModal.style.display = 'block';
        }

        function closeEditModal() {
            const editModal = document.getElementById('editTanggapanModal');
            editModal.style.display = 'none';
        }

        // Update the window click event to handle both modals
        window.onclick = function(event) {
            const modal = document.getElementById('tanggapanModal');
            const editModal = document.getElementById('editTanggapanModal');
            
            if (event.target == modal) {
                modal.style.display = 'none';
            }
            
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
        }

        // Optional: Add success/error message display
        <?php if(isset($_SESSION['success_message'])): ?>
        alert('<?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>');
        <?php endif; ?>

        <?php if(isset($_SESSION['error_message'])): ?>
        alert('<?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>');
        <?php endif; ?>
        

        function confirmDeleteTanggapan(idPengaduan, idTanggapan) {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Konfirmasi Hapus Tanggapan</h2>
                    <div class="close-button" onclick="this.closest('.modal').remove()">&times;</div>
                </div>
                <div class="modal-details">
                    <p>Apakah Anda yakin ingin menghapus tanggapan ini? Status pengaduan akan kembali ke "Proses".</p>
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <button class="btn btn-delete" onclick="deleteTanggapan('${idPengaduan}')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                        <button class="btn" onclick="this.closest('.modal').remove()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    function confirmDeleteTanggapan(idPengaduan, idTanggapan) {
        // Buat modal konfirmasi
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.id = 'deleteConfirmModal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Konfirmasi Hapus Tanggapan</h2>
                    <div class="close-button" onclick="closeDeleteModal()">&times;</div>
                </div>
                <div class="modal-details">
                    <p>Apakah Anda yakin ingin menghapus tanggapan untuk pengaduan dengan ID ${idPengaduan}?</p>
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <button class="btn btn-delete" onclick="deleteTanggapan('${idPengaduan}')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                        <button class="btn" onclick="closeDeleteModal()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Tampilkan modal
        modal.style.display = 'block';
            }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteConfirmModal');
            if (modal) {
                modal.style.display = 'none';
                modal.remove();
            }
        }

        function deleteTanggapan(idPengaduan) {
            // Kirim permintaan hapus ke server
            fetch('hapus_tanggapan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_pengaduan=${idPengaduan}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload(); // Muat ulang halaman
                } else {
                    alert('Gagal menghapus tanggapan: ' + data.message);
                    closeDeleteModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus tanggapan');
                closeDeleteModal();
            });
        }

        // Tambahkan event listener untuk menutup modal jika diklik di luar area modal
        document.addEventListener('click', function(event) {
            const deleteModal = document.getElementById('deleteConfirmModal');
            if (deleteModal && event.target === deleteModal) {
                closeDeleteModal();
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