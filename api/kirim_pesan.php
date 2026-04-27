<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['username'])) exit;

$pesan = trim($_POST['pesan'] ?? '');
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$is_admin = ($role == 'admin') ? 1 : 0;

$to_user = $_POST['to_user'] ?? '';
if ($is_admin && $to_user != '') {
    // Admin mengirim ke user tertentu (via admin_chat)
    $stmt = $koneksi->prepare("INSERT INTO chat_messages (username, message, is_admin) VALUES (?, ?, 1)");
    $stmt->bind_param("ss", $to_user, $pesan);
    $stmt->execute();
} else {
    // User biasa atau admin dari chat box kecil
    $stmt = $koneksi->prepare("INSERT INTO chat_messages (username, message, is_admin) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $username, $pesan, $is_admin);
    $stmt->execute();
}
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header("Location: $redirect");
exit;
?>