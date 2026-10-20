<?php
session_start();
if(!isset($_SESSION['nik'])) {
    header("Location: ../login/login_siswa.php");
    exit();
}

include '../koneksi.php';

function createPetugasNotification($koneksi, $id_pengaduan) {
    // Dapatkan semua petugas dengan level 'petugas'
    $query_petugas = "SELECT id_petugas FROM petugas WHERE level = 'petugas'";
    $result_petugas = mysqli_query($koneksi, $query_petugas);

    while ($petugas = mysqli_fetch_assoc($result_petugas)) {
        $id_petugas = $petugas['id_petugas'];
        $insert_notifikasi = "INSERT INTO tb_notifikasi_petugas 
                               (id_pengaduan, id_petugas) 
                               VALUES ('$id_pengaduan', '$id_petugas')";
        mysqli_query($koneksi, $insert_notifikasi);
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tgl_pengaduan = $_POST['tgl_pengaduan'];
    $nik = $_SESSION['nik'];
    $isi_laporan = mysqli_real_escape_string($koneksi, $_POST['isi_laporan']);
    $status = '0'; // 0 for new submissions

    // File upload handling (previous code remains the same)
    $target_dir = __DIR__ . "/../uploads/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (!is_writable($target_dir)) {
        die("Error: Folder uploads tidak dapat ditulis. Periksa izin folder.");
    }

    $file_name = basename($_FILES["foto"]["name"]);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    $check = getimagesize($_FILES["foto"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $error = "File bukan gambar.";
        $uploadOk = 0;
    }

    if ($_FILES["foto"]["size"] > 5000000) {
        $error = "Maaf, file Anda terlalu besar.";
        $uploadOk = 0;
    }

    $allowed_types = array('jpg', 'png', 'jpeg', 'gif');
    if(!in_array($imageFileType, $allowed_types)) {
        $error = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            $status = 'proses';
            
            $query = "INSERT INTO pengaduan (tgl_pengaduan, nik, isi_laporan, foto, status) 
                      VALUES ('$tgl_pengaduan', '$nik', '$isi_laporan', '$file_name', '$status')";
    
            if (mysqli_query($koneksi, $query)) {
                $id_pengaduan = mysqli_insert_id($koneksi);
                createPetugasNotification($koneksi, $id_pengaduan);
                
                $notify_query = "INSERT INTO notifications (id_pengaduan) VALUES ('$id_pengaduan')";
                mysqli_query($koneksi, $notify_query);
                
                $_SESSION['success'] = "Pengaduan berhasil disubmit!";
                
                header("Location: home_siswa.php");

                if($insert_pengaduan_success) {
                    createNotification($koneksi, $id_pengaduan, $nik);
                }
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengaduan - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
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

        .pengaduan-container {
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

        .pengaduan-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .pengaduan-title h2 {
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

        .input-group textarea {
            min-height: 120px;
            resize: vertical;
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

        .input-group input:focus, 
        .input-group input:valid,
        .input-group textarea:focus,
        .input-group textarea:valid {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.3);
        }

        .input-group input[type="file"] {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(0, 255, 136, 0.2);
            color: var(--text-color);
        }

        .input-group input[type="file"]::file-selector-button {
            background: var(--primary-color);
            color: var(--bg-color);
            border: none;
            padding: 10px 20px;
            margin-right: 20px;
            border-radius: 5px;
            cursor: pointer;
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

        .error {
            color: #ff4444;
            text-align: center;
            margin-bottom: 1rem;
            background: rgba(255, 68, 68, 0.1);
            padding: 1rem;
            border-radius: 8px;
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
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <a href="home_siswa.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>  Kembali
    </a>

    <div class="pengaduan-container">
        <div class="pengaduan-title">
            <h2>Form Pengaduan</h2>
        </div>

        <?php if(isset($error)): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="input-group">
                <input type="date" name="tgl_pengaduan" required>
                <label></label>
            </div>
            
            <div class="input-group">
                <textarea name="isi_laporan" required></textarea>
                <label>Isi Laporan</label>
            </div>
            
            <div class="input-group">
                <input type="file" name="foto" accept="image/*" required id="inputfoto">
                <label>Unggah Foto</label>
            </div>
            
            <button type="submit" class="btn">Kirim Pengaduan</button>
        </form>
    </div>
</body>
</html>