<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_petugas']) || $_SESSION['level'] != 'admin') {
    header("Location: login/login_petugas.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_petugas = mysqli_real_escape_string($koneksi, $_POST['id_petugas']);
    $nama_petugas = mysqli_real_escape_string($koneksi, $_POST['nama_petugas']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $telp = mysqli_real_escape_string($koneksi, $_POST['telp']);
    $level = mysqli_real_escape_string($koneksi, $_POST['level']);
    $foto_profil_lama = mysqli_real_escape_string($koneksi, $_POST['foto_profil_lama']);

    // Cek apakah file foto di-upload
    $foto_path = $foto_profil_lama; // Default gunakan foto lama
    if(isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        $target_dir = "uploads/profil/";
        
        // Buat direktori jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Generate nama file unik
        $file_extension = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $unique_filename;

        // Upload file baru
        if(move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file)) {
            $foto_path = $target_file;

            // Hapus foto lama jika bukan default
            if ($foto_profil_lama && $foto_profil_lama !== 'uploads/profil/default_profil.jpg') {
                unlink($foto_profil_lama);
            }
        }
    }

    // Query update
    $query = "UPDATE petugas SET 
              nama_petugas = '$nama_petugas', 
              username = '$username', 
              telp = '$telp', 
              level = '$level', 
              foto_profil = '$foto_path'
              WHERE id_petugas = '$id_petugas'";

    if (mysqli_query($koneksi, $query)) {
        $_SESSION['success'] = "Data petugas berhasil diupdate.";
    } else {
        $_SESSION['error'] = "Gagal mengupdate data petugas: " . mysqli_error($koneksi);
    }

    header("Location: data_petugas.php");
    exit();
}
?>