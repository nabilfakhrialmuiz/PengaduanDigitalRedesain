<?php
session_start();
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    header("Location: login_petugas.php");
    exit();
}


include 'koneksi.php';


$id_petugas = $_SESSION['id_petugas'];
$query = "SELECT * FROM petugas WHERE id_petugas = '$id_petugas'" && "SELECT foto_profil FROM petugas WHERE id_petugas = '$id_petugas'";
$result = mysqli_query($koneksi, $query) && mysqli_query($koneksi, $foto);


// Search and filter functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';

// Base query with JOIN
$query = "SELECT p.*, s.nama as nama_siswa, s.telp as telp_siswa 
          FROM pengaduan p 
          LEFT JOIN siswa s ON p.nik = s.nik 
          WHERE 1=1";

// Add search conditions
if (!empty($search)) {
    $search = mysqli_real_escape_string($koneksi, $search);
    $query .= " AND (s.nama LIKE '%$search%' OR p.isi_laporan LIKE '%$search%')";
}

// Add status filter
if (!empty($status_filter)) {
    $status_filter = mysqli_real_escape_string($koneksi, $status_filter);
    $query .= " AND p.status = '$status_filter'";
}

// Add date range filter
if (!empty($date_start) && !empty($date_end)) {
    $date_start = mysqli_real_escape_string($koneksi, $date_start);
    $date_end = mysqli_real_escape_string($koneksi, $date_end);
    $query .= " AND DATE(p.tgl_pengaduan) BETWEEN '$date_start' AND '$date_end'";
}

// Tambahkan setelah bagian filter
$active_filters = [];
if (!empty($search)) $active_filters[] = "Pencarian: $search";
if (!empty($status_filter)) $active_filters[] = "Status: $status_filter";
if (!empty($date_start)) $active_filters[] = "Date Start: $date_start";
if (!empty($date_end)) $active_filters[] = "Date End: $date_end";

// Order by date
$query .= " ORDER BY p.tgl_pengaduan DESC";

$result = mysqli_query($koneksi, $query);

// Function to export data
function exportData($format, $result, $koneksi) {
    $filename = "pengaduan_export_" . date('Y-m-d_H-i-s');
    
    switch($format) {
            
        case 'csv':
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, array('ID', 'Tanggal', 'Nama Siswa', 'No. Telp', 'Isi Laporan', 'Status', 'Tanggapan'));
            
            while($row = mysqli_fetch_assoc($result)) {
                $tanggapan_query = "SELECT tanggapan FROM tanggapan WHERE id_pengaduan = " . $row['id_pengaduan'] . " ORDER BY tgl_tanggapan DESC LIMIT 1";
                $tanggapan_result = mysqli_query($koneksi, $tanggapan_query);
                $tanggapan = mysqli_fetch_assoc($tanggapan_result);
                
                fputcsv($output, array(
                    $row['id_pengaduan'],
                    date('d/m/Y', strtotime($row['tgl_pengaduan'])),
                    $row['nama_siswa'],
                    $row['telp_siswa'],
                    $row['isi_laporan'],
                    ($row['status'] == '0' ? 'Pending' : ($row['status'] == 'proses' ? 'Proses' : 'Selesai')),
                    ($tanggapan ? $tanggapan['tanggapan'] : '-')
                ));
            }
            fclose($output);
            break;
            
        case 'pdf':
            require('fpdf.php');
            
            class PDF extends FPDF {
                function Header() {
                    $this->SetFont('Arial', 'B', 14);
                    $this->Cell(0, 10, 'Laporan Pengaduan', 0, 1, 'C');
                    $this->Ln(10);
                    
                    // Table header
                    $this->SetFont('Arial', 'B', 10);
                    $this->Cell(15, 7, 'ID', 1);
                    $this->Cell(25, 7, 'Tanggal', 1);
                    $this->Cell(35, 7, 'Nama Siswa', 1);
                    $this->Cell(30, 7, 'No. Telp', 1);
                    $this->Cell(50, 7, 'Isi Laporan', 1);
                    $this->Cell(20, 7, 'Status', 1);
                    $this->Ln();
                }
            }
            
            $pdf = new PDF();
            $pdf->AddPage('L', 'A4');
            $pdf->SetFont('Arial', '', 9);
            
            while($row = mysqli_fetch_assoc($result)) {
                $pdf->Cell(15, 6, $row['id_pengaduan'], 1);
                $pdf->Cell(25, 6, date('d/m/Y', strtotime($row['tgl_pengaduan'])), 1);
                $pdf->Cell(35, 6, $row['nama_siswa'], 1);
                $pdf->Cell(30, 6, $row['telp_siswa'], 1);
                $pdf->Cell(50, 6, substr($row['isi_laporan'], 0, 30) . '...', 1);
                $pdf->Cell(20, 6, ($row['status'] == '0' ? 'Pending' : ($row['status'] == 'proses' ? 'Proses' : 'Selesai')), 1);
                $pdf->Ln();
            }
            
            $pdf->Output($filename . '.pdf', 'D');
            break;
    }
    exit();
}

// Handle export requests
if(isset($_GET['export'])) {
    $format = $_GET['export'];
    if(in_array($format, ['excel', 'csv', 'pdf'])) {
        // Rerun query to get fresh result set
        $result = mysqli_query($koneksi, $query);
        exportData($format, $result, $koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengaduan - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
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

        .btn-view {
            background: linear-gradient(45deg, #00ccff, #007bff);
        }

        .btn-edit {
            background: linear-gradient(45deg, #ffc107, #ff9800);
        }

        .btn-delete {
            background: linear-gradient(45deg, #dc3545, #c82333);
        }

        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
        }

        .btn-warning {
            background: linear-gradient(45deg, #ffc107, #e0a800);
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
        <!-- Replace the existing controls-section div with this -->
        <div class="controls-section">
            <div class="search-box">
                <input type="search" id="searchInput" placeholder="Cari nama siswa atau isi laporan..." 
                    value="<?php echo htmlspecialchars($search); ?>">
                
                <div class="date-filters">
                    <input type="date" id="dateStart" 
                        value="<?php echo htmlspecialchars($date_start); ?>" 
                        placeholder="Tanggal Mulai">
                    <input type="date" id="dateEnd" 
                        value="<?php echo htmlspecialchars($date_end); ?>" 
                        placeholder="Tanggal Akhir">
                </div>
                
                <select id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="proses" <?php echo $status_filter === 'proses' ? 'selected' : ''; ?>>Proses</option>
                    <option value="selesai" <?php echo $status_filter === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                </select>
                
                <button class="btn" onclick="applyFilters()">
                    <i class="fas fa-search"></i> Terapkan Filter
                </button>

                <button class="btn btn-reset" onclick="resetFilters()">
                    <i class="fas fa-sync"></i> Reset Filter
                </button>
            </div>
            
            <div class="action-buttons">
                <button class="btn btn-stats" onclick="showPengaduanStats()">
                    <i class="fas fa-chart-bar"></i> Statistik
                </button>
            </div>

            <div class="export-dropdown">
                <button onclick="toggleExportDropdown()" class="export-dropdown-btn">
                    <i class="fas fa-file-export"></i> Ekspor <i class="fas fa-chevron-down"></i>
                </button>
                <div id="exportDropdown" class="export-dropdown-content">
                    <a href="#" onclick="exportData('csv')">
                        <i class="fas fa-file-csv"></i> Ekspor CSV
                    </a>
                    <a href="#" onclick="exportData('pdf')">
                        <i class="fas fa-file-pdf"></i> Ekspor PDF
                    </a>
                </div>
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Nama Siswa</th>
                    <th>No. Telp</th>
                    <th>Isi Laporan</th>
                    <th>Foto</th>
                    <th>Status</th>
                    <th>Tanggapan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row['id_pengaduan']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['tgl_pengaduan'])); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_siswa']); ?></td>
                        <td><?php echo htmlspecialchars($row['telp_siswa']); ?></td>
                        <td><?php echo htmlspecialchars($row['isi_laporan']); ?></td>
                        <td>
                            <?php if($row['foto']) { ?>
                                <img src="uploads/<?php echo $row['foto']; ?>" alt="Bukti" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php } ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $row['status'] == '0' ? 'pending' : ($row['status'] == 'proses' ? 'process' : 'complete'); ?>">
                                <?php echo $row['status'] == '0' ? 'Pending' : ($row['status'] == 'proses' ? 'Proses' : 'Selesai'); ?>
                            </span>
                        </td>
                        <td>
                            <?php if($row['status'] == 'selesai') {
                                // Get latest tanggapan
                                $tanggapan_query = "SELECT tanggapan FROM tanggapan WHERE id_pengaduan = " . $row['id_pengaduan'] . " ORDER BY tgl_tanggapan DESC LIMIT 1";
                                $tanggapan_result = mysqli_query($koneksi, $tanggapan_query);
                                $tanggapan = mysqli_fetch_assoc($tanggapan_result);
                                echo htmlspecialchars($tanggapan['tanggapan'] ?? '-');
                            } else {
                                echo "-";
                            } ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="detail_pengaduan.php?id=<?php echo $row['id_pengaduan']; ?>" class="btn btn-view" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-edit" onclick="openEditModal(
                                    <?php echo $row['id_pengaduan']; ?>, 
                                    '<?php echo htmlspecialchars($row['isi_laporan']); ?>', 
                                    '<?php echo $row['status']; ?>'
                                )" title="Edit Pengaduan">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="hapusPengaduan(<?php echo $row['id_pengaduan']; ?>)" class="btn btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php if($row['status'] == 'selesai'): ?>
                                    <button onclick="hapusTanggapan(<?php echo $row['id_pengaduan']; ?>)" class="btn btn-warning">
                                        <i class="fas fa-comment-slash"></i> 
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>

    <!-- Modal Edit Pengaduan -->
    <div id="editModal" class="modal" style="display:none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color: var(--dark-bg); color: var(--text-color); margin: 10% auto; padding: 20px; border: 1px solid var(--primary-color); width: 80%; max-width: 500px; border-radius: 10px;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--primary-color); padding-bottom: 10px;">
                <h2>Edit Pengaduan</h2>
                <span onclick="closeEditModal()" style="color: var(--text-color); font-size: 28px; cursor: pointer;">&times;</span>
            </div>
            <form id="editPengaduanForm" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="id_pengaduan" id="editIdPengaduan">
                
                <div style="margin-bottom: 15px;">
                    <label for="editIsiLaporan" style="display: block; margin-bottom: 5px;">Isi Laporan</label>
                    <textarea name="isi_laporan" id="editIsiLaporan" rows="4" style="width: 100%; background: var(--card-bg); border: 1px solid var(--primary-color); color: var(--text-color); padding: 10px;"></textarea>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="editStatus" style="display: block; margin-bottom: 5px;">Status</label>
                    <select name="status" id="editStatus" style="width: 100%; background: var(--card-bg); border: 1px solid var(--primary-color); color: var(--text-color); padding: 10px;">
                        <option value="0">Pending</option>
                        <option value="proses">Proses</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeEditModal()" class="btn" style="background: var(--secondary-color);">Batal</button>
                    <button type="submit" class="btn" style="background: var(--primary-color);">Simpan</button>
                </div>
            </form>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            
            if (!dataBtn.contains(event.target) && !dataDropdown.contains(event.target)) {
                dataDropdown.classList.remove('active');
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

        // Add these functions to your existing JavaScript
        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            const dateStart = document.getElementById('dateStart').value;
            const dateEnd = document.getElementById('dateEnd').value;
            
            window.location.href = `data_pengaduan.php?search=${encodeURIComponent(search)}` +
                                `&status=${encodeURIComponent(status)}` +
                                `&date_start=${encodeURIComponent(dateStart)}` +
                                `&date_end=${encodeURIComponent(dateEnd)}`;
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

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('dateStart').value = '';
            document.getElementById('dateEnd').value = '';
            window.location.href = 'data_pengaduan.php';
        }

        function openEditModal(id, isiLaporan, status) {
            document.getElementById('editIdPengaduan').value = id;
            document.getElementById('editIsiLaporan').value = isiLaporan;
            document.getElementById('editStatus').value = status;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Add event listener for form submission
        document.getElementById('editPengaduanForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Ambil data dari form
            const formData = new FormData(this);
            
            // Kirim data dengan AJAX
            fetch('update_pengaduan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan sistem!',
                    text: 'Terjadi kesalahan saat memproses permintaan.',
                    confirmButtonText: 'OK'
                });
            });
        });

        function hapusPengaduan(id_pengaduan) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus pengaduan ini? Semua data terkait akan ikut terhapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('hapus_pengaduan.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id_pengaduan=${id_pengaduan}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan sistem!',
                            text: 'Terjadi kesalahan saat memproses permintaan.',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        }

        function hapusTanggapan(id_pengaduan) {
            Swal.fire({
                title: 'Konfirmasi Hapus Tanggapan',
                text: 'Apakah Anda yakin ingin menghapus tanggapan untuk pengaduan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('hapus_tanggapan.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id_pengaduan=${id_pengaduan}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan sistem!',
                            text: 'Terjadi kesalahan saat memproses permintaan.',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        }

        function showPengaduanStats() {
            window.location.href = 'pengaduan_stats.php';
        }

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