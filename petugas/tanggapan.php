<?php

session_start();

if(!isset($_SESSION['id_petugas']) || $_SESSION['level'] != 'petugas') {
    header("Location: ../login/login_petugas.php");
    exit();
}

include '../koneksi.php';

if(isset($_GET['id_notifikasi'])) {
    $id_notifikasi = $_GET['id_notifikasi'];
    $query = "SELECT p.*, s.nama 
              FROM tb_notifikasi_petugas np
              JOIN pengaduan p ON np.id_pengaduan = p.id_pengaduan
              JOIN siswa s ON p.nik = s.nik
              WHERE np.id_notifikasi = '$id_notifikasi'";
    $result = mysqli_query($koneksi, $query);
    $pengaduan = mysqli_fetch_assoc($result);
    
    // Tampilkan data pengaduan dan berikan tanggapan
}

// Get ID from URL if exists
$selected_id = isset($_GET['id']) ? $_GET['id'] : '';

// Modify the query to get pengaduan that's not completed
$query_pengaduan = "SELECT p.*, s.nama 
                    FROM pengaduan p 
                    JOIN siswa s ON p.nik = s.nik 
                    WHERE p.status != 'selesai'";
$result_pengaduan = mysqli_query($koneksi, $query_pengaduan);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi input
    if (empty($_POST['id_pengaduan']) || empty($_POST['tgl_tanggapan']) || empty($_POST['tanggapan'])) {
        $error = "Semua field harus diisi!";
    } else {
        $id_pengaduan = $_POST['id_pengaduan'];
        $tgl_tanggapan = $_POST['tgl_tanggapan'];
        $tanggapan = $_POST['tanggapan'];
        $id_petugas = $_SESSION['id_petugas'];

        // Mulai transaksi
        mysqli_begin_transaction($koneksi);

        try {
            // Prepared statement untuk insert tanggapan
            $stmt_tanggapan = mysqli_prepare($koneksi, 
                "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) 
                 VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_tanggapan, "issi", $id_pengaduan, $tgl_tanggapan, $tanggapan, $id_petugas);
            
            // Execute insert tanggapan
            if (!mysqli_stmt_execute($stmt_tanggapan)) {
                throw new Exception("Gagal menambahkan tanggapan: " . mysqli_stmt_error($stmt_tanggapan));
            }

            // Prepared statement untuk update status pengaduan
            $stmt_status = mysqli_prepare($koneksi, 
                "UPDATE pengaduan SET status = 'selesai' WHERE id_pengaduan = ?");
            mysqli_stmt_bind_param($stmt_status, "i", $id_pengaduan);
            
            // Execute update status
            if (!mysqli_stmt_execute($stmt_status)) {
                throw new Exception("Gagal memperbarui status pengaduan: " . mysqli_stmt_error($stmt_status));
            }

            // Commit transaksi
            mysqli_commit($koneksi);
            $success = "Tanggapan berhasil ditambahkan dan status pengaduan diperbarui!";

            // Tutup statement
            mysqli_stmt_close($stmt_tanggapan);
            mysqli_stmt_close($stmt_status);

            // Setelah insert tanggapan berhasil, tambahkan:
            $insert_notifikasi = "INSERT INTO tb_notifikasi_siswa (nik, id_pengaduan) 
            SELECT nik, $id_pengaduan FROM pengaduan 
            WHERE id_pengaduan = $id_pengaduan";
            mysqli_query($koneksi, $insert_notifikasi);

        } catch (Exception $e) {
            // Rollback transaksi jika ada kesalahan
            mysqli_rollback($koneksi);
            $error = "Error: " . $e->getMessage();
        }
    }
}

$query_pengaduan = "SELECT p.*, s.nama 
                    FROM pengaduan p 
                    JOIN siswa s ON p.nik = s.nik 
                    WHERE p.status != 'selesai'";
$result_pengaduan = mysqli_query($koneksi, $query_pengaduan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Tanggapan - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../utama/light-theme.css">
    <script src="../utama/theme-toggle.js"></script>
    <style>
        :root {
            --primary-color: #00ff88;
            --secondary-color: #00ccff;
            --bg-color: #0a0a0a;
            --text-color: #ffffff;
            --accent-color: #ff00ff;
            --dark-bg: #111111;
            --card-bg: rgba(255, 255, 255, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(45deg, #000000, #1a1a1a);
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 20px,
                rgba(0, 255, 136, 0.1) 20px,
                rgba(0, 255, 136, 0.1) 40px
            );
            animation: grid-move 20s linear infinite;
        }

        @keyframes grid-move {
            0% { transform: translateX(-50%) translateY(-50%) rotate(0deg); }
            100% { transform: translateX(-50%) translateY(-50%) rotate(360deg); }
        }

        .tanggapan-container {
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid var(--primary-color);
            border-radius: 15px;
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
        }

        .tanggapan-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .tanggapan-title h2 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-group select,
        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 8px;
            color: var(--text-color);
            font-family: 'Roboto', sans-serif;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .input-group select {
            appearance: none;
            cursor: pointer;
            padding-right: 2.5rem;
        }

        .input-group select option {
            background: var(--dark-bg);
            color: var(--text-color);
            padding: 0.5rem;
        }

        .input-group textarea {
            min-height: 120px;
            
        }

        .input-group label {
            position: absolute;
            top: 1rem;
            left: 1rem;
            color: rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
            pointer-events: none;
            z-index: 0;
        }

        .input-group select:focus + label,
        .input-group select:valid + label,
        .input-group input:focus + label,
        .input-group input:valid + label,
        .input-group textarea:focus + label,
        .input-group textarea:valid + label {
            top: -1rem;
            left: -0.3rem;
            font-size: 0.75rem;
            color: var(--primary-color);
            background: var(--bg-color);
            padding: 0 0.5rem;
        }

        .input-group select:focus,
        .input-group input:focus,
        .input-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.3);
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: var(--dark-bg);
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
        }

        .error,
        .success {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            text-align: center;
        }

        .error {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid rgba(255, 68, 68, 0.3);
            color: #ff4444;
        }

        .success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: var(--primary-color);
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 25px;
            font-family: 'Orbitron', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-btn:hover {
            background: rgba(0, 255, 136, 0.2);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.3);
        }

        .input-group::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            pointer-events: none;
            z-index: 2;
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <a href="home_petugas.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    <div class="tanggapan-container">
        <div class="tanggapan-title">
            <h2>Form Tanggapan</h2>
        </div>

        <?php if(isset($error)): ?>
            <div class="error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if(isset($success)): ?>
            <div class="success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
        <div class="input-group">
            <select name="id_pengaduan" required>
                <option value="" disabled <?php echo empty($selected_id) ? 'selected' : ''; ?>></option>
                <?php while($row = mysqli_fetch_assoc($result_pengaduan)): ?>
                    <option value="<?php echo $row['id_pengaduan']; ?>" 
                            <?php echo ($selected_id == $row['id_pengaduan']) ? 'selected' : ''; ?>>
                        ID: <?php echo $row['id_pengaduan']; ?> - <?php echo $row['nama']; ?> - 
                        <?php echo substr($row['isi_laporan'], 0, 50); ?>...
                    </option>
                <?php endwhile; ?>
            </select>
            <label>Pilih Pengaduan</label>
        </div>

            <div class="input-group">
                <input type="date" name="tgl_tanggapan" required>
                <label></label>
            </div>

            <div class="input-group">
                <textarea name="tanggapan" required></textarea>
                <label>Isi Tanggapan</label>
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-paper-plane"></i> Kirim Tanggapan
            </button>
        </form>
    </div>

    <script>
        document.querySelectorAll('.input-group input, .input-group select, .input-group textarea').forEach(element => {
            if (element.value) {
                element.parentElement.classList.add('focused');
            }
            
            element.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            element.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        );

         // Auto-trigger the label animation if there's a selected value
        window.addEventListener('DOMContentLoaded', (event) => {
            const selectElement = document.querySelector('select[name="id_pengaduan"]');
            if(selectElement.value) {
                selectElement.parentElement.classList.add('focused');
            }
        });
    </script>
</body>
</html>