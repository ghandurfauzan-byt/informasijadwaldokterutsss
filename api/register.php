<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $role = $_POST['role'];

    if (empty($username) || empty($password) || empty($nama_lengkap)) {
        $error = "Semua field harus diisi.";
    } elseif ($password !== $confirm) {
        $error = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 4) {
        $error = "Password minimal 4 karakter.";
    } else {
        $cek = $koneksi->prepare("SELECT id FROM users WHERE username = ?");
        $cek->bind_param("s", $username);
        $cek->execute();
        $cek->store_result();
        if ($cek->num_rows > 0) {
            $error = "Username sudah terdaftar.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $koneksi->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hashed, $nama_lengkap, $role);
            if ($stmt->execute()) {
                $success = "Pendaftaran berhasil! <a href='login.php'>Login di sini</a>";
            } else {
                $error = "Gagal mendaftar.";
            }
            $stmt->close();
        }
        $cek->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar | Klinik Sehat Umat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Roboto; }
        body {
            background: linear-gradient(145deg, #0f2b3d 0%, #1b4f72 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .register-card {
            background: white;
            border-radius: 48px;
            padding: 35px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 30px 50px rgba(0,0,0,0.3);
        }
        h2 { text-align: center; color: #0f2b3d; margin-bottom: 5px; }
        .sub { text-align: center; color: #2c7da0; margin-bottom: 25px; }
        .input-group { margin-bottom: 15px; }
        .input-group label { font-weight: 600; display: block; margin-bottom: 5px; color: #1e3a5f; }
        input, select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 60px;
            font-size: 0.95rem;
        }
        button {
            background: #1f7b4d;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 60px;
            color: white;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 10px;
        }
        .error, .success { padding: 10px; border-radius: 60px; text-align: center; margin-bottom: 15px; }
        .error { background: #fee2e2; color: #b91c1c; }
        .success { background: #d1fae5; color: #065f46; }
        .login-link { text-align: center; margin-top: 20px; }
        a { color: #1f7b4d; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
<div class="register-card">
    <i class="fas fa-user-plus" style="font-size: 40px; display: block; text-align: center; color:#1f7b4d;"></i>
    <h2>Daftar Akun Baru</h2>
    <div class="sub">Isi data dengan benar</div>
    <?php if ($error): ?><div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
    <form method="POST">
        <div class="input-group">
            <label><i class="fas fa-user"></i> Nama Lengkap</label>
            <input type="text" name="nama_lengkap" required>
        </div>
        <div class="input-group">
            <label><i class="fas fa-id-card"></i> Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="input-group">
            <label><i class="fas fa-lock"></i> Password (min 4)</label>
            <input type="password" name="password" required>
        </div>
        <div class="input-group">
            <label><i class="fas fa-lock"></i> Konfirmasi Password</label>
            <input type="password" name="confirm_password" required>
        </div>
        <div class="input-group">
            <label><i class="fas fa-user-tag"></i> Daftar sebagai</label>
            <select name="role">
                <option value="pasien">Pasien</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit"><i class="fas fa-paper-plane"></i> Daftar</button>
    </form>
    <div class="login-link">Sudah punya akun? <a href="login.php">Login</a></div>
</div>
</body>
</html>