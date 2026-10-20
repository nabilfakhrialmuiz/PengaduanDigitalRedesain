<?php
session_start();
if(!isset($_SESSION['id_petugas']) || $_SESSION['level'] != 'admin') {
    header("Location: ../login/login_petugas.php");
    exit();
}

include '../koneksi.php';
require('../fpdf.php');  // Ensure FPDF is included

// Export to CSV
if(isset($_GET['export']) && $_GET['export'] == 'csv') {
    // Prepare CSV export
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=data_petugas.csv');
    
    // Create a file pointer 
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    fputcsv($output, array('ID', 'Nama Petugas', 'Username', 'Email', 'Telepon', 'Level'));
    
    // Base query
    $query = "SELECT id_petugas, nama_petugas, username, email, telp, level FROM petugas WHERE 1=1";
    
    // Reapply search and filter conditions from main page
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $level_filter = isset($_GET['level']) ? $_GET['level'] : '';
    
    if (!empty($search)) {
        $search = mysqli_real_escape_string($koneksi, $search);
        $query .= " AND (nama_petugas LIKE '%$search%' OR username LIKE '%$search%' OR email LIKE '%$search%' OR telp LIKE '%$search%')";
    }
    
    if (!empty($level_filter)) {
        $level_filter = mysqli_real_escape_string($koneksi, $level_filter);
        $query .= " AND level = '$level_filter'";
    }
    
    $query .= " ORDER BY id_petugas DESC";
    
    $result = mysqli_query($koneksi, $query);
    
    // Output each row
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, array(
            $row['id_petugas'], 
            $row['nama_petugas'], 
            $row['username'], 
            $row['email'], 
            $row['telp'], 
            $row['level']
        ));
    }
    
    fclose($output);
    exit();
}

// Export to PDF
if(isset($_GET['export']) && $_GET['export'] == 'pdf') {
    class PDF extends FPDF {
        // Page header
        function Header() {
            $this->SetFont('Arial', 'B', 15);
            $this->Cell(0, 10, 'Data Petugas - PENGADUAN DIGITAL SMK NEGERI 6', 0, 1, 'C');
            $this->Ln(10);
        }
        
        // Page footer
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }
    }
    
    // Instantiation of inherited class
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    
    // Base query
    $query = "SELECT id_petugas, nama_petugas, username, email, telp, level FROM petugas WHERE 1=1";
    
    // Reapply search and filter conditions from main page
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $level_filter = isset($_GET['level']) ? $_GET['level'] : '';
    
    if (!empty($search)) {
        $search = mysqli_real_escape_string($koneksi, $search);
        $query .= " AND (nama_petugas LIKE '%$search%' OR username LIKE '%$search%' OR email LIKE '%$search%' OR telp LIKE '%$search%')";
    }
    
    if (!empty($level_filter)) {
        $level_filter = mysqli_real_escape_string($koneksi, $level_filter);
        $query .= " AND level = '$level_filter'";
    }

    
    $query .= " ORDER BY id_petugas DESC";
    
    $result = mysqli_query($koneksi, $query);
    
    // Table header
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(15, 7, 'ID', 1);
    $pdf->Cell(40, 7, 'Nama Petugas', 1);
    $pdf->Cell(30, 7, 'Username', 1);
    $pdf->Cell(50, 7, 'Email', 1);
    $pdf->Cell(25, 7, 'Telepon', 1);
    $pdf->Cell(30, 7, 'Level', 1);
    $pdf->Ln();
    
    // Table rows
    $pdf->SetFont('Arial', '', 9);
    while ($row = mysqli_fetch_assoc($result)) {
        $pdf->Cell(15, 7, $row['id_petugas'], 1);
        $pdf->Cell(40, 7, $row['nama_petugas'], 1);
        $pdf->Cell(30, 7, $row['username'], 1);
        $pdf->Cell(50, 7, $row['email'], 1);
        $pdf->Cell(25, 7, $row['telp'], 1);
        $pdf->Cell(30, 7, $row['level'], 1);
        $pdf->Ln();
    }
    
    // Output PDF
    $pdf->Output('data_petugas.pdf', 'D');
    exit();
}

// Proses Update Petugas
if(isset($_POST['update_petugas'])) {
    $id_petugas = mysqli_real_escape_string($koneksi, $_POST['id_petugas']);
    $nama_petugas = mysqli_real_escape_string($koneksi, $_POST['nama_petugas']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $telp = mysqli_real_escape_string($koneksi, $_POST['telp']);
    $level = mysqli_real_escape_string($koneksi, $_POST['level']);

    // Cek apakah file foto diunggah
    $foto_profil = $row['foto_profil']; // Gunakan foto lama sebagai default
    if(isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        $target_dir = "../uploads/profil/";
        $file_extension = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
        $new_filename = 'profil_' . $id_petugas . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        // Hapus foto lama jika bukan default
        if(!empty($row['foto_profil']) && $row['foto_profil'] != '../uploads/profil/default_profil.jpg') {
            unlink($row['foto_profil']);
        }

        // Pindahkan file yang diunggah
        if(move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file)) {
            $foto_profil = $target_file;
        }
    }

    // Query update
    $update_query = "UPDATE petugas SET 
        nama_petugas = '$nama_petugas', 
        username = '$username', 
        email = '$email', 
        telp = '$telp', 
        level = '$level', 
        foto_profil = '$foto_profil'
    WHERE id_petugas = '$id_petugas'";

    if(mysqli_query($koneksi, $update_query)) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data petugas telah diupdate.',
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                confirmButtonColor: 'var(--primary-color)'
            }).then(() => {
                window.location.href = 'data_petugas.php';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Tidak dapat mengupdate data petugas.',
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                confirmButtonColor: 'var(--primary-color)'
            });
        </script>";
    }
}

// Delete Petugas Process
if(isset($_GET['hapus']) && $_GET['hapus'] == 'true' && isset($_GET['id'])) {
    $id_petugas = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Query to get profile photo before deletion
    $cek_foto = mysqli_query($koneksi, "SELECT foto_profil FROM petugas WHERE id_petugas = '$id_petugas'");
    $data_foto = mysqli_fetch_assoc($cek_foto);
    
    // Delete photo file if not default
    if(!empty($data_foto['foto_profil']) && $data_foto['foto_profil'] != 'uploads/profil/default_profil.jpg') {
        unlink($data_foto['foto_profil']);
    }
    
    // Delete user
    $delete_query = "DELETE FROM petugas WHERE id_petugas = '$id_petugas'";
    if(mysqli_query($koneksi, $delete_query)) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data petugas telah dihapus.',
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                confirmButtonColor: 'var(--primary-color)'
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Tidak dapat menghapus data petugas.',
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                confirmButtonColor: 'var(--primary-color)'
            });
        </script>";
    }
}
// Search and filter functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$level_filter = isset($_GET['level']) ? $_GET['level'] : '';


    // Tambahkan setelah bagian filter
    $active_filters = [];
    if (!empty($search)) $active_filters[] = "Pencarian: $search";
    if (!empty($level_filter)) $active_filters[] = "Level: $level_filter";
    
// Base query with all required fields except password
$query = "SELECT id_petugas, nama_petugas, username, email, telp, level, foto_profil FROM petugas WHERE 1=1";

// Add search condition
if (!empty($search)) {
    $search = mysqli_real_escape_string($koneksi, $search);
    $query .= " AND (nama_petugas LIKE '%$search%' OR username LIKE '%$search%' OR email LIKE '%$search%' OR telp LIKE '%$search%')";
}

// Add level filter
if (!empty($level_filter)) {
    $level_filter = mysqli_real_escape_string($koneksi, $level_filter);
    $query .= " AND level = '$level_filter'";
}

// Order by id
$query .= " ORDER BY id_petugas DESC";

$result = mysqli_query($koneksi, $query);

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
    <title>Data Petugas - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../utama/light-theme.css">
    <script src="../utama/theme-toggle.js"></script>
    <!-- Reuse your existing CSS -->
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
            white-space: nowrap;
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
            justify-content: center;
        }
        
        .action-btn {
            background: transparent;
            border: none;
            color: var(--text-color);
            padding: 0.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            transform: scale(1.1);
        }
        
        .view-btn {
            color: var(--secondary-color);
        }
        
        .edit-btn {
            color: var(--primary-color);
        }
        
        .delete-btn {
            color: #dc3545;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group input, 
        .form-group select {
            background: var(--card-bg);
            border: 1px solid var(--primary-color);
            color: var(--text-color);
            padding: 0.5rem 1rem;
            border-radius: 5px;
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

        /* Styling khusus untuk modal edit */
        .swal2-popup {
            background: var(--card-bg) !important;
            border: 1px solid var(--primary-color) !important;
        }

        .swal2-title {
            color: var(--text-color) !important;
            font-family: 'Orbitron', sans-serif !important;
        }

        .swal2-content {
            color: var(--text-color) !important;
        }

        .swal2-input, .swal2-select {
            background: rgba(10, 10, 10, 0.5) !important;
            border: 1px solid var(--primary-color) !important;
            color: var(--text-color) !important;
        }

        .swal2-confirm {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)) !important;
            color: var(--bg-color) !important;
            border: none !important;
        }

        .swal2-cancel {
            background: linear-gradient(45deg, #dc3545, #c82333) !important;
            color: var(--text-color) !important;
            border: none !important;
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

            .export-buttons {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <!-- Reuse your existing navbar structure -->
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
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="controls-section">
            <div class="search-box">
                <input type="search" id="searchInput" placeholder="Cari petugas (Nama, Username, Email, Telepon)..." value="<?php echo htmlspecialchars($search); ?>">
                <select id="levelFilter">
                    <option value="">Semua Level</option>
                    <option value="admin" <?php echo $level_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="petugas" <?php echo $level_filter === 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                </select>
            
                <button class="btn" onclick="applyFilters()">
                <i class="fas fa-search"></i> Terapkan Filter
            </button>

                 <!-- Tambahkan tombol Reset -->
                 <button class="btn btn-reset" onclick="resetFilters()">
                    <i class="fas fa-sync"></i> Reset Filter
                </button>
            </div>

            <button class="btn" onclick="window.location.href='../login/signup_petugas.php?asal=data_petugas'">
                <i class="fas fa-plus"></i> Tambah Petugas
            </button>

            <button class="btn" onclick="window.location.href='statistik_petugas.php'">
                <i class="fas fa-chart-bar"></i> Statistik 
            </button>

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
                    <th>Foto</th>
                    <th>Nama Petugas</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Level</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['id_petugas']; ?></td>
                    <td>
                        <?php 
                            // Dalam loop tampilan foto
                            $foto_path = !empty($row['foto_profil']) ? $row['foto_profil'] : '../uploads/profil/default_profil.jpg';

                            // Tambahkan debugging
                            if (!file_exists($foto_path)) {
                                echo "File not found: " . $foto_path . "<br>";
                                // Cetak informasi tambahan untuk debugging
                                echo "Database path: " . $row['foto_profil'] . "<br>";
                            }

                            // Gunakan path default jika file tidak ditemukan
                            $foto_path = file_exists($foto_path) ? $foto_path : '../uploads/profil/default_profil.jpg';
                            ?>
                            <img src="<?php echo $foto_path; ?>" 
                                alt="Foto Profil" 
                                style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                    </td>
                    <td><?php echo htmlspecialchars($row['nama_petugas']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['telp']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $row['level']; ?>">
                            <?php echo ucfirst($row['level']); ?>
                        </span>
                    </td>

                    <td>
                        <div class="action-buttons">
                            <button onclick="viewPetugas(<?php echo $row['id_petugas']; ?>)" class="action-btn view-btn" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editPetugas(<?php echo $row['id_petugas']; ?>)" class="action-btn edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deletePetugas(<?php echo $row['id_petugas']; ?>)" class="action-btn delete-btn" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>

    <script>
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

        
        
        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const level = document.getElementById('levelFilter').value;
            window.location.href = `data_petugas.php?search=${encodeURIComponent(search)}&level=${encodeURIComponent(level)}`;
        }
        // Add search on enter key press
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });

        function viewPetugas(id) {
            fetch(`get_petugas_detail.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: 'Detail Petugas',
                        html: `
                            <div style="text-align: center;">
                                <img src="${data.foto_profil}" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 15px;">
                                <h3 style="color: var(--primary-color);">${data.nama_petugas}</h3>
                                <p><strong>Username:</strong> ${data.username}</p>
                                <p><strong>Email:</strong> ${data.email}</p>
                                <p><strong>Telepon:</strong> ${data.telp}</p>
                                <p><strong>Level:</strong> ${data.level}</p>
                            </div>
                        `,
                        background: 'var(--bg-color)',
                        color: 'var(--text-color)',
                        confirmButtonColor: 'var(--primary-color)'
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Tidak dapat mengambil detail petugas',
                        background: 'var(--bg-color)',
                        color: 'var(--text-color)',
                        confirmButtonColor: 'var(--primary-color)'
                    });
                });
        }


        function editPetugas(id_petugas) {
            // Ambil data petugas menggunakan AJAX
            fetch(`get_petugas_detail.php?id=${id_petugas}`)
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: 'Edit Petugas',
                        html: `
                            <form id="editPetugasForm" enctype="multipart/form-data">
                                <input type="hidden" name="id_petugas" value="${data.id_petugas}">
                                <div class="form-group">
                                    <label for="nama_petugas">Nama Petugas</label>
                                    <input type="text" id="nama_petugas" name="nama_petugas" value="${data.nama_petugas}" required class="swal2-input">
                                </div>
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" value="${data.username}" required class="swal2-input">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="${data.email}" required class="swal2-input">
                                </div>
                                <div class="form-group">
                                    <label for="telp">Telepon</label>
                                    <input type="text" id="telp" name="telp" value="${data.telp}" required class="swal2-input">
                                </div>
                                <div class="form-group">
                                    <label for="level">Level</label>
                                    <select id="level" name="level" class="swal2-select">
                                        <option value="admin" ${data.level === 'admin' ? 'selected' : ''}>Admin</option>
                                        <option value="petugas" ${data.level === 'petugas' ? 'selected' : ''}>Petugas</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="foto_profil">Foto Profil</label>
                                    <input type="file" id="foto_profil" name="foto_profil" accept="image/*" class="swal2-input">
                                </div>
                            </form>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Update',
                        cancelButtonText: 'Batal',
                        preConfirm: () => {
                            const form = document.getElementById('editPetugasForm');
                            const formData = new FormData(form);
                            
                            return fetch('data_petugas.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Gagal mengupdate data');
                                }
                                return response.text();
                            })
                            .catch(error => {
                                Swal.showValidationMessage(`Request failed: ${error}`);
                            });
                        }
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan',
                        text: 'Tidak dapat mengambil data petugas',
                        background: 'var(--bg-color)',
                        color: 'var(--text-color)',
                        confirmButtonColor: 'var(--primary-color)'
                    });
                });
        }

        function deletePetugas(id) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus petugas ini?',
                icon: 'warning',
                showCancelButton: true,
                background: 'var(--bg-color)',
                color: 'var(--text-color)',
                confirmButtonColor: 'var(--primary-color)',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `data_petugas.php?hapus=true&id=${id}`;
                }
            });
        }

        function exportData(type) {
            const search = document.getElementById('searchInput').value;
            const level = document.getElementById('levelFilter').value;
            window.location.href = `data_petugas.php?export=${type}&search=${encodeURIComponent(search)}&level=${encodeURIComponent(level)}`;
        }

        function resetFilters() {
            window.location.href = 'data_petugas.php';
        }

    </script>

</body>

</html>