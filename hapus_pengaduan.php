<?php
session_start();
include 'koneksi.php';

// Cek apakah pengguna sudah login dan memiliki hak akses
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    echo json_encode([
        'success' => false, 
        'message' => 'Anda tidak memiliki izin untuk menghapus data.'
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
    // Hapus file foto jika ada
    $query_foto = "SELECT foto FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'";
    $result_foto = mysqli_query($koneksi, $query_foto);
    $row_foto = mysqli_fetch_assoc($result_foto);
    
    if($row_foto && !empty($row_foto['foto'])) {
        $foto_path = 'uploads/' . $row_foto['foto'];
        if(file_exists($foto_path)) {
            unlink($foto_path);
        }
    }

    // Hapus tanggapan terkait terlebih dahulu
    $query_hapus_tanggapan = "DELETE FROM tanggapan WHERE id_pengaduan = '$id_pengaduan'";
    mysqli_query($koneksi, $query_hapus_tanggapan);

    // Hapus notifikasi terkait
    $query_hapus_notifikasi = "DELETE FROM notifications WHERE id_pengaduan = '$id_pengaduan'";
    mysqli_query($koneksi, $query_hapus_notifikasi);

    // Hapus pengaduan
    $query_hapus_pengaduan = "DELETE FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'";
    mysqli_query($koneksi, $query_hapus_pengaduan);

    // Commit transaksi
    mysqli_commit($koneksi);

    echo json_encode([
        'success' => true, 
        'message' => 'Pengaduan berhasil dihapus.'
    ]);
} catch (Exception $e) {
    // Rollback transaksi jika ada kesalahan
    mysqli_rollback($koneksi);

    echo json_encode([
        'success' => false, 
        'message' => 'Gagal menghapus pengaduan: ' . $e->getMessage()
    ]);
}

mysqli_close($koneksi);
?>