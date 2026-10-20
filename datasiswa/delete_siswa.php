<?php
session_start();
include '../koneksi.php';

// Enhanced logging function
function log_deletion_attempt($nik, $status, $message) {
    $log_file = 'deletion_log.txt';
    $log_entry = date('Y-m-d H:i:s') . " | NIK: $nik | Status: $status | Message: $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Check authentication and authorization
if(!isset($_SESSION['id_petugas']) || $_SESSION['level'] != 'admin') {
    $response = [
        'success' => false, 
        'message' => 'Unauthorized Access',
        'error_code' => 403
    ];
    log_deletion_attempt('N/A', 'UNAUTHORIZED', 'Attempt to delete without admin rights');
    echo json_encode($response);
    exit();
}

// Get NIK from POST parameter with advanced validation
$nik = isset($_POST['nik']) ? trim(mysqli_real_escape_string($koneksi, $_POST['nik'])) : '';

// Comprehensive input validation
if (empty($nik) || !preg_match('/^[0-9]+$/', $nik)) {
    $response = [
        'success' => false, 
        'message' => 'NIK tidak valid',
        'error_code' => 400
    ];
    log_deletion_attempt($nik, 'INVALID_NIK', 'Invalid NIK format');
    echo json_encode($response);
    exit();
}

// Begin transaction for safe deletion
mysqli_begin_transaction($koneksi);

try {
    // First, check if the student exists and get additional details
    $check_query = "SELECT nama FROM siswa WHERE nik = '$nik'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        mysqli_rollback($koneksi);
        $response = [
            'success' => false, 
            'message' => 'Siswa tidak ditemukan',
            'error_code' => 404
        ];
        log_deletion_attempt($nik, 'NOT_FOUND', 'Student record not found');
        echo json_encode($response);
        exit();
    }

    $student = mysqli_fetch_assoc($check_result);

    // Delete related tanggapan (responses) first
    $delete_tanggapan = "DELETE FROM tanggapan 
                         WHERE id_pengaduan IN (
                             SELECT id_pengaduan FROM pengaduan WHERE nik = '$nik'
                         )";
    $tanggapan_result = mysqli_query($koneksi, $delete_tanggapan);
    
    if ($tanggapan_result === false) {
        mysqli_rollback($koneksi);
        $response = [
            'success' => false, 
            'message' => 'Gagal menghapus data tanggapan',
            'error_code' => 500,
            'error_details' => mysqli_error($koneksi)
        ];
        log_deletion_attempt($nik, 'TANGGAPAN_DELETE_FAILED', $response['error_details']);
        echo json_encode($response);
        exit();
    }

    // Delete related pengaduan (complaints)
    $delete_pengaduan = "DELETE FROM pengaduan WHERE nik = '$nik'";
    $pengaduan_result = mysqli_query($koneksi, $delete_pengaduan);
    
    if ($pengaduan_result === false) {
        mysqli_rollback($koneksi);
        $response = [
            'success' => false, 
            'message' => 'Gagal menghapus data pengaduan',
            'error_code' => 500,
            'error_details' => mysqli_error($koneksi)
        ];
        log_deletion_attempt($nik, 'PENGADUAN_DELETE_FAILED', $response['error_details']);
        echo json_encode($response);
        exit();
    }

    // Delete the student record
    $delete_siswa = "DELETE FROM siswa WHERE nik = '$nik'";
    $siswa_result = mysqli_query($koneksi, $delete_siswa);

    if ($siswa_result) {
        mysqli_commit($koneksi);
        $response = [
            'success' => true, 
            'message' => 'Berhasil menghapus data siswa',
            'deleted_student_name' => $student['nama']
        ];
        log_deletion_attempt($nik, 'SUCCESS', 'Successfully deleted student and related records');
        echo json_encode($response);
    } else {
        mysqli_rollback($koneksi);
        $response = [
            'success' => false, 
            'message' => 'Gagal menghapus data siswa',
            'error_code' => 500,
            'error_details' => mysqli_error($koneksi)
        ];
        log_deletion_attempt($nik, 'SISWA_DELETE_FAILED', $response['error_details']);
        echo json_encode($response);
    }
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $response = [
        'success' => false, 
        'message' => 'Terjadi kesalahan sistem',
        'error_code' => 500,
        'error_details' => $e->getMessage()
    ];
    log_deletion_attempt($nik, 'EXCEPTION', $e->getMessage());
    echo json_encode($response);
}