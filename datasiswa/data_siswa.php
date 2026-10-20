<?php
session_start();
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    header("Location: login_petugas.php");
    exit();
}

include '../koneksi.php';

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kelas_filter = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$domain_filter = isset($_GET['domain']) ? $_GET['domain'] : '';

// Base query with search condition
$query = "SELECT * FROM siswa WHERE 1=1";

if (!empty($search)) {
    $search = mysqli_real_escape_string($koneksi, $search);
    $query .= " AND (nik LIKE '%$search%' OR 
                     nama LIKE '%$search%' OR 
                     username LIKE '%$search%' OR 
                     email LIKE '%$search%')";
}

// Filter Kelas
if (!empty($kelas_filter)) {
    $kelas_filter = mysqli_real_escape_string($koneksi, $kelas_filter);
    $query .= " AND kelas = '$kelas_filter'";
}

// Filter Domain Email
if (!empty($domain_filter)) {
    $domain_filter = mysqli_real_escape_string($koneksi, $domain_filter);
    $query .= " AND email LIKE '%@$domain_filter'";
}

// Tambahkan setelah bagian filter
$active_filters = [];
if (!empty($search)) $active_filters[] = "Pencarian: $search";
if (!empty($kelas_filter)) $active_filters[] = "Kelas: $kelas_filter";
if (!empty($domain_filter)) $active_filters[] = "Domain: $domain_filter";

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
    <title>Data Siswa - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../utama/light-theme.css">
    <script src="../utama/theme-toggle.js"></script>
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

        /* Custom SweetAlert Styling to Match Cyberpunk Theme */
        .swal2-popup {
            background: var(--dark-bg) !important;
            border: 2px solid var(--primary-color) !important;
        }

        .swal2-title {
            color: var(--primary-color) !important;
            font-family: 'Orbitron', sans-serif !important;
        }

        .swal2-popup .swal-input-container {
            display: flex;
            flex-direction: column;
            margin-bottom: 1rem;
            text-align: left
        }

        .swal2-popup .swal-input-container label {
            margin-bottom: 0.2rem;
            color: var(--secondary-color);
            font-family: 'Orbitron', sans-serif;
        }

        .swal2-input {
            background: var(--card-bg) !important;
            border: 1px solid var(--primary-color) !important;
            color: var(--text-color) !important;
            font-family: 'Orbitron', sans-serif !important;
            transition: all 0.3s ease !important;
            margin-left: 2px;
            width: 450px;
        }

        .swal2-input:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.2) !important;
        }

        .swal2-confirm {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)) !important;
            border: none !important;
            color: var(--bg-color) !important;
            font-family: 'Orbitron', sans-serif !important;
            transition: all 0.3s ease !important;
        }

        .swal2-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.2);
        }

        .swal2-cancel {
            background: linear-gradient(45deg, #ff00ff, #dc3545) !important;
            border: none !important;
            color: var(--text-color) !important;
            font-family: 'Orbitron', sans-serif !important;
            transition: all 0.3s ease !important;
        }

        .swal2-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 0, 255, 0.2);
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
    
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <!-- Navbar structure remains the same -->
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
                <input type="search" id="searchInput" placeholder="Cari siswa..." value="<?php echo htmlspecialchars($search); ?>">

                 <!-- Dropdown Filter Kelas -->
                <select id="kelasFilter" name="kelas">
                    <option value="">Semua Kelas</option>
                    <?php
                    // Ambil daftar kelas unik dari database
                    $kelas_query = "SELECT DISTINCT kelas FROM siswa ORDER BY kelas";
                    $kelas_result = mysqli_query($koneksi, $kelas_query);
                    while($kelas_row = mysqli_fetch_assoc($kelas_result)) {
                        $selected = ($kelas_row['kelas'] == $kelas_filter) ? 'selected' : '';
                        echo "<option value='{$kelas_row['kelas']}' $selected>{$kelas_row['kelas']}</option>";
                    }
                    ?>
                </select>

                <!-- Dropdown Filter Domain Email -->
                <select id="domainFilter" name="domain"  >
                    <option value="">Semua Domain</option>
                    <?php
                    // Ambil daftar domain unik dari database
                    $domain_query = "SELECT DISTINCT SUBSTRING_INDEX(email, '@', -1) as domain FROM siswa ORDER BY domain";
                    $domain_result = mysqli_query($koneksi, $domain_query);
                    while($domain_row = mysqli_fetch_assoc($domain_result)) {
                        $selected = ($domain_row['domain'] == $domain_filter) ? 'selected' : '';
                        echo "<option value='{$domain_row['domain']}' $selected>{$domain_row['domain']}</option>";
                    }
                    ?>
                </select>

                <button class="btn" onclick="applyFilters()">
                    <i class="fas fa-search"></i> Terapkan Filter
                </button>

                 <!-- Tambahkan tombol Reset -->
                <button class="btn btn-reset" onclick="resetFilters()">
                    <i class="fas fa-sync"></i> Reset Filter
                </button>

                
            </div>


            <button class="btn" onclick="window.location.href='../login/signup_siswa.php?asal=data_siswa'">
                <i class="fas fa-plus"></i> Tambah Siswa
            </button>

            <button class="btn" onclick="window.location.href='statistik_siswa.php'">
                <i class="fas fa-chart-bar"></i> Statistik 
            </button>

            <div class="export-dropdown">
                <button onclick="toggleExportDropdown()" class="export-dropdown-btn">
                    <i class="fas fa-file-export"></i> Ekspor <i class="fas fa-chevron-down"></i>
                </button>
                <div id="exportDropdown" class="export-dropdown-content">
                    <a href="#" onclick="exportToExcel()">
                        <i class="fas fa-file-csv"></i> Ekspor CSV
                    </a>
                    <a href="#" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> Ekspor PDF
                    </a>
                </div>
            </div>
            
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Telp</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nik']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                    <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['telp']); ?></td>
                    
                   
                    <td>
                        <div class="action-buttons">
                            <button onclick="showSiswaDetail('<?php echo $row['nik']; ?>')" class="btn btn-view">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if($_SESSION['level'] == 'admin') { ?>
                                <button class="btn btn-edit" onclick="editSiswa('<?php echo $row['nik']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            <button class="btn btn-delete" onclick="deleteSiswa('<?php echo $row['nik']; ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>

    <div class="modal" id="siswaDetailModal" tabindex="-1" style="display:none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Siswa</h5>
                    <button type="button" class="close" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-body" id="siswaDetailContent">
                    <!-- Siswa details will be dynamically inserted here -->
                </div>
            </div>
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
            const kelas = document.getElementById('kelasFilter').value;
            const domain = document.getElementById('domainFilter').value;

            // Konstruksi URL dengan parameter filter
            let url = 'data_siswa.php';
            let params = [];

            if (search) params.push(`search=${encodeURIComponent(search)}`);
            if (kelas) params.push(`kelas=${encodeURIComponent(kelas)}`);
            if (domain) params.push(`domain=${encodeURIComponent(domain)}`);

            // Tambahkan parameter jika ada
            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            window.location.href = url;
        }

        function resetFilters() {
            // Reset input dan dropdown ke kondisi awal
            document.getElementById('searchInput').value = '';
            document.getElementById('kelasFilter').selectedIndex = 0;
            document.getElementById('domainFilter').selectedIndex = 0;

            // Redirect ke halaman tanpa parameter filter
            window.location.href = 'data_siswa.php';
        }

        function exportToExcel() {
            // Get current filter parameters
            const search = document.getElementById('searchInput').value;
            const kelas = document.getElementById('kelasFilter').value;
            const domain = document.getElementById('domainFilter').value;

            // Construct URL with parameters
            let url = 'export_siswa_csv.php';
            let params = [];

            if (search) params.push(`search=${encodeURIComponent(search)}`);
            if (kelas) params.push(`kelas=${encodeURIComponent(kelas)}`);
            if (domain) params.push(`domain=${encodeURIComponent(domain)}`);

            // Add parameters if any
            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            // Redirect to export script
            window.location.href = url;
        }

        function exportToPDF() {
            window.location.href = 'export_siswa_pdf.php';
        }

        function showSiswaDetail(nik) {
        // Fetch student details via AJAX
        fetch(`get_siswa_detail.php?nik=${nik}`)
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    title: 'Detail Siswa',
                    html: `
                        <div class="student-detail" style="text-align: left; font-family: 'Orbitron', sans-serif;">
                            <div class="detail-item">
                                <strong style="color: var(--primary-color);">NIK:</strong> 
                                <span style="color: var(--text-color);">${data.nik}</span>
                            </div>
                            <div class="detail-item">
                                <strong style="color: var(--primary-color);">Nama:</strong> 
                                <span style="color: var(--text-color);">${data.nama}</span>
                            </div>
                            <div class="detail-item">
                                <strong style="color: var(--primary-color);">Username:</strong> 
                                <span style="color: var(--text-color);">${data.username}</span>
                            </div>
                            <div class="detail-item">
                                <strong style="color: var(--primary-color);">Email:</strong> 
                                <span style="color: var(--text-color);">${data.email}</span>
                            </div>
                            <div class="detail-item">
                                <strong style="color: var(--primary-color);">Telepon:</strong> 
                                <span style="color: var(--text-color);">${data.telp}</span>
                            </div>
                            <div class="detail-item">
                                <strong style="color: var(--primary-color);">Kelas:</strong> 
                                <span style="color: var(--text-color);">${data.kelas}</span>
                            </div>
                        </div>
                    `,
                    icon: 'info',
                    background: 'var(--dark-bg)',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: 'var(--primary-color)',
                    customClass: {
                        popup: 'cyberpunk-modal',
                        title: 'cyberpunk-title',
                        content: 'cyberpunk-content'
                    }
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal mengambil detail siswa',
                    background: 'var(--dark-bg)',
                    confirmButtonColor: 'var(--primary-color)'
                });
            });
        }

    function closeModal() {
        const modal = document.getElementById('siswaDetailModal');
        modal.style.display = 'none';
    }

    function editSiswa(nik) {
        // Fetch student details via AJAX
        fetch(`get_siswa_detail.php?nik=${nik}`)
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    title: 'Edit Data Siswa',
                    html: `
                        <form id="editSiswaForm">
                            <input type="hidden" name="nik" value="${data.nik}">
                            <div class="swal-input-container">
                                <label>NIK</label>
                                <input type="text" name="nik" value="${data.nik}" class="swal2-input" readonly>
                            </div>
                            <div class="swal-input-container">
                                <label>Nama</label>
                                <input type="text" name="nama" value="${data.nama}" class="swal2-input" required>
                            </div>
                            <div class="swal-input-container">
                                <label>Username</label>
                                <input type="text" name="username" value="${data.username}" class="swal2-input" required>
                            </div>
                            <div class="swal-input-container">
                                <label>Email</label>
                                <input type="email" name="email" value="${data.email}" class="swal2-input" required>
                            </div>
                            <div class="swal-input-container">
                                <label>Telepon</label>
                                <input type="text" name="telp" value="${data.telp}" class="swal2-input" required>
                            </div>
                            <div class="swal-input-container">
                                <label>Kelas</label>
                                <input type="text" name="kelas" value="${data.kelas}" class="swal2-input" required>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        const form = document.getElementById('editSiswaForm');
                        if (form.checkValidity()) {
                            const formData = new FormData(form);
                            return fetch('update_siswa.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    return result;
                                } else {
                                    Swal.showValidationMessage(result.message);
                                }
                            });
                        } else {
                            Swal.showValidationMessage('Mohon isi semua field');
                        }
                    }
                }).then((result) => {
                    if (result.value && result.value.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data siswa berhasil diupdate'
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal mengambil detail siswa'
                });
            });
    }

    function deleteSiswa(nik) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data siswa akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('delete_siswa.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `nik=${nik}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: `Data siswa ${data.deleted_student_name} telah dihapus`
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat menghapus data'
                    });
                });
            }
        });
    }
        // Add event listeners
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('searchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyFilters();
                }
            });
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