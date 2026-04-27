<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['username'])) exit;

$username = $_SESSION['username'];
$stmt = $koneksi->prepare("SELECT * FROM chat_messages WHERE (username = ? AND is_admin = 1) OR (username = ? AND is_admin = 0) ORDER BY created_at ASC");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();
while($msg = $result->fetch_assoc()) {
    $is_admin = ($msg['is_admin'] == 1);
    $class = $is_admin ? 'message-admin' : 'message-user';
    echo '<div class="message-bubble '.$class.'">';
    echo nl2br(htmlspecialchars($msg['message']));
    echo '<div class="message-time">'.$msg['created_at'].'</div>';
    echo '</div>';
}
?>