<?php
// ============================================================
// public/logout.php  — AUTH-01
// Người làm: Nguyễn Văn Quang
// Branch: feature/auth
// ============================================================
session_start();
require_once __DIR__ . '/../config/db.php';

// Xóa remember_token trong DB nếu có
if (isset($_COOKIE['remember_token'])) {
    $db = getDB();
    $db->prepare("DELETE FROM remember_tokens WHERE token = ?")
       ->execute([$_COOKIE['remember_token']]);

    // Xóa cookie trên trình duyệt
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Xóa toàn bộ session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600,
        $p['path'], $p['domain'], $p['secure'], $p['httponly']
    );
}
session_destroy();

// Redirect về trang login
header('Location: /login.php?logout=1');
exit;
