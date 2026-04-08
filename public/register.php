<?php
// ============================================================
// public/register.php — AUTH-01 (Đăng ký tài khoản)
// PHP THUẦN - MySQLi
// ============================================================
session_start();
require_once __DIR__ . '/../config/db.php';

// Nếu đã đăng nhập rồi → redirect về trang chủ
if (currentUser()) {
    header('Location: /barber-spa-website/public/index.php');
    exit;
}

$error = '';
$success = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    // Validate
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } elseif (strlen($name) < 3) {
        $error = 'Tên phải có ít nhất 3 ký tự.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không đúng định dạng.';
    } elseif (strlen($password) < 8) {
        $error = 'Mật khẩu phải có ít nhất 8 ký tự.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        // Kiểm tra email đã tồn tại
        $email_safe = escape($email);
        $existingUser = fetchOne("SELECT id FROM users WHERE email = '$email_safe' LIMIT 1");
        
        if ($existingUser) {
            $error = 'Email này đã được đăng ký. Vui lòng dùng email khác.';
        } else {
            // Tạo tài khoản mới
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $name_safe = escape($name);
            $hashedPassword_safe = escape($hashedPassword);
            
            execute("INSERT INTO users (name, email, password, role) VALUES ('$name_safe', '$email_safe', '$hashedPassword_safe', 'customer')");
            
            $success = 'Đăng ký thành công! Vui lòng <a href="/barber-spa-website/public/login.php" style="color: #e94560;">đăng nhập</a> để tiếp tục.';
            $name = '';
            $email = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký — Barber & Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/barber-spa-website/public/css/style.css">
    <style>
        body { background: #f8f9fa; }
        .auth-card {
            max-width: 420px; margin: 60px auto;
            background: #fff; border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            padding: 40px 36px;
        }
        .auth-logo { font-size: 1.6rem; font-weight: 700; color: #1a1a2e; margin-bottom: 8px; }
        .auth-logo span { color: #e94560; }
        .btn-primary { background: #e94560; border-color: #e94560; }
        .btn-primary:hover { background: #c73652; border-color: #c73652; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-logo text-center mb-4">✂ Barber<span>&Spa</span></div>
    <h5 class="mb-4 text-center">Tạo tài khoản mới</h5>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success py-2"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Họ và tên</label>
            <input type="text" name="name" class="form-control"
                   value="<?= htmlspecialchars($name) ?>"
                   placeholder="Nguyễn Văn A" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($email) ?>"
                   placeholder="example@gmail.com" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" class="form-control"
                   placeholder="••••••••" required minlength="8">
            <small class="text-muted">Tối thiểu 8 ký tự</small>
        </div>
        <div class="mb-4">
            <label class="form-label">Xác nhận mật khẩu</label>
            <input type="password" name="confirm_password" class="form-control"
                   placeholder="••••••••" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
    </form>

    <hr class="my-4">
    <p class="text-center mb-0 small">
        Đã có tài khoản? <a href="/barber-spa-website/public/login.php">Đăng nhập ngay</a>
    </p>
</div>
</body>
</html>
