<?php
// ============================================================
// public/forgot-password.php — Quên mật khẩu (AUTH-01)
// ============================================================
session_start();
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';
$step = (int)($_GET['step'] ?? 1);
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        // Bước 1: Nhập email
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $error = 'Vui lòng nhập email.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email không đúng định dạng.';
        } else {
            $email_safe = escape($email);
            $user = fetchOne("SELECT id FROM users WHERE email = '$email_safe' LIMIT 1");
            if (!$user) {
                $error = 'Email này không tồn tại trong hệ thống.';
            } else {
                // TODO: Gửi email reset link
                $success = 'Hướng dẫn đặt lại mật khẩu đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.';
            }
        }
    } elseif ($step === 2) {
        // Bước 2: Đặt lại mật khẩu
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if (empty($password) || empty($confirmPassword)) {
            $error = 'Vui lòng điền đầy đủ mật khẩu.';
        } elseif (strlen($password) < 8) {
            $error = 'Mật khẩu phải có ít nhất 8 ký tự.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Mật khẩu xác nhận không khớp.';
        } else {
            // TODO: Xác minh token và cập nhật mật khẩu
            $success = 'Mật khẩu đã được đặt lại thành công. <a href="/barber-spa-website/public/login.php" style="color: var(--brand);">Đăng nhập ngay</a>';
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
    <link rel="stylesheet" href="/barber-spa-website/public/css/style.css">
    <style>
        body { background: var(--dark); }
        .auth-card {
            max-width: 420px; margin: 60px auto;
            background: var(--dark2); border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.3);
            padding: 40px 36px;
            border: 1px solid var(--border);
        }
        .auth-logo { font-size: 1.6rem; font-weight: 700; color: var(--text); margin-bottom: 8px; }
        .auth-logo span { color: var(--brand); }
        h5 { color: var(--text); }
        .form-control { background: var(--dark3); border: 1px solid var(--border); color: var(--text); }
        .form-control:focus { background: var(--dark3); border-color: var(--brand); box-shadow: 0 0 0 3px rgba(233,69,96,.1); }
        .form-label { color: var(--text); }
        .btn-primary { background: var(--brand); border-color: var(--brand); }
        .btn-primary:hover { background: #c73652; border-color: #c73652; }
        .text-muted { color: var(--text-muted) !important; }
        a { color: var(--brand); text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-logo text-center mb-4">✂ Barber<span>&Spa</span></div>
    <h5 class="mb-4 text-center">Quên mật khẩu</h5>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success py-2"><?= $success ?></div>
    <?php else: ?>

    <?php if ($step === 1): ?>
    <!-- BƯỚC 1: NHẬP EMAIL -->
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   placeholder="example@gmail.com" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary w-100">Gửi hướng dẫn</button>
    </form>

    <?php elseif ($step === 2): ?>
    <!-- BƯỚC 2: ĐẶT LẠI MẬT KHẨU -->
    <form method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="mb-3">
            <label class="form-label">Mật khẩu mới</label>
            <input type="password" name="password" class="form-control"
                   placeholder="••••••••" required minlength="8" autofocus>
            <small class="text-muted">Tối thiểu 8 ký tự</small>
        </div>
        <div class="mb-4">
            <label class="form-label">Xác nhận mật khẩu</label>
            <input type="password" name="confirm_password" class="form-control"
                   placeholder="••••••••" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary w-100">Đặt lại mật khẩu</button>
    </form>

    <?php endif; ?>

    <?php endif; ?>

    <hr class="my-4">
    <p class="text-center mb-0 small">
        Nhớ mật khẩu? <a href="/barber-spa-website/public/login.php">Đăng nhập ngay</a>
    </p>
</div>
</body>
</html>
