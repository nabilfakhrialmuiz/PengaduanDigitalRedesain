<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

// Cek apakah user memiliki akses
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    echo json_encode([
        'success' => false, 
        'message' => 'Anda tidak memiliki izin untuk melakukan edit.'
    ]);
    exit();
}

// Validasi input
$id_pengaduan = isset($_POST['id_pengaduan']) ? intval($_POST['id_pengaduan']) : 0;
$isi_laporan = isset($_POST['isi_laporan']) ? trim($_POST['isi_laporan']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

// Validasi data
if($id_pengaduan <= 0 || empty($isi_laporan) || empty($status)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Data tidak valid. Mohon isi semua field.'
    ]);
    exit();
}

// Query update
$query = "UPDATE pengaduan SET isi_laporan = ?, status = ? WHERE id_pengaduan = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "ssi", $isi_laporan, $status, $id_pengaduan);

if(mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Berhasil mengupdate data pengaduan.'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Gagal mengupdate data. Silakan coba lagi.'
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($koneksi);