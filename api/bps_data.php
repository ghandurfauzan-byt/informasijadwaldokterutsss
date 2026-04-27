<?php
session_start();
include 'koneksi.php';

// Hanya admin yang boleh mengakses
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Ganti dengan API Key Anda yang asli dari BPS
$api_key = 'e795849c43cba25bedb1e2d710a7237b';

// Contoh endpoint untuk data indikator kesehatan (ganti sesuai kebutuhan)
// Endpoint ini contoh, Anda bisa ganti dengan endpoint lain dari dokumentasi BPS
$url = "https://webapi.bps.go.id/v1/api/list/model/data/lang/ind/domain/0000/var/1477/th/118/key/{$api_key}";

// Mengambil data menggunakan cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = null;
$error = null;

if ($httpCode == 200 && $response) {
    $data = json_decode($response, true);
    if (!isset($data['data']) || empty($data['data'])) {
        $error = "Data tidak ditemukan atau kosong.";
    }
} else {
    $error = "Gagal mengambil data dari API BPS. HTTP Code: $httpCode";
}

// Coba endpoint alternatif untuk daftar domain (wilayah)
$url_domain = "https://webapi.bps.go.id/v1/api/list/domain/key/{$api_key}";
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $url_domain);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
$response_domain = curl_exec($ch2);
curl_close($ch2);
$domains = json_decode($response_domain, true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data BPS - Klinik Sehat Umat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Roboto; }
        body { background:#f0f7ff; padding:20px; }
        .container { max-width:1300px; margin:auto; background:white; border-radius:30px; padding:30px; box-shadow:0 20px 40px rgba(0,0,0,0.05); }
        .btn-back { background:#7f8c8d; color:white; padding:8px 20px; border-radius:30px; text-decoration:none; display:inline-block; margin-bottom:20px; }
        h1 { color:#2c3e50; margin-bottom:10px; }
        .sub { color:#7f8c8d; margin-bottom:30px; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { font-size: 1rem; opacity: 0.9; margin-bottom: 10px; }
        .stat-card .value { font-size: 2.2rem; font-weight: bold; }
        .stat-card .unit { font-size: 0.8rem; opacity: 0.8; }
        table { width:100%; border-collapse:collapse; margin-top:20px; background:white; border-radius:20px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
        th { background:#34495e; color:white; padding:12px; text-align:left; }
        td { padding:10px; border-bottom:1px solid #ecf0f1; }
        .error-box { background:#f8d7da; color:#721c24; padding:15px; border-radius:15px; margin:20px 0; }
        .chart-container { max-width: 500px; margin: 30px auto; background: white; padding: 20px; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        canvas { max-height: 300px; }
    </style>
</head>
<body>
<div class="container">
    <a href="admin.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Admin Panel</a>
    <h1><i class="fas fa-chart-line"></i> Data Statistik dari BPS</h1>
    <p class="sub">Data indikator kesehatan dan kependudukan dari Badan Pusat Statistik</p>

    <?php if ($error): ?>
        <div class="error-box">
            <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
            <br><small>Coba periksa API Key atau koneksi internet Anda.</small>
        </div>
    <?php elseif ($data && isset($data['data']) && count($data['data']) > 0): ?>
        <!-- Tampilan ringkasan dalam bentuk kartu (ambil 4 data pertama sebagai contoh) -->
        <div class="stats-grid">
            <?php 
            $count = 0;
            foreach ($data['data'] as $item):
                if ($count >= 4) break;
                $label = isset($item['var_name']) ? $item['var_name'] : (isset($item['nama']) ? $item['nama'] : 'Indikator');
                $value = isset($item['nilai']) ? $item['nilai'] : (isset($item['value']) ? $item['value'] : '-');
                $unit = isset($item['unit']) ? $item['unit'] : '';
            ?>
            <div class="stat-card">
                <h3><i class="fas fa-chart-simple"></i> <?= htmlspecialchars($label) ?></h3>
                <div class="value"><?= htmlspecialchars($value) ?></div>
                <div class="unit"><?= htmlspecialchars($unit) ?></div>
            </div>
            <?php $count++; endforeach; ?>
        </div>

        <!-- Tabel data lengkap -->
        <h2><i class="fas fa-table"></i> Data Lengkap Indikator</h2>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Indikator</th>
                        <th>Satuan</th>
                        <th>Nilai</th>
                        <th>Tahun</th>
                        <th>Wilayah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['data'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['var_id'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($item['var_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($item['unit'] ?? '-') ?></td>
                        <td><strong><?= htmlspecialchars($item['nilai'] ?? '-') ?></strong></td>
                        <td><?= htmlspecialchars($item['th'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($item['domain'] ?? 'Nasional') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Grafik sederhana (ambil data numerik) -->
        <?php
        // Siapkan data untuk grafik (cari data yang bisa diplot)
        $chartLabels = [];
        $chartValues = [];
        foreach ($data['data'] as $item) {
            if (isset($item['nilai']) && is_numeric($item['nilai']) && strlen($chartLabels) < 8) {
                $chartLabels[] = isset($item['var_name']) ? substr($item['var_name'], 0, 20) : 'Data';
                $chartValues[] = (float)$item['nilai'];
            }
        }
        if (count($chartLabels) > 0):
        ?>
        <div class="chart-container">
            <h3 style="text-align:center; margin-bottom:15px;"><i class="fas fa-chart-bar"></i> Visualisasi Data</h3>
            <canvas id="bpsChart"></canvas>
        </div>
        <script>
            const ctx = document.getElementById('bpsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chartLabels) ?>,
                    datasets: [{
                        label: 'Nilai Indikator',
                        data: <?= json_encode($chartValues) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        </script>
        <?php endif; ?>

    <?php else: ?>
        <div class="error-box">
            <i class="fas fa-info-circle"></i> Data tidak tersedia atau format respons tidak sesuai.
            <br><small>Silakan periksa endpoint API BPS yang digunakan.</small>
        </div>
    <?php endif; ?>

    <!-- Tampilkan daftar domain/wilayah yang tersedia (opsional) -->
    <?php if (isset($domains['data']) && count($domains['data']) > 0): ?>
    <h2 style="margin-top: 40px;"><i class="fas fa-map-marker-alt"></i> Daftar Wilayah (Domain) Tersedia</h2>
    <div style="overflow-x: auto;">
        <table>
            <thead><tr><th>Kode Domain</th><th>Nama Wilayah</th></tr></thead>
            <tbody>
                <?php foreach ($domains['data'] as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['id'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['name'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
</body>
</html>