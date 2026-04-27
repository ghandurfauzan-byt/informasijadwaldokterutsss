<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Ambil data jadwal
$jadwal = $koneksi->query("SELECT * FROM jadwal_dokter ORDER BY id");

// Ambil data antrean (semua poli)
$antrean = $koneksi->query("SELECT * FROM antrian ORDER BY created_at DESC");

// Ambil data user (kecuali admin yang sedang login)
$current_admin = $_SESSION['username'];
$users = $koneksi->query("SELECT id, username, nama_lengkap, role, created_at FROM users WHERE username != '$current_admin' ORDER BY created_at DESC");

// Ambil daftar poli unik untuk reset antrean per poli
$poli_list = $koneksi->query("SELECT DISTINCT poli FROM antrian WHERE poli IS NOT NULL");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Klinik Sehat Umat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Roboto; }
        body { background:#f0f7ff; padding:20px; }
        .container { max-width:1400px; margin:auto; background:white; border-radius:30px; box-shadow:0 20px 40px rgba(0,0,0,0.05); padding:30px; }
        h1 { color:#2c3e50; margin-bottom:10px; }
        h2 { margin:30px 0 15px 0; color:#2980b9; border-left:5px solid #2980b9; padding-left:15px; }
        .btn-back, .btn-bps { background:#7f8c8d; color:white; padding:8px 20px; border-radius:30px; text-decoration:none; display:inline-block; margin-bottom:20px; margin-right:10px; }
        .btn-bps { background:#27ae60; }
        table { width:100%; border-collapse:collapse; margin-top:10px; background:white; border-radius:15px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
        th { background:#34495e; color:white; padding:12px; text-align:left; }
        td { padding:10px; border-bottom:1px solid #ecf0f1; }
        .btn-edit, .btn-delete, .btn-reset, .btn-add { padding:5px 12px; border-radius:20px; text-decoration:none; font-size:13px; display:inline-block; margin:2px; }
        .btn-edit { background:#f39c12; color:white; }
        .btn-delete { background:#e74c3c; color:white; }
        .btn-reset { background:#e67e22; color:white; }
        .btn-add { background:#27ae60; color:white; margin-bottom:10px; }
        form { display:inline; }
        input, select { padding:8px 12px; border-radius:30px; border:1px solid #ccc; margin:5px 0; }
        .form-inline { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:20px; background:#f8fafc; padding:15px; border-radius:20px; }
        .success { background:#d4edda; color:#155724; padding:10px; border-radius:40px; margin-bottom:15px; }
        .error { background:#f8d7da; color:#721c24; padding:10px; border-radius:40px; margin-bottom:15px; }
    </style>
</head>
<body>
<div class="container">
    <div>
        <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Halaman Utama</a>
        <a href="bps_data.php" class="btn-bps"><i class="fas fa-chart-bar"></i> Data Statistik BPS</a>
    </div>
    <h1>⚙️ Admin Panel - Klinik Sehat Umat</h1>
    <p>Selamat datang, <?= $_SESSION['nama_lengkap'] ?> (Admin)</p>

    <!-- Notifikasi -->
    <?php if(isset($_GET['success'])): ?>
        <div class="success">✅ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
        <div class="error">❌ <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <!-- ========== MANAJEMEN JADWAL DOKTER ========== -->
    <h2>📅 Manajemen Jadwal Dokter</h2>
    <div class="form-inline">
        <form action="proses_admin.php" method="POST" style="display:flex; gap:10px; flex-wrap:wrap;">
            <input type="text" name="nama_dokter" placeholder="Nama Dokter" required>
            <input type="text" name="spesialis" placeholder="Spesialis (Poli Umum, dll)" required>
            <input type="text" name="jam" placeholder="Jam Praktik" required>
            <button type="submit" name="action" value="tambah_jadwal" class="btn-add"><i class="fas fa-plus"></i> Tambah Jadwal</button>
        </form>
    </div>
    <table>
        <thead><tr><th>ID</th><th>Nama Dokter</th><th>Spesialis</th><th>Jam Praktik</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php while($row = $jadwal->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nama_dokter']) ?></td>
                <td><?= htmlspecialchars($row['spesialis']) ?></td>
                <td><?= htmlspecialchars($row['jam']) ?></td>
                <td>
                    <form action="proses_admin.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="text" name="nama_dokter" value="<?= htmlspecialchars($row['nama_dokter']) ?>" required style="width:120px;">
                        <input type="text" name="spesialis" value="<?= htmlspecialchars($row['spesialis']) ?>" required style="width:100px;">
                        <input type="text" name="jam" value="<?= htmlspecialchars($row['jam']) ?>" required style="width:150px;">
                        <button type="submit" name="action" value="edit_jadwal" class="btn-edit"><i class="fas fa-edit"></i> Edit</button>
                    </form>
                    <form action="proses_admin.php" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus jadwal ini?')">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" name="action" value="hapus_jadwal" class="btn-delete"><i class="fas fa-trash"></i> Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- ========== MANAJEMEN ANTREAN ========== -->
    <h2>📋 Manajemen Antrean</h2>
    <div class="form-inline">
        <form action="proses_admin.php" method="POST">
            <button type="submit" name="action" value="reset_semua_antrean" class="btn-reset" onclick="return confirm('Hapus SEMUA antrean?')"><i class="fas fa-trash-alt"></i> Reset Semua Antrean</button>
        </form>
        <form action="proses_admin.php" method="POST">
            <select name="poli_reset" required>
                <option value="">Pilih Poli</option>
                <?php while($p = $poli_list->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($p['poli']) ?>"><?= htmlspecialchars($p['poli']) ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="action" value="reset_per_poli" class="btn-reset" onclick="return confirm('Hapus antrean untuk poli terpilih?')"><i class="fas fa-filter"></i> Reset per Poli</button>
        </form>
    </div>
    <table>
        <thead><tr><th>ID</th><th>Poli</th><th>No Antrian</th><th>Nama Pasien</th><th>Status</th><th>Waktu</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php while($a = $antrean->fetch_assoc()): ?>
            <tr>
                <td><?= $a['id'] ?></td>
                <td><?= htmlspecialchars($a['poli']) ?></td>
                <td><?= $a['nomor_antrian'] ?></td>
                <td><?= htmlspecialchars($a['nama_pasien']) ?></td>
                <td><?= $a['status'] ?></td>
                <td><?= $a['created_at'] ?></td>
                <td>
                    <form action="proses_admin.php" method="POST" onsubmit="return confirm('Hapus antrean ini?')">
                        <input type="hidden" name="id_antrean" value="<?= $a['id'] ?>">
                        <button type="submit" name="action" value="hapus_antrean" class="btn-delete"><i class="fas fa-trash"></i> Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- ========== MANAJEMEN AKUN USER ========== -->
    <h2>👥 Manajemen Akun User</h2>
    <table>
        <thead><tr><th>ID</th><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php while($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                <td><?= $u['role'] ?></td>
                <td><?= $u['created_at'] ?></td>
                <td>
                    <form action="proses_admin.php" method="POST" onsubmit="return confirm('Hapus user ini? Semua antrean dan chat-nya tetap ada.')">
                        <input type="hidden" name="id_user" value="<?= $u['id'] ?>">
                        <button type="submit" name="action" value="hapus_user" class="btn-delete"><i class="fas fa-user-minus"></i> Hapus User</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>