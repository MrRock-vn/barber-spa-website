<?php
// ============================================================================
// public/reset-password.php - Đặt lại mật khẩu (Standalone version)
// Người làm: Nguyễn Văn Quang
// ============================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';

if (empty($token)) {
    header('Location: forgot-password.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare('SELECT * FROM users WHERE reset_token = ? LIMIT 1');
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    $error = 'Token đặt lại mật khẩu không hợp lệ.';
} elseif (strtotime($user['reset_token_expires']) < time()) {
    $error = 'Token đặt lại mật khẩu đã hết hạn.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password = trim($_POST['password'] ?? '');
    $passwordConfirmation = trim($_POST['password_confirmation'] ?? '');

    if (strlen($password) < 8) {
        $error = 'Mật khẩu phải có ít nhất 8 ký tự.';
    } elseif ($password !== $passwordConfirmation) {
        $error = 'Xác nhận mật khẩu không khớp.';
    } else {
        // Cập nhật mật khẩu mới
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare('UPDATE users SET password=?, reset_token=NULL, reset_token_expires=NULL WHERE id=?');
        $stmt->execute([$hashedPassword, $user['id']]);

        $_SESSION['success'] = 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập.';
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Barber Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-page { min-height: 100vh; display: flex; align-items: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .auth-panel { width: 100%; max-width: 900px; margin: 0 auto; }
        .auth-card { display: flex; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .auth-card-side { flex: 1; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 50px 40px; }
        .auth-brand { font-size: 28px; font-weight: bold; margin-bottom: 30px; }
        .auth-side-title { font-size: 24px; font-weight: 600; margin-bottom: 15px; }
        .auth-side-text { opacity: 0.9; margin-bottom: 30px; }
        .auth-features { list-style: none; padding: 0; }
        .auth-feature-item { padding: 10px 0; padding-left: 30px; position: relative; }
        .auth-feature-item:before { content: "✓"; position: absolute; left: 0; font-weight: bold; }
        .auth-card-body { flex: 1; padding: 50px 40px; }
        .auth-form-control { padding: 12px 15px; border-radius: 8px; border: 1px solid #ddd; }
        .auth-form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
        .auth-footer { margin-top: 20px; text-align: center; }
        .auth-footer a { display: block; margin: 10px 0; color: #667eea; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
        .alert { border-radius: 8px; }
    </style>
</head>
<body>
<section class="auth-page">
    <div class="container py-5">
        <div class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-side">
                    <div class="auth-brand">BARBER SPA</div>
                    <h3 class="auth-side-title">Đặt lại mật khẩu</h3>
                    <p class="auth-side-text">Thiết lập mật khẩu mới để bạn tiếp tục đặt lịch và sử dụng dịch vụ an toàn.</p>
                    <ul class="auth-features">
                        <li class="auth-feature-item">Mật khẩu bảo mật cao</li>
                        <li class="auth-feature-item">Tiếp tục đặt lịch dễ dàng</li>
                    </ul>
                </div>
                <div class="auth-card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <div class="auth-footer">
                            <a href="forgot-password.php">Yêu cầu link mới</a>
                            <a href="login.php">Quay lại đăng nhập</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                            <div class="mb-3">
                                <label class="form-label">Mật khẩu mới</label>
                                <input type="password" name="password" class="form-control auth-form-control" 
                                       placeholder="Tối thiểu 8 ký tự" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" name="password_confirmation" class="form-control auth-form-control" 
                                       placeholder="Nhập lại mật khẩu" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-lg">Cập nhật mật khẩu</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>
