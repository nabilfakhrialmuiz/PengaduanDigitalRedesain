<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['id_petugas']) || $_SESSION['level'] != 'admin') {
    header("Location: ../login/login_petugas.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggapan = $_POST['tanggapan'];
    $tgl_tanggapan = $_POST['tgl_tanggapan'];
    
    $query = "UPDATE tanggapan SET 
              tanggapan = ?, 
              tgl_tanggapan = ? 
              WHERE id_tanggapan = ?";
              
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $tanggapan, $tgl_tanggapan, $id_tanggapan);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Tanggapan berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Gagal memperbarui tanggapan: " . mysqli_error($koneksi);
    }
    
    mysqli_stmt_close($stmt);
    header("Location: data_tanggapan.php");
    exit();
}
?>