<?php
session_start();
include '../koneksi.php';

// Check authentication
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get NIK from GET parameter
$nik = isset($_GET['nik']) ? mysqli_real_escape_string($koneksi, $_GET['nik']) : '';

if (empty($nik)) {
    http_response_code(400);
    echo json_encode(['error' => 'NIK tidak valid']);
    exit();
}

// Fetch student details
$query = "SELECT * FROM siswa WHERE nik = '$nik'";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil data']);
    exit();
}

$siswa = mysqli_fetch_assoc($result);

if (!$siswa) {
    http_response_code(404);
    echo json_encode(['error' => 'Siswa tidak ditemukan']);
    exit();
}

// Return student details as JSON
echo json_encode($siswa);