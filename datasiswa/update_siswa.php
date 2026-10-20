<?php
session_start();
include '../koneksi.php';

header('Content-Type: application/json');

// Cek apakah pengguna memiliki akses
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki izin']);
    exit();
}

// Ambil data dari form
$nik = $_POST['nik'];
$nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$email = mysqli_real_escape_string($koneksi, $_POST['email']);
$telp = mysqli_real_escape_string($koneksi, $_POST['telp']);
$kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);

// Query update
$query = "UPDATE siswa SET 
          nama = '$nama', 
          username = '$username', 
          email = '$email', 
          telp = '$telp', 
          kelas = '$kelas' 
          WHERE nik = '$nik'";

if (mysqli_query($koneksi, $query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Gagal update data: ' . mysqli_error($koneksi)
    ]);
}