<?php
header('Content-Type: application/json');

include '../koneksi.php';

if(!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID tidak ditemukan']);
    exit();
}

$id_petugas = mysqli_real_escape_string($koneksi, $_GET['id']);

$query = "SELECT id_petugas, nama_petugas, username, email, telp, level, 
          COALESCE(foto_profil, '../uploads/profil/default_profil.jpg') as foto_profil 
          FROM petugas 
          WHERE id_petugas = '$id_petugas'";

$result = mysqli_query($koneksi, $query);

if(!$result) {
    echo json_encode(['error' => 'Query gagal: ' . mysqli_error($koneksi)]);
    exit();
}

$petugas = mysqli_fetch_assoc($result);

// Pastikan foto profil ada
$petugas['foto_profil'] = file_exists($petugas['foto_profil']) 
    ? $petugas['foto_profil'] 
    : '../uploads/profil/default_profil.jpg';

echo json_encode($petugas);
?>