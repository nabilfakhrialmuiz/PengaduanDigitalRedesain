<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - PENGADUAN DIGITAL SMK NEGERI 6</title>
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

            .slide {
                height: 300px;
            }

            .gallery-title {
                font-size: 2rem;
            }

            .thumbnail {
                width: 60px;
                height: 60px;
            }
        }

        

        /* Galeri Slider Styles */
        .gallery-section {
            padding: 6rem 2rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .gallery-slider {
            position: relative;
            max-width: 900px;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 255, 136, 0.2);
        }

        .slider-container {
            display: flex;
            transition: transform 0.5s ease;
        }

        .slide {
            min-width: 100%;
            height: 500px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: var(--card-bg);
            border: 1px solid rgba(0, 255, 136, 0.2);
        }

        .slide img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .slider-controls {
            display: flex;
            justify-content: space-between;
            position: absolute;
            top: 50%;
            width: 100%;
            transform: translateY(-50%);
        }

        .slider-btn {
            background: rgba(0, 255, 136, 0.2);
            color: var(--primary-color);
            border: none;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .slider-btn:hover {
            background: rgba(0, 255, 136, 0.4);
        }

        .gallery-title {
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .thumbnail-container {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 2px solid transparent;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .thumbnail:hover, .thumbnail.active {
            border-color: var(--primary-color);
            transform: scale(1.1);
        }

        

        /* Footer */
        footer {
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(10px);
            padding: 2rem;
            text-align: center;
            border-top: 1px solid var(--primary-color);
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
                        <a href="../login/login_siswa.php">
                            <i class="fas fa-graduation-cap"></i> Siswa
                        </a>
                        <a href="../login/login_petugas.php">
                            <i class="fas fa-user-tie"></i> Petugas
                        </a>
                        <a href="../login/signup.php">
                            <i class="fas fa-user-plus"></i> Daftar
                        </a>
                        
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <section class="gallery-section">
        <h1 class="gallery-title">Galeri Kegiatan Sekolah</h1>
        
        <div class="gallery-slider">
            <div class="slider-container">
                <div class="slide">
                    <img src="../Leonardo_Gerbang_Sekolah.jpg" alt="Kegiatan Sekolah 1">
                </div>
                <div class="slide">
                    <img src="../Leonardo_Perpustakaan.jpg" alt="Kegiatan Sekolah 2">
                </div>
                <div class="slide">
                    <img src="../lab komputer.jpg" alt="Kegiatan Sekolah 3">
                </div>
                <div class="slide">
                    <img src="../Leonardo_Belajar_Coding.jpg" alt="Kegiatan Sekolah 4">
                </div>
                <div class="slide">
                    <img src="../Leonardo_Lorong_kelas.jpg" alt="Kegiatan Sekolah 5">
                </div>
                <div class="slide">
                    <img src="../Leonardo_Parkiran_Sekolah.jpg" alt="Kegiatan Sekolah 6">
                </div>
                <div class="slide">
                    <img src="../Gedung_Sekolah.jpg" alt="Kegiatan Sekolah 7">
                </div>
                <div class="slide">
                    <img src="../Lorong_Tangga.jpg" alt="Kegiatan Sekolah 8">
                </div>
                <div class="slide">
                    <img src="../Masjid_Sekolah.jpg" alt="Kegiatan Sekolah 9">
                </div>
                <div class="slide">
                    <img src="../Ruang_Guru.jpg" alt="Kegiatan Sekolah 10">
                </div>
                <div class="slide">
                    <img src="../Leonardo_Apel.jpg" alt="Kegiatan Sekolah 11">
                </div>
            </div>

            <div class="slider-controls">
                <button class="slider-btn prev-btn">
                    <i class="fas fa-chevron-left fa-2x"></i>
                </button>
                <button class="slider-btn next-btn">
                    <i class="fas fa-chevron-right fa-2x"></i>
                </button>
            </div>
        </div>

        <div class="thumbnail-container">
            <img src="../Leonardo_Gerbang_Sekolah.jpg" alt="Thumbnail 1" class="thumbnail active" data-index="0">
            <img src="../Leonardo_Perpustakaan.jpg" alt="Thumbnail 2" class="thumbnail" data-index="1">
            <img src="../lab komputer.jpg" alt="Thumbnail 3" class="thumbnail" data-index="2">
            <img src="../Leonardo_Belajar_Coding.jpg" alt="Thumbnail 4" class="thumbnail" data-index="3">
            <img src="../Leonardo_Lorong_kelas.jpg" alt="Thumbnail 5" class="thumbnail" data-index="4">
            <img src="../Leonardo_Parkiran_Sekolah.jpg" alt="Thumbnail 6" class="thumbnail" data-index="5">
            <img src="../Gedung_Sekolah.jpg" alt="Thumbnail 7" class="thumbnail" data-index="6">
            <img src="../Lorong_Tangga.jpg" alt="Thumbnail 8" class="thumbnail" data-index="7">
            <img src="../Masjid_Sekolah.jpg" alt="Thumbnail 9" class="thumbnail" data-index="8">
            <img src="../Ruang_Guru.jpg" alt="Thumbnail 10" class="thumbnail" data-index="9">
            <img src="../Leonardo_Apel.jpg" alt="Thumbnail 11" class="thumbnail" data-index="10">
        </div>
    </section>

    <footer>
        <p>&copy; 2024 Layanan Pengaduan Digital | SMK NEGERI 6 KOTA BEKASI</p>
    </footer>

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

        // Slider JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const sliderContainer = document.querySelector('.slider-container');
            const slides = document.querySelectorAll('.slide');
            const prevBtn = document.querySelector('.prev-btn');
            const nextBtn = document.querySelector('.next-btn');
            const thumbnails = document.querySelectorAll('.thumbnail');
            
            let currentIndex = 0;
            const totalSlides = slides.length;

            function updateSlider() {
                const offset = -currentIndex * 100;
                sliderContainer.style.transform = `translateX(${offset}%)`;
                
                // Update active thumbnail
                thumbnails.forEach(thumbnail => thumbnail.classList.remove('active'));
                thumbnails[currentIndex].classList.add('active');
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % totalSlides;
                updateSlider();
            }

            function prevSlide() {
                currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                updateSlider();
            }

            // Event Listeners for Navigation Buttons
            nextBtn.addEventListener('click', nextSlide);
            prevBtn.addEventListener('click', prevSlide);

            // Thumbnail Click Navigation
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    currentIndex = parseInt(this.dataset.index);
                    updateSlider();
                });
            });

            // Optional: Auto Slide
            setInterval(nextSlide, 5000);

        });
    </script>
</body>
</html>