<?php
// ============================================================================
// public/logout.php - Đăng xuất (Standalone version)
// Người làm: Nguyễn Văn Quang
// ============================================================================

session_start();

// Xóa remember token nếu có
if (isset($_SESSION['user']['id']) && isset($_COOKIE['remember_token'])) {
    require_once __DIR__ . '/../config/db.php';
    $db = getDB();
    $db->prepare('DELETE FROM remember_tokens WHERE user_id = ?')
        ->execute([$_SESSION['user']['id']]);
}

// Xóa cookie
setcookie('remember_token', '', time() - 3600, '/');

// Destroy session
session_unset();
session_destroy();

// Redirect về trang login
header('Location: login.php');
exit;
