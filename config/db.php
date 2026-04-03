<?php
// config/db.php — KẾT NỐI DATABASE
// File này được INCLUDE vào mọi trang PHP khác

define('DB_HOST', 'localhost');
define('DB_NAME', 'barber_spa');
define('DB_USER', 'root');       // ← đổi theo máy của bạn
define('DB_PASS', '');           // ← đổi theo máy của bạn
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('<div style="padding:20px;background:#fee;color:#c00;font-family:monospace">
                <b>Lỗi kết nối Database:</b><br>' . $e->getMessage() . '<br><br>
                <small>Kiểm tra lại DB_HOST, DB_USER, DB_PASS trong config/db.php</small>
            </div>');
        }
    }
    return $pdo;
}

// Helper: lấy user đang đăng nhập
function currentUser(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['user'] ?? null;
}

// Helper: yêu cầu đăng nhập
function requireLogin(): void {
    if (!currentUser()) {
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}
