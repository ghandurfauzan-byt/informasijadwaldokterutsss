<?php
session_start();
include 'koneksi.php'; // Pastikan koneksi.php sudah menggunakan SSL TiDB

// Jika sudah login, langsung ke halaman utama sesuai role
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Menggunakan Prepared Statement untuk keamanan
    $stmt = $koneksi->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Cek Password (menggunakan password_verify)
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];

            // Logika Redireksi Berdasarkan Role
            if ($row['role'] == 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Klinik Sehat Umat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, 'Poppins', sans-serif;
        }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f2b3d 0%, #1b4f72 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(2px);
            border-radius: 40px;
            box-shadow: 0 30px 50px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            padding: 40px 35px;
            text-align: center;
            transition: 0.3s;
        }
        .login-card h2 {
            font-size: 2rem;
            color: #0f2b3d;
            margin-bottom: 8px;
        }
        .sub {
            color: #2c7da0;
            margin-bottom: 30px;
            font-weight: 500;
        }
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .input-group label {
            font-weight: 600;
            color: #1e3a5f;
            margin-bottom: 5px;
            display: block;
        }
        .input-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #cbd5e1;
            border-radius: 60px;
            font-size: 1rem;
            transition: 0.2s;
        }
        .input-group input:focus {
            outline: none;
            border-color: #2c7da0;
            box-shadow: 0 0 0 3px rgba(44, 125, 160, 0.2);
        }
        button {
            background: #1f7b4d;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 60px;
            width: 100%;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        button:hover {
            background: #166534;
            transform: scale(1.02);
        }
        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 10px;
            border-radius: 60px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        .register-link {
            margin-top: 25px;
            font-size: 0.9rem;
        }
        .register-link a {
            color: #1f7b4d;
            text-decoration: none;
            font-weight: bold;
        }
        .back-home {
            margin-top: 15px;
            font-size: 0.85rem;
        }
        .back-home a {
            color: #5b7f95;
            text-decoration: none;
        }
        .back-home a:hover {
            color: #1f7b4d;
        }
        .demo {
            background: #e9f5f0;
            border-radius: 30px;
            padding: 8px;
            margin-top: 20px;
            font-size: 12px;
            color: #2c5e2e;
        }
    </style>
</head>
<body>
<div class="login-card">
    <i class="fas fa-hospital-user" style="font-size: 50px; color: #1f7b4d;"></i>
    <h2>Klinik Sehat Umat</h2>
    <div class="sub">Silakan login ke akun Anda</div>
    
    <?php if ($error): ?>
        <div class="error"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="input-group">
            <label><i class="fas fa-user"></i> Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required autofocus>
        </div>
        <div class="input-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <input type="password" name="password" placeholder="••••••" required>
        </div>
        <button type="submit"><i class="fas fa-sign-in-alt"></i> Masuk</button>
    </form>
    
    <div class="register-link">
        Belum punya akun? <a href="register.php">Daftar Sekarang</a>
    </div>
    <div class="back-home">
        <a href="landing.php"><i class="fas fa-home"></i> Kembali ke Beranda</a>
    </div>
    <div class="demo">
        <i class="fas fa-info-circle"></i> Demo: admin / admin123 | Daftar sebagai pasien
    </div>
</div>
</body>
</html>