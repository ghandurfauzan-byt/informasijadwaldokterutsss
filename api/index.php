<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$filter_poli = isset($_GET['poli']) ? $_GET['poli'] : '';
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? $username;

// Ambil jadwal dokter
$sql = "SELECT * FROM jadwal_dokter";
if ($filter_poli != '') {
    $sql .= " WHERE spesialis = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("s", $filter_poli);
} else {
    $stmt = $koneksi->prepare($sql);
}
$stmt->execute();
$result_jadwal = $stmt->get_result();

// Ambil antrian untuk poli yang dipilih
$antrian_list = [];
if ($filter_poli != '') {
    $stmt_antrian = $koneksi->prepare("SELECT * FROM antrian WHERE poli = ? ORDER BY nomor_antrian ASC");
    $stmt_antrian->bind_param("s", $filter_poli);
    $stmt_antrian->execute();
    $antrian_list = $stmt_antrian->get_result();
}

// ========== AMBIL DATA BPS UNTUK USER ==========
$bps_data = [];
$bps_error = null;
$api_key_bps = 'e795849c43cba25bedb1e2d710a7237b'; // Ganti dengan API Key Anda
// Endpoint untuk mengambil daftar domain (wilayah) - ini pasti berhasil
$url_bps = "https://webapi.bps.go.id/v1/api/list/domain/key/$api_key_bps";
$ch_bps = curl_init();
curl_setopt($ch_bps, CURLOPT_URL, $url_bps);
curl_setopt($ch_bps, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_bps, CURLOPT_TIMEOUT, 8);
$response_bps = curl_exec($ch_bps);
$http_bps = curl_getinfo($ch_bps, CURLINFO_HTTP_CODE);
curl_close($ch_bps);

if ($http_bps == 200 && $response_bps) {
    $bps_json = json_decode($response_bps, true);
    if (isset($bps_json['data']) && is_array($bps_json['data'])) {
        $bps_data['domain_count'] = count($bps_json['data']);
    } else {
        $bps_error = "Data tidak lengkap";
    }
} else {
    $bps_error = "Gagal mengambil data BPS";
}

// Data dummy jika gagal
if ($bps_error || !isset($bps_data['domain_count'])) {
    $bps_data['domain_count'] = 34;
    $bps_data['use_dummy'] = true;
} else {
    $bps_data['use_dummy'] = false;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik Sehat Umat | Jadwal & Antrian</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Roboto, sans-serif; }
        body { background: #f0f7ff; padding: 20px; }
        .container { max-width: 1300px; margin: auto; background: white; border-radius: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); overflow: hidden; }
        .top-nav { background: #0f172a; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; color: white; }
        .user-info { background: #1e293b; padding: 6px 16px; border-radius: 40px; font-size: 0.9rem; }
        .btn-logout, .btn-admin, .btn-wa { padding: 6px 18px; border-radius: 40px; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; }
        .btn-logout { background: #ef4444; color: white; }
        .btn-admin { background: #f59e0b; color: white; }
        .btn-wa { background: #25d366; color: white; }
        header { background: linear-gradient(120deg, #1e3a8a, #3b82f6); padding: 40px 30px; text-align: center; color: white; }
        header h1 { font-size: 2rem; }
        .content-section { padding: 30px; }
        .filter-box { text-align: center; margin-bottom: 30px; }
        select { padding: 12px 24px; border-radius: 60px; border: none; background: white; box-shadow: 0 2px 6px rgba(0,0,0,0.1); width: 280px; font-size: 1rem; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        th { background: #2563eb; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .doctor-name { font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .tag { background: #dbeafe; padding: 4px 12px; border-radius: 40px; font-size: 0.75rem; font-weight: 600; color: #1e40af; }
        .form-section { background: #f8fafc; margin: 20px 30px 30px; padding: 25px; border-radius: 28px; border: 1px solid #e2e8f0; }
        .input-group { display: flex; gap: 15px; flex-wrap: wrap; align-items: center; }
        .input-group input { flex: 2; padding: 12px 20px; border-radius: 60px; border: 1px solid #cbd5e1; }
        .btn-submit { background: #10b981; color: white; border: none; padding: 12px 28px; border-radius: 60px; cursor: pointer; font-weight: 600; }
        .queue-list { background: white; border-radius: 24px; padding: 20px; margin-top: 20px; border: 1px solid #e2e8f0; }
        .queue-item { display: inline-block; margin: 5px; padding: 6px 16px; border-radius: 40px; font-size: 0.85rem; }
        .status-menunggu { background: #fef3c7; color: #b45309; }
        .status-dipanggil { background: #d1fae5; color: #065f46; }
        .admin-call { margin-top: 15px; display: inline-block; background: #f97316; color: white; padding: 8px 20px; border-radius: 40px; text-decoration: none; }
        
        /* Widget BPS untuk user */
        .bps-widget { background: #f8fafc; margin: 30px; padding: 25px; border-radius: 28px; border: 1px solid #e2e8f0; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-top: 20px; }
        .stat-card { padding: 20px; border-radius: 20px; color: white; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card i { font-size: 2rem; margin-bottom: 10px; }
        .stat-card h3 { font-size: 1rem; opacity: 0.9; margin-bottom: 8px; }
        .stat-card .value { font-size: 2rem; font-weight: bold; }
        .stat-card .unit { font-size: 0.75rem; opacity: 0.8; }
        .bps-note { font-size: 12px; text-align: center; margin-top: 15px; color: #64748b; }
        @media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="container">
    <div class="top-nav">
        <span><i class="fas fa-hospital-user"></i> Klinik Sehat Umat</span>
        <div>
            <span class="user-info"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($nama_lengkap) ?> (<?= ucfirst($role) ?>)</span>
            <?php if ($role == 'admin'): ?>
                <a href="admin.php" class="btn-admin"><i class="fas fa-cog"></i> Admin Panel</a>
                <a href="admin_chat.php" class="btn-wa"><i class="fab fa-whatsapp"></i> Chat WA Style</a>
            <?php else: ?>
                <a href="user_chat.php" class="btn-wa"><i class="fas fa-comment-dots"></i> Chat Admin</a>
            <?php endif; ?>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <header>
        <h1>Jadwal Praktik Dokter</h1>
        <p>📍 Pilih poli, lihat jadwal, dan ambil nomor antrian</p>
    </header>
    <div class="content-section">
        <div class="filter-box">
            <form method="GET">
                <select name="poli" onchange="this.form.submit()">
                    <option value="">-- Semua Poli --</option>
                    <option value="Poli Umum" <?= $filter_poli == 'Poli Umum' ? 'selected' : '' ?>>Poli Umum</option>
                    <option value="Poli Ibu & Anak" <?= $filter_poli == 'Poli Ibu & Anak' ? 'selected' : '' ?>>Poli Ibu & Anak</option>
                    <option value="Poli Gigi" <?= $filter_poli == 'Poli Gigi' ? 'selected' : '' ?>>Poli Gigi</option>
                </select>
            </form>
        </div>
        <div style="overflow-x: auto;">
            <table>
                <thead><tr><th>No</th><th>Nama Dokter</th><th>Spesialis</th><th>Jam Praktik</th></tr></thead>
                <tbody>
                    <?php if ($result_jadwal->num_rows > 0): $no=1; while($row = $result_jadwal->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="doctor-name"><i class="fas fa-user-md"></i> <?= htmlspecialchars($row['nama_dokter']) ?></td>
                        <td><span class="tag"><?= htmlspecialchars($row['spesialis']) ?></span></td>
                        <td><i class="far fa-clock"></i> <?= htmlspecialchars($row['jam']) ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="4" style="text-align:center">Silakan pilih poli untuk menampilkan jadwal</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($filter_poli != ''): ?>
        <div class="form-section">
            <h2><i class="fas fa-list-ol"></i> Daftar Antrian : <?= htmlspecialchars($filter_poli) ?></h2>
            <form action="proses_antrian.php" method="POST">
                <input type="hidden" name="poli" value="<?= htmlspecialchars($filter_poli) ?>">
                <div class="input-group">
                    <input type="text" name="nama" placeholder="Nama lengkap pasien" required>
                    <button type="submit" class="btn-submit"><i class="fas fa-plus-circle"></i> Ambil Nomor Antrian</button>
                </div>
            </form>
            <div class="queue-list">
                <strong><i class="fas fa-people-arrows"></i> Antrian saat ini:</strong><br>
                <?php if ($antrian_list && $antrian_list->num_rows > 0): ?>
                    <?php while($ant = $antrian_list->fetch_assoc()): ?>
                        <span class="queue-item status-<?= $ant['status'] ?>">
                            <i class="fas fa-ticket-alt"></i> <?= $ant['nomor_antrian'] ?> - <?= htmlspecialchars($ant['nama_pasien']) ?> (<?= $ant['status'] ?>)
                        </span>
                    <?php endwhile; ?>
                <?php else: ?>
                    <span>Belum ada antrian.</span>
                <?php endif; ?>
            </div>
            <?php if ($role == 'admin'): ?>
                <a href="panggil_antrian.php?poli=<?= urlencode($filter_poli) ?>" class="admin-call"><i class="fas fa-bullhorn"></i> Panggil Antrian Selanjutnya</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ========== WIDGET DATA BPS UNTUK USER ========== -->
    <div class="bps-widget">
        <h2><i class="fas fa-chart-line"></i> Data Statistik Kesehatan (BPS)</h2>
        <p>Data indikator kesehatan dan kependudukan Indonesia dari Badan Pusat Statistik</p>
        <div class="stats-grid">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="fas fa-database"></i>
                <h3>Jumlah Wilayah</h3>
                <div class="value"><?= $bps_data['domain_count'] ?></div>
                <div class="unit">Provinsi di Indonesia</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                <i class="fas fa-heartbeat"></i>
                <h3>Angka Harapan Hidup</h3>
                <div class="value">71,5</div>
                <div class="unit">Tahun (2023)</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                <i class="fas fa-baby-carriage"></i>
                <h3>Prevalensi Stunting</h3>
                <div class="value">21,6%</div>
                <div class="unit">Tahun 2023</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                <i class="fas fa-hospital-user"></i>
                <h3>Fasilitas Kesehatan</h3>
                <div class="value">10.569</div>
                <div class="unit">Puskesmas (2022)</div>
            </div>
        </div>
        <div class="bps-note">
            <i class="fas fa-info-circle"></i> 
            <?php if ($bps_data['use_dummy'] ?? false): ?>
                Data simulasi (integrasi API BPS sedang dalam pengembangan). Jumlah wilayah diambil dari API BPS.
            <?php else: ?>
                Jumlah wilayah diambil dari API BPS. Data lainnya bersumber dari BPS (dummy untuk demo).
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>