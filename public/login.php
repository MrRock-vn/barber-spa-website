<?php
// ============================================================================
// public/login.php - Đăng nhập (Standalone version)
// Người làm: Nguyễn Văn Quang
// ============================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

// Nếu đã đăng nhập rồi → redirect về trang chủ
if (isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    // Validate cơ bản
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không đúng định dạng.';
    } else {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Email hoặc mật khẩu không đúng.';
        } elseif (!$user['is_active']) {
            $error = 'Tài khoản đã bị đình chỉ. Vui lòng liên hệ Admin.';
        } elseif ($user['locked_until'] && new DateTime() < new DateTime($user['locked_until'])) {
            $remaining = (new DateTime($user['locked_until']))->diff(new DateTime())->i;
            $error = "Tài khoản bị khóa tạm thời do nhập sai quá nhiều lần. Thử lại sau {$remaining} phút.";
        } elseif (!password_verify($password, $user['password'])) {
            // Tăng số lần nhập sai
            $attempts = $user['login_attempts'] + 1;
            $lockUntil = null;
            if ($attempts >= 5) {
                $lockUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                $attempts = 0;
            }
            $db->prepare('UPDATE users SET login_attempts=?, locked_until=? WHERE id=?')
                ->execute([$attempts, $lockUntil, $user['id']]);
            $error = 'Email hoặc mật khẩu không đúng.';
        } else {
            // ✅ Đăng nhập thành công
            $db->prepare('UPDATE users SET login_attempts=0, locked_until=NULL, last_login_at=NOW(), login_ip=? WHERE id=?')
                ->execute([$_SERVER['REMOTE_ADDR'], $user['id']]);

            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];

            // Remember Me: set cookie 7 ngày
            if ($remember) {
                $rememberToken = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
                
                $db->prepare('INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?) 
                             ON DUPLICATE KEY UPDATE token=?, expires_at=?')
                    ->execute([$user['id'], $rememberToken, $expiresAt, $rememberToken, $expiresAt]);

                setcookie('remember_token', $rememberToken, time() + (7 * 24 * 60 * 60), '/');
            }

            // Redirect theo role
            switch ($user['role']) {
                case 'admin':
                    header('Location: ../index.php?path=admin/dashboard');
                    break;
                case 'owner':
                    header('Location: ../index.php?path=owner/dashboard');
                    break;
                default:
                    header('Location: ../index.php');
                    break;
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Barber Spa</title>
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
                    <h3 class="auth-side-title">Chào mừng trở lại</h3>
                    <p class="auth-side-text">Đăng nhập để quản lý lịch hẹn nhanh gọn, ưu đãi trực tiếp và thông tin cá nhân an toàn.</p>
                    <ul class="auth-features">
                        <li class="auth-feature-item">Quản lý lịch hẹn mọi lúc</li>
                        <li class="auth-feature-item">Lưu thông tin cá nhân</li>
                        <li class="auth-feature-item">Thanh toán nhanh và bảo mật</li>
                    </ul>
                </div>
                <div class="auth-card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control auth-form-control" 
                                   value="<?= htmlspecialchars($email) ?>" placeholder="mail@domain.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="password" class="form-control auth-form-control" 
                                   placeholder="Nhập mật khẩu" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">Đăng nhập</button>
                    </form>

                    <div class="auth-footer">
                        <a href="forgot-password.php">Quên mật khẩu?</a>
                        <a href="register.php">Chưa có tài khoản? Đăng ký</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>
