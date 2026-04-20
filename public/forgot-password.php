<?php
// ============================================================================
// public/forgot-password.php - Quên mật khẩu (Standalone version)
// Người làm: Nguyễn Văn Quang
// ============================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không đúng định dạng.';
    } else {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Tạo reset token
            $resetToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $stmt = $db->prepare('UPDATE users SET reset_token=?, reset_token_expires=? WHERE id=?');
            $stmt->execute([$resetToken, $expiresAt, $user['id']]);

            $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/reset-password.php?token=' . urlencode($resetToken);

            // Trong thực tế, bạn sẽ gửi email ở đây
            // Tạm thời hiển thị link
            $success = 'Link đặt lại mật khẩu đã được tạo. (Trong thực tế sẽ gửi qua email)<br><a href="' . htmlspecialchars($resetLink) . '">Click vào đây để đặt lại mật khẩu</a>';
        } else {
            // Vẫn hiển thị success để tránh lộ thông tin user
            $success = 'Nếu email tồn tại trong hệ thống, liên kết đặt lại mật khẩu đã được gửi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Barber Spa</title>
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
                    <h3 class="auth-side-title">Không nhớ mật khẩu?</h3>
                    <p class="auth-side-text">Chỉ cần nhập email và chúng tôi sẽ gửi liên kết khôi phục password đến bạn.</p>
                    <ul class="auth-features">
                        <li class="auth-feature-item">Bảo mật tài khoản tối ưu</li>
                        <li class="auth-feature-item">Khôi phục nhanh chóng</li>
                    </ul>
                </div>
                <div class="auth-card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control auth-form-control" 
                                   value="<?= htmlspecialchars($email) ?>" placeholder="mail@domain.com" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">Gửi yêu cầu</button>
                    </form>

                    <div class="auth-footer">
                        <a href="login.php">Quay lại đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>
