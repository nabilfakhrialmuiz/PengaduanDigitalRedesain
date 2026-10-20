<?php
session_start();
if(!isset($_SESSION['id_petugas']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    header("Location: login_petugas.php");
    exit();
}

include 'koneksi.php';

$id_petugas = $_SESSION['id_petugas'];
$query = "SELECT * FROM petugas WHERE id_petugas = '$id_petugas'";
$result = mysqli_query($koneksi, $query);
$petugas = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_petugas = mysqli_real_escape_string($koneksi, $_POST['nama_petugas']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $telp = mysqli_real_escape_string($koneksi, $_POST['telp']);
    
    // Handle password update
    $password_update = "";
    if (!empty($_POST['new_password'])) {
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $password_update = ", password = '$new_password'";
    }

    $update_query = "UPDATE petugas SET 
                    nama_petugas = '$nama_petugas', 
                    username = '$username',
                    telp = '$telp'
                    $password_update 
                    WHERE id_petugas = '$id_petugas'";

    if (mysqli_query($koneksi, $update_query)) {
        $_SESSION['success'] = "Profil berhasil diperbarui!";
        header("Location: profil_petugas.php");
        exit();
    } else {
        $error = "Gagal memperbarui profil: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Petugas - PENGADUAN DIGITAL</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --text-color: #333;
            --bg-color: #f4f4f4;
            --card-bg: rgba(255, 255, 255, 0.9);
            --hover-color: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: var(--text-color);
            min-height: 100vh;
        }

        .navbar {
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
        }

        .nav-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 0 1rem;
        }

        .navbar.hidden {
            transform: translateY(-100%);
        }

        .menu-left {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .menu-right {
            margin-left: auto;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin: 0 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }

        .nav-links a:hover {
            background-color: var(--hover-color);
            transform: translateY(-2px);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: var(--secondary-color);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .nav-links a:hover::after {
            transform: scaleX(1);
        }

        .profile-container {
            max-width: 1200px;
            margin: 100px auto 0;
            padding: 2rem;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        .profile-sidebar {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .profile-content {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }

        .btn-update {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            width: auto;
            display: block;
            margin: 2rem auto 0;
            min-width: 200px;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 992px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-links">
            <div class="menu-left">
                <a href="home_petugas.php"><i class="fas fa-home"></i> Home</a>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                <a href="tanggapan.php"><i class="fas fa-comments"></i> Tanggapan</a>
                <a href="data_pengaduan.php"><i class="fas fa-file-alt"></i> Data Pengaduan</a>
            </div>
            <div class="menu-right">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="profile-container">
        <aside class="profile-sidebar">
            <h3>Menu Petugas</h3>
            <ul class="profile-nav">
                <li><a href="#" class="active"><i class="fas fa-user"></i> Informasi Profil</a></li>
                <li><a href="#"><i class="fas fa-lock"></i> Ganti Password</a></li>
                <li><a href="#"><i class="fas fa-bell"></i> Notifikasi</a></li>
                <li><a href="#"><i class="fas fa-shield-alt"></i> Keamanan</a></li>
            </ul>
        </aside>

        <main class="profile-content">
            <h2 class="section-title">Informasi Profil Petugas</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>ID Petugas</label>
                        <input type="text" value="<?php echo $petugas['id_petugas']; ?>" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label>Nama Petugas</label>
                        <input type="text" name="nama_petugas" value="<?php echo $petugas['nama_petugas']; ?>" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo $petugas['username']; ?>" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Level</label>
                        <input type="text" value="<?php echo $petugas['level']; ?>" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="tel" name="telp" value="<?php echo $petugas['telp']; ?>" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn-update">Update Profile</button>
            </form>
        </main>
    </div>

    <script>
        let lastScrollTop = 0;
        const navbar = document.querySelector('.navbar');
        const navbarHeight = navbar.offsetHeight;

        window.addEventListener('scroll', () => {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > navbarHeight) {
                navbar.classList.add('hidden');
            } else {
                navbar.classList.remove('hidden');
            }
            
            lastScrollTop = scrollTop;
        });
    </script>
</body>
</html>