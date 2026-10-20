<?php
include 'koneksi.php';

// Ambil parameter filter dari URL
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';
$petugas_filter = isset($_GET['petugas']) ? $_GET['petugas'] : '';

// Bangun query dengan filter yang sama seperti di data_tanggapan.php
$query = "SELECT t.*, p.nama_petugas, pg.isi_laporan 
          FROM tanggapan t
          LEFT JOIN petugas p ON t.id_petugas = p.id_petugas
          LEFT JOIN pengaduan pg ON t.id_pengaduan = pg.id_pengaduan
          WHERE 1=1";

// Terapkan filter
if (!empty($search)) {
    $search = mysqli_real_escape_string($koneksi, $search);
    $query .= " AND (t.tanggapan LIKE '%$search%' OR 
                     p.nama_petugas LIKE '%$search%' OR 
                     pg.isi_laporan LIKE '%$search%')";
}

if (!empty($date_start) && !empty($date_end)) {
    $query .= " AND DATE(t.tgl_tanggapan) BETWEEN '$date_start' AND '$date_end'";
}

if (!empty($petugas_filter)) {
    $query .= " AND p.id_petugas = '$petugas_filter'";
}

$query .= " ORDER BY t.tgl_tanggapan DESC";

$result = mysqli_query($koneksi, $query);

// Set header untuk download CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=data_tanggapan_' . date('YmdHis') . '.csv');

// Buat file CSV
$output = fopen('php://output', 'w');

// Header CSV
fputcsv($output, array('ID Tanggapan', 'Isi Pengaduan', 'Tanggapan', 'Nama Petugas', 'Tanggal Tanggapan'));

// Isi data
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, array(
        $row['id_tanggapan'], 
        $row['isi_laporan'], 
        $row['tanggapan'], 
        $row['nama_petugas'], 
        $row['tgl_tanggapan']
    ));
}

fclose($output);
exit();