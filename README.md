# Pengaduan Digital — Redesain

Pengaduan Digital adalah aplikasi berbasis web untuk membantu proses penyampaian dan pengelolaan pengaduan di lingkungan sekolah. Repositori ini berisi versi redesain dari proyek Pengaduan Digital yang saya kembangkan sebagai salah satu persyaratan kelulusan dari Sekolah Menengah Kejuruan (SMK), jurusan Rekayasa Perangkat Lunak (RPL).

Versi ini berfokus pada pengembangan dan penyempurnaan dari proyek sebelumnya. Repositori versi awal akan dipublikasikan secara terpisah agar kode versi asli dan versi redesain dapat dipelajari serta dibandingkan dengan lebih jelas.

## Tentang Proyek

Aplikasi ini dirancang untuk mendukung alur pengaduan antara siswa dan pihak sekolah. Struktur kode menunjukkan adanya area untuk siswa, petugas, dan administrator, beserta halaman untuk mengelola pengaduan, tanggapan, profil, statistik, serta beberapa fitur pendukung akun dan notifikasi.

Repositori ini merupakan proyek pembelajaran dan dokumentasi proses pengembangan. Detail fitur yang tersedia dapat berubah seiring proses penyempurnaan aplikasi.

## Fitur

Berdasarkan struktur kode yang tersedia, proyek ini mencakup beberapa bagian berikut:

- Pengelolaan pengaduan dan tanggapan.
- Area pengguna untuk siswa, petugas, dan administrator.
- Halaman profil dan pengaturan akun.
- Statistik pengaduan dan tanggapan.
- Ekspor data tanggapan ke format CSV dan PDF.
- Fitur pendukung autentikasi, termasuk kode verifikasi dua faktor pada bagian tertentu.
- Komponen pendukung notifikasi.
- Penyimpanan berkas yang diunggah.

Ketersediaan dan perilaku setiap fitur tetap perlu diverifikasi melalui pengujian aplikasi secara langsung.

## Teknologi

Teknologi dan pustaka yang digunakan pada repositori ini meliputi:

- **PHP** untuk logika aplikasi sisi server.
- **MySQL** untuk penyimpanan data, dengan koneksi database melalui MySQLi.
- **Composer** untuk pengelolaan dependensi PHP.
- **PHPMailer** untuk kebutuhan pengiriman email.
- **PragmaRX Google2FA** untuk dukungan autentikasi dua faktor.
- **Twilio SDK** untuk integrasi layanan Twilio.
- **PHP dotenv** untuk membaca konfigurasi lingkungan.
- **FPDF** untuk pembuatan dokumen PDF.

## Struktur Repositori

Beberapa direktori dan berkas utama:

| Direktori atau berkas | Keterangan |
| --- | --- |
| `admin/` | Halaman dan komponen untuk administrator. |
| `petugas/` | Halaman dan komponen untuk petugas. |
| `siswa/` | Halaman dan komponen untuk siswa. |
| `datapetugas/` | Direktori yang berkaitan dengan data petugas. |
| `datasiswa/` | Direktori yang berkaitan dengan data siswa. |
| `utama/` | Direktori halaman pendukung aplikasi. |
| `login/` | Bagian yang berkaitan dengan proses login. |
| `uploads/` | Direktori untuk berkas yang diunggah. |
| `koneksi.php` | Konfigurasi koneksi database. |
| `data_pengaduan.php` | Halaman atau proses terkait data pengaduan. |
| `data_tanggapan.php` | Halaman atau proses terkait data tanggapan. |
| `statistik.php` | Bagian statistik aplikasi. |
| `export_tanggapan_csv.php` | Proses ekspor tanggapan ke CSV. |
| `export_tanggapan_pdf.php` | Proses ekspor tanggapan ke PDF. |
| `composer.json` | Daftar dependensi PHP yang dikelola Composer. |

Struktur di atas merupakan ringkasan, bukan daftar seluruh berkas di dalam repositori.

## Menjalankan di Lingkungan Lokal

Petunjuk berikut merupakan gambaran awal untuk menyiapkan proyek PHP secara lokal. Aplikasi memerlukan konfigurasi database yang sesuai dengan kode dan struktur database proyek.

1. Pasang lingkungan pengembangan PHP lokal, misalnya XAMPP, yang menyediakan PHP, Apache, dan MySQL/MariaDB.
2. Clone repositori ini ke direktori proyek web lokal:

   ```bash
   git clone https://github.com/nabilfakhrialmuiz/PengaduanDigitalRedesain.git
   ```

3. Masuk ke direktori proyek:

   ```bash
   cd PengaduanDigitalRedesain
   ```

4. Buat database bernama `pengaduandigital`, atau sesuaikan nama database dengan konfigurasi yang digunakan.
5. Sesuaikan pengaturan koneksi database pada `koneksi.php` dengan konfigurasi lokal.
6. Pasang dependensi PHP menggunakan Composer:

   ```bash
   composer install
   ```

7. Jalankan Apache dan MySQL, kemudian akses direktori proyek melalui browser menggunakan alamat lokal sesuai konfigurasi server web.

**Catatan:** Repositori ini belum mendokumentasikan skema database secara lengkap. Siapkan struktur tabel yang dibutuhkan aplikasi sebelum menjalankannya. Konfigurasi email, Twilio, dan fitur eksternal lainnya juga perlu disesuaikan apabila fitur tersebut digunakan. Langkah di atas tidak menjamin aplikasi langsung berjalan tanpa penyesuaian.

## Versi Awal dan Versi Redesain

Proyek ini memiliki dua versi yang dipisahkan ke dalam repositori berbeda:

- **Versi redesain:** repositori ini, berisi pengembangan ulang dan penyempurnaan antarmuka maupun bagian aplikasi.
- **Versi awal:** akan tersedia pada repositori terpisah setelah dipublikasikan.

Pemisahan repositori dimaksudkan agar riwayat dan implementasi kedua versi tetap jelas. Tautan ke repositori versi awal akan ditambahkan di bagian ini setelah repositori tersebut tersedia.

## Tujuan Pengembangan

Proyek ini dibuat sebagai bagian dari persyaratan kelulusan SMK jurusan Rekayasa Perangkat Lunak. Selain memenuhi kebutuhan tugas akhir, proyek ini menjadi dokumentasi proses belajar dalam membangun aplikasi web, mengelola kode sumber, menggunakan database, serta mengembangkan dan menyempurnakan aplikasi yang sudah ada.

## Status Proyek

Proyek ini merupakan proyek akademik. Fitur, konfigurasi, dan dokumentasi masih dapat disempurnakan. Sebelum digunakan di lingkungan nyata, aplikasi perlu melalui pengujian fungsional dan pemeriksaan keamanan lebih lanjut.

## Catatan Keamanan

Jangan menyimpan kata sandi, token, kredensial layanan, atau informasi sensitif lain ke repositori publik. Gunakan berkas konfigurasi lokal yang tidak dilacak Git untuk menyimpan rahasia, dan pastikan berkas seperti `.env` tidak berisi kredensial aktif. Jika kredensial pernah terunggah ke repositori publik, segera cabut atau rotasi kredensial tersebut.

---

Dikembangkan oleh [nabilfakhrialmuiz](https://github.com/nabilfakhrialmuiz) sebagai proyek akhir pembelajaran Rekayasa Perangkat Lunak di SMK.
