<?php
session_start();
include 'koneksi.php';

// Cek apakah pengguna sudah login dan memiliki hak akses
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    echo json_encode([
        'success' => false, 
        'message' => 'Anda tidak memiliki izin untuk menghapus tanggapan.'
    ]);
    exit();
}

// Pastikan ada ID pengaduan yang dikirim
if(!isset($_POST['id_pengaduan']) || empty($_POST['id_pengaduan'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'ID Pengaduan tidak valid.'
    ]);
    exit();
}

$id_pengaduan = mysqli_real_escape_string($koneksi, $_POST['id_pengaduan']);

// Mulai transaksi untuk memastikan konsistensi data
mysqli_begin_transaction($koneksi);

try {
    // Hapus tanggapan terkait
    $query_hapus_tanggapan = "DELETE FROM tanggapan WHERE id_pengaduan = '$id_pengaduan'";
    mysqli_query($koneksi, $query_hapus_tanggapan);

    // Update status pengaduan kembali ke proses
    $query_update_status = "UPDATE pengaduan SET status = 'proses' WHERE id_pengaduan = '$id_pengaduan'";
    mysqli_query($koneksi, $query_update_status);

    // Commit transaksi
    mysqli_commit($koneksi);

    echo json_encode([
        'success' => true, 
        'message' => 'Tanggapan berhasil dihapus.'
    ]);
} catch (Exception $e) {
    // Rollback transaksi jika ada kesalahan
    mysqli_rollback($koneksi);

    echo json_encode([
        'success' => false, 
        'message' => 'Gagal menghapus tanggapan: ' . $e->getMessage()
    ]);
}

mysqli_close($koneksi);
?>