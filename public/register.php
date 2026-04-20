<?php
// ============================================================================
// public/register.php - Đăng ký (Standalone version)
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
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $passwordConfirmation = trim($_POST['password_confirmation'] ?? '');

    // Validate
    if (empty($name) || mb_strlen($name) < 2 || mb_strlen($name) > 150) {
        $error = 'Họ tên phải từ 2 đến 150 ký tự.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không đúng định dạng.';
    } elseif (strlen($password) < 8) {
        $error = 'Mật khẩu phải có ít nhất 8 ký tự.';
    } elseif ($password !== $passwordConfirmation) {
        $error = 'Xác nhận mật khẩu không khớp.';
    } else {
        $db = getDB();
        
        // Kiểm tra email đã tồn tại
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email đã được sử dụng.';
        } else {
            // Tạo tài khoản mới
            $emailToken = bin2hex(random_bytes(32));
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $db->prepare('INSERT INTO users (name, email, password, role, is_active, email_token, created_at) 
                                 VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$name, $email, $hashedPassword, 'customer', 1, $emailToken]);

            $_SESSION['success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
            header('Location: login.php');
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
    <title>Đăng ký - Barber Spa</title>
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
                    <h3 class="auth-side-title">Bắt đầu ngay hôm nay</h3>
                    <p class="auth-side-text">Tạo tài khoản để truy cập ưu đãi, quản lý profile và đặt dịch vụ barber chuyên nghiệp.</p>
                    <ul class="auth-features">
                        <li class="auth-feature-item">Đặt lịch nhanh chóng</li>
                        <li class="auth-feature-item">Lưu thông tin tức thì</li>
                        <li class="auth-feature-item">Theo dõi lịch sử booking</li>
                    </ul>
                </div>
                <div class="auth-card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" name="name" class="form-control auth-form-control" 
                                   value="<?= htmlspecialchars($name) ?>" placeholder="Nguyễn Văn A" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control auth-form-control" 
                                   value="<?= htmlspecialchars($email) ?>" placeholder="mail@domain.com" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Mật khẩu</label>
                                <input type="password" name="password" class="form-control auth-form-control" 
                                       placeholder="Tối thiểu 8 ký tự" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nhập lại mật khẩu</label>
                                <input type="password" name="password_confirmation" class="form-control auth-form-control" 
                                       placeholder="Nhập lại mật khẩu" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">Đăng ký</button>
                    </form>

                    <div class="auth-footer">
                        <a href="login.php">Đã có tài khoản? Đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>
