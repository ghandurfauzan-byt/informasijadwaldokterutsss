<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $poli = $_POST['poli'];
    $nama = trim($_POST['nama']);
    
    if (empty($nama)) {
        header("Location: index.php?poli=".urlencode($poli)."&error=nama_kosong");
        exit();
    }
    
    $today = date('Y-m-d');
    $stmt = $koneksi->prepare("SELECT MAX(nomor_antrian) as last FROM antrian WHERE poli = ? AND DATE(created_at) = ?");
    $stmt->bind_param("ss", $poli, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $next_number = ($row['last'] ?? 0) + 1;
    
    $stmt_insert = $koneksi->prepare("INSERT INTO antrian (poli, nomor_antrian, nama_pasien, status) VALUES (?, ?, ?, 'menunggu')");
    $stmt_insert->bind_param("sis", $poli, $next_number, $nama);
    
    if ($stmt_insert->execute()) {
        header("Location: index.php?poli=".urlencode($poli)."&success=antrian_ditambahkan");
    } else {
        header("Location: index.php?poli=".urlencode($poli)."&error=gagal");
    }
    exit();
}
?>