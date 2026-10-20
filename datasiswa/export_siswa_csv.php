<?php
session_start();
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    header("Location: login_petugas.php");
    exit();
}

include '../koneksi.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kelas_filter = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$domain_filter = isset($_GET['domain']) ? $_GET['domain'] : '';

// Construct base query
$query = "SELECT * FROM siswa WHERE 1=1";

if (!empty($search)) {
    $search = mysqli_real_escape_string($koneksi, $search);
    $query .= " AND (nik LIKE '%$search%' OR 
                     nama LIKE '%$search%' OR 
                     username LIKE '%$search%' OR 
                     email LIKE '%$search%')";
}

// Kelas Filter
if (!empty($kelas_filter)) {
    $kelas_filter = mysqli_real_escape_string($koneksi, $kelas_filter);
    $query .= " AND kelas = '$kelas_filter'";
}

// Domain Filter
if (!empty($domain_filter)) {
    $domain_filter = mysqli_real_escape_string($koneksi, $domain_filter);
    $query .= " AND email LIKE '%@$domain_filter'";
}

$result = mysqli_query($koneksi, $query);

// Prepare CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=data_siswa.csv');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Add empty rows and header matching the sample
fputcsv($output, ['']);
fputcsv($output, ['SMKN 6 Kota Bekasi']);
fputcsv($output, ['']);
fputcsv($output, ['Data Siswa - Pengaduan Digital - SMKN 6 Kota Bekasi']);
fputcsv($output, ['']);

// Write headers
fputcsv($output, ['NIK', 'Nama', 'Username', 'Email', 'Telepon', 'Kelas']);

// Fetch and write data
while ($row = mysqli_fetch_assoc($result)) {
    // Format NIK to prevent scientific notation
    $nik = $row['nik'];
    
    // Ensure consistent formatting
    $formatted_row = [
        (string)$nik,  // Explicitly convert to string
        $row['nama'],
        $row['username'],
        $row['email'] ?? '',  // Use empty string if email is null
        $row['telp'] ?? '',   // Use empty string if telp is null
        $row['kelas']
    ];
    
    fputcsv($output, $formatted_row);
}

// Close output stream
fclose($output);
exit();