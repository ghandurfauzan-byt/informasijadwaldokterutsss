<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: landing.php");
    exit();
}
$log_message = date('Y-m-d H:i:s') . " - User " . $_SESSION['username'] . " (" . $_SESSION['role'] . ") telah logout.\n";
file_put_contents('logs/logout.log', $log_message, FILE_APPEND);
session_destroy();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
header("Location: landing.php");
exit();
?>