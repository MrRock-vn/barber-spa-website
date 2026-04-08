<?php
// config/db.php — KẾT NỐI DATABASE (PHP THUẦN - MySQLi)
// File này được INCLUDE vào mọi trang PHP khác

define('DB_HOST', 'localhost');
define('DB_NAME', 'barber_spa');
define('DB_USER', 'root');       // ← đổi theo máy của bạn
define('DB_PASS', '');           // ← đổi theo máy của bạn
define('DB_CHARSET', 'utf8mb4');

// Kết nối database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die('<div style="padding:20px;background:#fee;color:#c00;font-family:monospace">
        <b>Lỗi kết nối Database:</b><br>' . $conn->connect_error . '<br><br>
        <small>Kiểm tra lại DB_HOST, DB_USER, DB_PASS trong config/db.php</small>
    </div>');
}

// Set charset
$conn->set_charset(DB_CHARSET);

// Helper: lấy user đang đăng nhập
function currentUser(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['user'] ?? null;
}

// Helper: yêu cầu đăng nhập
function requireLogin(): void {
    if (!currentUser()) {
        header('Location: /barber-spa-website/public/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// Helper: escape string
function escape($str) {
    global $conn;
    return $conn->real_escape_string($str);
}

// Helper: query
function query($sql) {
    global $conn;
    $result = $conn->query($sql);
    if (!$result) {
        die('Lỗi query: ' . $conn->error);
    }
    return $result;
}

// Helper: fetch all
function fetchAll($sql) {
    $result = query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Helper: fetch one
function fetchOne($sql) {
    $result = query($sql);
    return $result->fetch_assoc();
}

// Helper: execute (INSERT, UPDATE, DELETE)
function execute($sql) {
    global $conn;
    $result = $conn->query($sql);
    if (!$result) {
        die('Lỗi execute: ' . $conn->error);
    }
    return $result;
}

// Helper: get last insert id
function lastInsertId() {
    global $conn;
    return $conn->insert_id;
}
