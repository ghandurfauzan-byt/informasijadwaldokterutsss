<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik Sehat Umat - Layanan Kesehatan Terpercaya</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f0f9ff; scroll-behavior: smooth; }
        
        /* Navbar */
        .navbar {
            background: white;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo { font-size: 1.5rem; font-weight: 700; color: #1e3a8a; }
        .logo i { color: #10b981; margin-right: 8px; }
        .nav-links a {
            margin-left: 20px;
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
            transition: 0.2s;
        }
        .nav-links a:hover { color: #10b981; }
        .btn-login {
            background: #10b981;
            color: white !important;
            padding: 8px 25px;
            border-radius: 40px;
        }
        .btn-logout {
            background: #ef4444;
            color: white !important;
            padding: 8px 25px;
            border-radius: 40px;
        }

        /* Hero */
        .hero {
            background: linear-gradient(120deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 100px 40px;
            text-align: center;
        }
        .hero h1 { font-size: 3rem; margin-bottom: 20px; }
        .hero p { font-size: 1.2rem; max-width: 800px; margin: auto; opacity: 0.9; line-height: 1.6; }
        .hero-buttons { margin-top: 40px; }
        .hero-buttons a {
            display: inline-block;
            padding: 14px 35px;
            margin: 0 10px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-primary { background: #10b981; color: white; border: none; }
        .btn-primary:hover { background: #059669; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(16,185,129,0.4); }
        .btn-outline { background: transparent; border: 2px solid white; color: white; }
        .btn-outline:hover { background: white; color: #1e3a8a; transform: translateY(-3px); }

        /* Features */
        .features { padding: 80px 40px; text-align: center; }
        .features h2 { font-size: 2.2rem; color: #1e2a3a; margin-bottom: 50px; }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: auto;
        }
        .feature-card {
            background: white;
            padding: 40px 20px;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .feature-card:hover { transform: translateY(-10px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .feature-card i { font-size: 3rem; color: #3b82f6; margin-bottom: 20px; }
        .feature-card h3 { margin-bottom: 15px; color: #1e3a8a; }
        .feature-card p { color: #64748b; font-size: 0.95rem; }

        /* Jadwal */
        .schedule-preview { background: #e0f2fe; padding: 80px 40px; text-align: center; }
        .schedule-preview h2 { margin-bottom: 40px; color: #1e2a3a; }
        .doctor-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            max-width: 1100px;
            margin: auto;
        }
        .doctor-item {
            background: white;
            border-radius: 20px;
            padding: 25px;
            width: 300px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .doctor-item i { font-size: 2.5rem; color: #10b981; margin-bottom: 15px; }
        .doctor-item h4 { color: #1e3a8a; margin-bottom: 5px; }
        .doctor-item .specialty { color: #10b981; font-weight: 600; font-size: 0.9rem; margin-bottom: 10px; }
        .doctor-item .time { color: #64748b; font-size: 0.85rem; }

        footer { background: #0f172a; color: #94a3b8; text-align: center; padding: 40px; font-size: 0.95rem; }
        
        @media (max-width: 768px) {
            .navbar { padding: 15px 20px; }
            .hero h1 { font-size: 2.2rem; }
            .hero-buttons a { display: block; margin: 15px auto; width: 90%; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo"><i class="fas fa-hospital-user"></i> Klinik Sehat Umat</div>
        <div class="nav-links">
            <a href="#features">Layanan</a>
            <a href="#jadwal">Jadwal</a>
            <?php if(isset($_SESSION['username'])): ?>
                <a href="index.php" style="font-weight: bold; color: #1e3a8a;">Dashboard</a>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="register.php" style="background:#e2e8f0; padding:8px 25px; border-radius:40px;">Daftar</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero">
        <h1>Kesehatan Anda Prioritas Kami</h1>
        <p>Kami hadir memberikan pelayanan medis profesional dengan sentuhan kasih sayang. Daftar secara online untuk konsultasi lebih cepat dan mudah.</p>
        <div class="hero-buttons">
            <?php if(!isset($_SESSION['username'])): ?>
                <a href="register.php" class="btn-primary"><i class="fas fa-user-plus"></i> Mulai Daftar</a>
                <a href="login.php" class="btn-outline">Masuk ke Akun</a>
            <?php else: ?>
                <a href="index.php" class="btn-primary">Buka Dashboard Anda</a>
            <?php endif; ?>
        </div>
    </section>

    <section id="features" class="features">
        <h2><i class="fas fa-star-of-life"></i> Mengapa Memilih Kami?</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <i class="fas fa-calendar-check"></i>
                <h3>Jadwal Dokter</h3>
                <p>Informasi waktu praktik dokter yang akurat dan selalu diperbarui secara realtime.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-ticket-alt"></i>
                <h3>Antrian Online</h3>
                <p>Ambil nomor antrian tanpa perlu datang subuh. Pantau status antrian dari ponsel.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-comment-medical"></i>
                <h3>Konsultasi Admin</h3>
                <p>Hubungi petugas kami melalui fitur chat untuk bantuan pendaftaran dan informasi.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-chart-bar"></i>
                <h3>Statistik Sehat</h3>
                <p>Pantau indikator kesehatan nasional terbaru melalui integrasi data resmi BPS.</p>
            </div>
        </div>
    </section>

    <section id="jadwal" class="schedule-preview">
        <h2><i class="fas fa-clock"></i> Jadwal Praktik Dokter</h2>
        <div class="doctor-list">
            <div class="doctor-item">
                <i class="fas fa-user-md"></i>
                <h4>dr. Ahmad Hidayat</h4>
                <p class="specialty">Spesialis Penyakit Dalam</p>
                <p class="time">Senin - Kamis | 08:00 - 12:00</p>
            </div>
            <div class="doctor-item">
                <i class="fas fa-female"></i>
                <h4>dr. Siti Rahma</h4>
                <p class="specialty">Spesialis Anak (Poli KIA)</p>
                <p class="time">Selasa & Kamis | 09:00 - 13:00</p>
            </div>
            <div class="doctor-item">
                <i class="fas fa-tooth"></i>
                <h4>dr. Budi Santoso</h4>
                <p class="specialty">Spesialis Gigi & Mulut</p>
                <p class="time">Rabu & Jumat | 13:00 - 16:00</p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 Klinik Sehat Umat. All rights reserved.</p>
        <p style="margin-top:10px; font-size:0.8rem; opacity:0.7;"><i class="fas fa-map-marker-alt"></i> Jl. Kesehatan No. 123, Jakarta Pusat</p>
    </footer>
</body>
</html>