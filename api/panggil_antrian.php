<?php
session_start();
include 'koneksi.php';
if ($_SESSION['role'] != 'admin') { 
    header("Location: index.php"); 
    exit(); 
}

$poli = $_GET['poli'] ?? '';
if ($poli) {
    $stmt = $koneksi->prepare("UPDATE antrian SET status = 'dipanggil' WHERE poli = ? AND status = 'menunggu' ORDER BY nomor_antrian ASC LIMIT 1");
    $stmt->bind_param("s", $poli);
    $stmt->execute();
}
header("Location: index.php?poli=".urlencode($poli));
?>