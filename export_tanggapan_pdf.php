<?php
// Unduh FPDF dari http://www.fpdf.org/ dan letakkan di folder yang sama
require('fpdf.php');
include 'koneksi.php';

class PDF extends FPDF {
    // Header halaman
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Data Tanggapan', 0, 1, 'C');
        $this->Cell(0, 10, 'PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI', 0, 1, 'C');
        $this->Ln(10);
    }
    
    // Footer halaman
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Ambil parameter filter dari URL
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';
$petugas_filter = isset($_GET['petugas']) ? $_GET['petugas'] : '';

// Bangun query dengan filter
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

// Buat PDF
$pdf = new PDF('L'); // Landscape
$pdf->AliasNbPages();
$pdf->AddPage();

// Header tabel
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(25, 10, 'ID Tanggapan', 1);
$pdf->Cell(50, 10, 'Isi Pengaduan', 1);
$pdf->Cell(50, 10, 'Tanggapan', 1);
$pdf->Cell(40, 10, 'Nama Petugas', 1);
$pdf->Cell(40, 10, 'Tanggal Tanggapan', 1);
$pdf->Ln();

// Isi data
$pdf->SetFont('Arial', '', 9);
while ($row = mysqli_fetch_assoc($result)) {
    // Potong teks yang terlalu panjang
    $isi_pengaduan = substr($row['isi_laporan'], 0, 50) . (strlen($row['isi_laporan']) > 50 ? '...' : '');
    $tanggapan = substr($row['tanggapan'], 0, 50) . (strlen($row['tanggapan']) > 50 ? '...' : '');
    
    $pdf->Cell(25, 10, $row['id_tanggapan'], 1);
    $pdf->Cell(50, 10, $isi_pengaduan, 1);
    $pdf->Cell(50, 10, $tanggapan, 1);
    $pdf->Cell(40, 10, $row['nama_petugas'], 1);
    $pdf->Cell(40, 10, $row['tgl_tanggapan'], 1);
    $pdf->Ln();
}

// Output PDF
$pdf->Output('D', 'data_tanggapan_' . date('YmdHis') . '.pdf');
exit();