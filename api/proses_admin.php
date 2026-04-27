<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

$action = $_POST['action'] ?? '';

if ($action == 'tambah_jadwal') {
    $nama = $_POST['nama_dokter'];
    $spesialis = $_POST['spesialis'];
    $jam = $_POST['jam'];
    $stmt = $koneksi->prepare("INSERT INTO jadwal_dokter (nama_dokter, spesialis, jam) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama, $spesialis, $jam);
    $stmt->execute();
    header("Location: admin.php?success=Jadwal ditambahkan");
}
elseif ($action == 'edit_jadwal') {
    $id = $_POST['id'];
    $nama = $_POST['nama_dokter'];
    $spesialis = $_POST['spesialis'];
    $jam = $_POST['jam'];
    $stmt = $koneksi->prepare("UPDATE jadwal_dokter SET nama_dokter=?, spesialis=?, jam=? WHERE id=?");
    $stmt->bind_param("sssi", $nama, $spesialis, $jam, $id);
    $stmt->execute();
    header("Location: admin.php?success=Jadwal diupdate");
}
elseif ($action == 'hapus_jadwal') {
    $id = $_POST['id'];
    $stmt = $koneksi->prepare("DELETE FROM jadwal_dokter WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin.php?success=Jadwal dihapus");
}
elseif ($action == 'reset_semua_antrean') {
    $koneksi->query("DELETE FROM antrian");
    header("Location: admin.php?success=Semua antrean direset");
}
elseif ($action == 'reset_per_poli') {
    $poli = $_POST['poli_reset'];
    $stmt = $koneksi->prepare("DELETE FROM antrian WHERE poli = ?");
    $stmt->bind_param("s", $poli);
    $stmt->execute();
    header("Location: admin.php?success=Antrean untuk $poli direset");
}
elseif ($action == 'hapus_antrean') {
    $id = $_POST['id_antrean'];
    $stmt = $koneksi->prepare("DELETE FROM antrian WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin.php?success=Antrean dihapus");
}
elseif ($action == 'hapus_user') {
    $id_user = $_POST['id_user'];
    $stmt = $koneksi->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    header("Location: admin.php?success=User berhasil dihapus");
}
else {
    header("Location: admin.php?error=Aksi tidak dikenal");
}
exit();
?>