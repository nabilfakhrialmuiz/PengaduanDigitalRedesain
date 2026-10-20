<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - PENGADUAN DIGITAL SMK NEGERI 6 KOTA BEKASI</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="light-theme.css">
    <script src="theme-toggle.js"></script>
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
            overflow-x: hidden;
        }

        /* Animated Background */
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
            0% {
                transform: translateX(-50%) translateY(-50%) rotate(0deg);
            }
            100% {
                transform: translateX(-50%) translateY(-50%) rotate(360deg);
            }
        }

        
        /* Navbar Styles */
        .navbar {
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--primary-color);
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .nav-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .nav-links a {
            color: var(--text-color);
            text-decoration: none;
            padding: 0.5rem 1rem;
            position: relative;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .nav-links a::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::before {
            width: 100%;
        }

        .theme-toggle-btn {
            background: transparent;
            color: var(--text-color);
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            margin-right: 1rem;
            transition: all 0.3s ease;
        }

        .theme-toggle-btn:hover {
            color: var(--primary-color);
            transform: rotate(180deg);
        }


        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropbtn {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: var(--dark-bg);
            padding: 1rem 2rem;
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            border: none;
            cursor: pointer;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dropbtn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            transition: 0.5s;
        }

        .dropbtn:hover::before {
            left: 100%;
        }

        .dropbtn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            min-width: 200px;
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid var(--primary-color);
            border-radius: 15px;
            overflow: hidden;
            z-index: 1000;
            transform: translateY(10px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .dropdown-content.active {
            display: block;
            transform: translateY(0);
            opacity: 1;
        }

        .dropdown-content a {
            color: var(--text-color);
            padding: 1rem 2rem;
            text-decoration: none;
            display: block;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dropdown-content a::before {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
            transform: translateX(-100%);
            transition: 0.5s;
        }

        .dropdown-content a:hover::before {
            transform: translateX(100%);
        }

        .dropdown-content a:hover {
            background: rgba(0, 255, 136, 0.1);
            padding-left: 2.5rem;
        }

        .dropdown-content a i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        /* Update menu-right styles */
        .menu-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .dropdown-content {
                position: fixed;
                top: auto;
                right: 50%;
                transform: translateX(50%) translateY(10px);
                width: 90%;
                max-width: 300px;
            }

            .dropdown-content.active {
                transform: translateX(50%) translateY(0);
            }
        }


        /* Hero Section */
        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding-top: 60px;
            position: relative;
            overflow: hidden;
        }

        .hero-content h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 3.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                text-shadow: 0 0 10px var(--primary-color),
                           0 0 20px var(--primary-color);
            }
            to {
                text-shadow: 0 0 20px var(--secondary-color),
                           0 0 30px var(--secondary-color);
            }
        }

        /* About Section */
        .section {
            padding: 4rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-align: center;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Features Section */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-item {
            background: var(--card-bg);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 15px;
            padding: 2rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            position: relative;
            overflow: hidden;
        }

        .feature-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, 
                transparent 0%, 
                rgba(0, 255, 136, 0.1) 50%, 
                transparent 100%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .feature-item:hover::before {
            transform: translateX(100%);
        }

        .feature-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.2);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        /* Stats Section */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .stat-item {
            background: var(--card-bg);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.2);
        }

        .stat-number {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        /* Footer */
        footer {
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(10px);
            padding: 2rem;
            text-align: center;
            border-top: 1px solid var(--primary-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .features, .stats {
                grid-template-columns: 1fr;
            }
        }

        /* Tombol Back to Top */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: var(--bg-color);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .back-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <nav class="navbar">
        <div class="nav-links">
            <div class="menu-left">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="about.php"><i class="fas fa-info-circle"></i> About</a>
                <a href="gallery.php"><i class="fas fa-images"></i> Galeri</a>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
            </div>
            <div class="menu-right">
                <button id="theme-toggle" class="theme-toggle-btn">
                    <i class="fas fa-sun"></i>
                </button>
                <div class="dropdown">
                    <button class="dropbtn">
                        <i class="fas fa-user"></i> Login
                    </button>
                    <div class="dropdown-content">
                        <a href="../login/login_siswa.php"><i class="fas fa-graduation-cap"></i> Siswa</a>
                        <a href="../login/login_petugas.php"><i class="fas fa-user-tie"></i> Petugas</a>
                        <a href="../login/signup.php"><i class="fas fa-user-plus"></i> Daftar</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="hero-content">
            <h1>Pengaduan Digital</h1>
            <p>Suara Anda, Perubahan Kami</p>
        </div>
    </div>

    <div class="section" id="about">
        <h2 class="section-title">Tentang Kami</h2>
        <p>Pengaduan Digital SMK Negeri 6 Kota Bekasi adalah platform inovatif yang dirancang untuk memfasilitasi komunikasi yang efektif antara siswa, guru, dan manajemen sekolah. Sistem ini memungkinkan setiap anggota komunitas sekolah untuk menyampaikan keluhan, saran, dan masukan secara terstruktur dan profesional.</p>
        <p>Kami berkomitmen untuk menciptakan lingkungan sekolah yang lebih baik melalui transparansi dan penanganan masalah yang efisien. Setiap pengaduan akan ditangani dengan serius dan profesional untuk memastikan solusi terbaik bagi semua pihak.</p>
    </div>

    <div class="section" id="features">
        <h2 class="section-title">Fitur Unggulan</h2>
        <div class="features">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Keamanan Data</h3>
                <p>Sistem keamanan berlapis untuk melindungi privasi pengguna</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Respon Cepat</h3>
                <p>Penanganan pengaduan yang cepat dan profesional</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Tracking Progress</h3>
                <p>Pemantauan status pengaduan secara real-time</p>
            </div>
        </div>
    </div>

    <div class="section" id="stats">
        <h2 class="section-title">Statistik Pengaduan</h2>
        <div class="stats">
            <div class="stat-item">
                <div class="stat-number">1000+</div>
                <div class="stat-label">Pengaduan Terselesaikan</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Layanan Support</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">98%</div>
                <div class="stat-label">Tingkat Kepuasan</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">15min</div>
                <div class="stat-label">Rata-rata Respon</div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Layanan Pengaduan Digital | SMK NEGERI 6 KOTA BEKASI</p>
    </footer>

    <!-- Tombol Back to Top -->
    <button id="backToTop" class="back-to-top" title="Kembali ke Atas">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropBtn = document.querySelector('.dropbtn');
            const dropContent = document.querySelector('.dropdown-content');
            let timeoutId;

            // Toggle dropdown on hover
            document.querySelector('.dropdown').addEventListener('mouseenter', function() {
                clearTimeout(timeoutId);
                dropContent.classList.add('active');
            });

            document.querySelector('.dropdown').addEventListener('mouseleave', function() {
                timeoutId = setTimeout(() => {
                    dropContent.classList.remove('active');
                }, 200);
            });

            // Toggle dropdown on click (for mobile)
            dropBtn.addEventListener('click', function(e) {
                e.preventDefault();
                dropContent.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    dropContent.classList.remove('active');
                }
            });
        });

        // Fungsi Back to Top
        document.addEventListener('DOMContentLoaded', function() {
            const backToTopButton = document.getElementById('backToTop');

            // Tampilkan/sembunyikan tombol berdasarkan scroll
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.add('show');
                } else {
                    backToTopButton.classList.remove('show');
                }
            });

            // Fungsi scroll smooth ke atas
            backToTopButton.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>