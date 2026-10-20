<?php
// export_siswa_pdf.php
require('../fpdf.php');  // Adjust path to your FPDF library
include '../koneksi.php';

class PDFSiswa extends FPDF {
    function Header() {
        // Cyberpunk-style header
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(0, 255, 136);  // Neon green
        $this->Cell(0, 10, 'DATA SISWA - PENGADUAN DIGITAL', 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function SiswaTable($data) {
        // Table header
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(0, 255, 136, 20);  // Light neon green
        $header = ['NIK', 'Nama', 'Username', 'Email', 'Telepon', 'Kelas'];
        $w = [30, 40, 30, 50, 20, 20];
        
        for($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        $this->Ln();

        // Data rows
        $this->SetFont('Arial', '', 9);
        foreach($data as $row) {
            $this->Cell($w[0], 6, $row['nik'], 1);
            $this->Cell($w[1], 6, $row['nama'], 1);
            $this->Cell($w[2], 6, $row['username'], 1);
            $this->Cell($w[3], 6, $row['email'], 1);
            $this->Cell($w[4], 6, $row['telp'], 1);
            $this->Cell($w[5], 6, $row['kelas'], 1);
            $this->Ln();
        }
    }
}

// Start session and check authentication
session_start();
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    die("Unauthorized access");
}

// Fetch all siswa data
$query = "SELECT * FROM siswa";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($koneksi));
}

$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Create PDF
$pdf = new PDFSiswa();
$pdf->AliasNbPages();
$pdf->AddPage('L');  // Landscape orientation
$pdf->SiswaTable($data);

// Output PDF
$filename = 'Data_Siswa_' . date('YmdHis') . '.pdf';
$pdf->Output($filename, 'D');  // Download