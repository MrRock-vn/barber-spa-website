<?php
// ============================================================
// public/forgot-password.php  — AUTH-01
// Người làm: Nguyễn Văn Quang
// Branch: feature/auth
// ============================================================
session_start();
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Vui lòng nhập email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không đúng định dạng.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Luôn hiện thông báo thành công dù email có tồn tại hay không
        // (tránh lộ thông tin người dùng)
        if ($user) {
            // Xóa token cũ (nếu có)
            $db->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);

            // Tạo token mới
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $db->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)')
               ->execute([$email, $token, $expires]);

            // Link đặt lại (hiện ra màn hình thay vì gửi email — OK cho demo)
            $resetLink = 'http://localhost/barber-spa/public/reset-password.php?token=' . $token;
            $success   = $resetLink; // truyền xuống HTML để hiển thị
        } else {
            // Email không tồn tại nhưng vẫn hiện thông báo giống nhau
            $success = 'NOT_FOUND';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu — Barber & Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .auth-card {
            max-width: 440px; margin: 80px auto;
            background: #fff; border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            padding: 40px 36px;
        }
        .auth-logo { font-size: 1.6rem; font-weight: 700; color: #1a1a2e; }
        .auth-logo span { color: #e94560; }
        .btn-primary { background: #e94560; border-color: #e94560; }
        .btn-primary:hover { background: #c73652; border-color: #c73652; }
        .link-box {
            background: #f0fff4; border: 1px solid #b2f5c8;
            border-radius: 8px; padding: 12px 16px;
            word-break: break-all; font-size: .85rem;
        }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-logo text-center mb-4">✂ Barber<span>&Spa</span></div>
    <h5 class="mb-2 text-center">Quên mật khẩu</h5>
    <p class="text-muted text-center small mb-4">
        Nhập email đăng ký, chúng tôi sẽ gửi link đặt lại mật khẩu.
    </p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>

    <?php elseif ($success && $success !== 'NOT_FOUND'): ?>
        <!-- Thành công: hiện link (chế độ demo) -->
        <div class="alert alert-success py-2">
            <strong>Yêu cầu đã được ghi nhận!</strong><br>
            <span class="small">Link đặt lại mật khẩu (demo — thay bằng email thật khi deploy):</span>
        </div>
        <div class="link-box mb-3">
            <a href="<?= htmlspecialchars($success) ?>"><?= htmlspecialchars($success) ?></a>
        </div>
        <p class="small text-muted">⏰ Link có hiệu lực trong <strong>1 giờ</strong>.</p>
        <a href="/login.php" class="btn btn-outline-secondary w-100">← Quay lại đăng nhập</a>

    <?php elseif ($success === 'NOT_FOUND'): ?>
        <!-- Email không tồn tại — vẫn hiện giống thành công -->
        <div class="alert alert-success py-2">
            Nếu email tồn tại trong hệ thống, bạn sẽ nhận được link đặt lại mật khẩu.
        </div>
        <a href="/login.php" class="btn btn-outline-secondary w-100">← Quay lại đăng nhập</a>

    <?php else: ?>
        <!-- Form nhập email -->
        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Địa chỉ email</label>
                <input type="email" name="email" class="form-control"
                       placeholder="example@gmail.com" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100">Gửi link đặt lại</button>
        </form>
        <hr class="my-4">
        <p class="text-center mb-0 small">
            <a href="/login.php">← Quay lại đăng nhập</a>
        </p>
    <?php endif; ?>
</div>
</body>
</html>
