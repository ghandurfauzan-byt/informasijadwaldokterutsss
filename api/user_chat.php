<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? $username;

// User hanya chat dengan admin (anggap admin username = 'admin')
$admin_username = 'admin';
$selected_user = $admin_username; // selalu admin

// Ambil pesan antara user dan admin
$stmt = $koneksi->prepare("SELECT * FROM chat_messages WHERE (username = ? AND is_admin = 1) OR (username = ? AND is_admin = 0) ORDER BY created_at ASC");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$messages = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Chat dengan Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        body { background: #e5ddd5; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
        .app-header {
            background: #075e54;
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .app-header a {
            color: white;
            text-decoration: none;
            background: #128c7e;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
        }
        .main-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }
        .chat-list {
            width: 320px;
            background: #f0f2f5;
            border-right: 1px solid #e0e0e0;
            overflow-y: auto;
        }
        .chat-list h3 {
            padding: 16px;
            background: #f0f2f5;
            font-size: 16px;
            border-bottom: 1px solid #e0e0e0;
        }
        .contact-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            gap: 12px;
            cursor: pointer;
            border-bottom: 1px solid #e8e8e8;
            background: #e9ecef; /* aktif */
        }
        .avatar {
            width: 45px;
            height: 45px;
            background: #25d366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
        .contact-info {
            flex: 1;
        }
        .contact-name {
            font-weight: 600;
            color: #111b21;
        }
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #efeae2;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.05"><path fill="none" d="M10 10h80v80H10z"/><circle cx="30" cy="30" r="4"/></svg>');
        }
        .chat-header-area {
            background: #f0f2f5;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .message-bubble {
            max-width: 65%;
            padding: 8px 12px;
            border-radius: 18px;
            position: relative;
            font-size: 13px;
            word-wrap: break-word;
        }
        .message-user {
            background: #dcf8c5;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .message-admin {
            background: white;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 0.5px rgba(0,0,0,0.05);
        }
        .message-time {
            font-size: 10px;
            opacity: 0.6;
            text-align: right;
            margin-top: 4px;
        }
        .chat-input-area {
            background: #f0f2f5;
            padding: 12px 20px;
            display: flex;
            gap: 12px;
            border-top: 1px solid #e0e0e0;
        }
        .chat-input-area input {
            flex: 1;
            padding: 12px 16px;
            border: none;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
        }
        .chat-input-area button {
            background: #25d366;
            border: none;
            width: 45px;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            font-size: 18px;
        }
        @media (max-width: 700px) {
            .chat-list { width: 250px; }
        }
    </style>
</head>
<body>
<div class="app-header">
    <div><i class="fab fa-whatsapp"></i> Chat dengan Admin</div>
    <a href="index.php"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>
<div class="main-container">
    <div class="chat-list">
        <h3><i class="fas fa-users"></i> Percakapan</h3>
        <div class="contact-item">
            <div class="avatar"><i class="fas fa-user-shield"></i></div>
            <div class="contact-info">
                <div class="contact-name">Admin Klinik</div>
                <div style="font-size:12px;">Online</div>
            </div>
        </div>
    </div>
    <div class="chat-area">
        <div class="chat-header-area">
            <div class="avatar" style="width:40px; height:40px;"><i class="fas fa-user-shield"></i></div>
            <div><strong>Admin Klinik</strong></div>
        </div>
        <div class="chat-messages" id="chatMessages">
            <?php while($msg = $messages->fetch_assoc()): ?>
                <?php $is_admin = ($msg['is_admin'] == 1); ?>
                <div class="message-bubble <?= $is_admin ? 'message-admin' : 'message-user' ?>">
                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                    <div class="message-time"><?= $msg['created_at'] ?></div>
                </div>
            <?php endwhile; ?>
        </div>
        <form class="chat-input-area" id="chatForm" method="POST" action="kirim_pesan.php">
            <input type="text" name="pesan" placeholder="Ketik pesan..." autocomplete="off" required>
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function scrollToBottom() {
        let msgs = document.getElementById('chatMessages');
        if(msgs) msgs.scrollTop = msgs.scrollHeight;
    }
    scrollToBottom();
    setInterval(function() {
        $.get('get_pesan_user.php', function(data) {
            if(data.trim() !== "") {
                $('#chatMessages').html(data);
                scrollToBottom();
            }
        });
    }, 3000);
    $('#chatForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        $.post(form.attr('action'), form.serialize(), function() {
            form.find('input[name="pesan"]').val('');
            $.get('get_pesan_user.php', function(data) {
                $('#chatMessages').html(data);
                scrollToBottom();
            });
        });
        return false;
    });
</script>
</body>
</html>