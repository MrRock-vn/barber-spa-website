<?php
// ============================================================================
// public/change-password.php - Đổi mật khẩu (Standalone version)
// Người làm: Nguyễn Văn Quang
// ============================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    // Validate
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Xác nhận mật khẩu mới không khớp.';
    } else {
        // Kiểm tra mật khẩu hiện tại
        $db = getDB();
        $stmt = $db->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$user['id']]);
        $userRecord = $stmt->fetch();

        if (!$userRecord || !password_verify($currentPassword, $userRecord['password'])) {
            $error = 'Mật khẩu hiện tại không đúng.';
        } else {
            // Cập nhật mật khẩu mới
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $db->prepare('UPDATE users SET password=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([$hashedPassword, $user['id']]);

            $success = 'Đổi mật khẩu thành công!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - Barber Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .change-password-page { min-height: 100vh; display: flex; align-items: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .change-password-card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 500px; width: 100%; }
        .change-password-header { text-align: center; margin-bottom: 30px; }
        .change-password-title { color: #333; font-weight: 600; margin-bottom: 10px; }
        .change-password-subtitle { color: #666; font-size: 14px; }
        .form-control { padding: 12px 15px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 15px; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px 30px; border-radius: 8px; width: 100%; }
        .btn-outline-secondary { border-radius: 8px; padding: 12px 30px; }
        .password-requirements { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .password-requirements ul { margin: 0; padding-left: 20px; }
        .password-requirements li { margin-bottom: 5px; }
    </style>
</head>
<body>
<div class="change-password-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="change-password-card">
                    <div class="change-password-header">
                        <h3 class="change-password-title">Đổi mật khẩu</h3>
                        <p class="change-password-subtitle">Cập nhật mật khẩu để bảo mật tài khoản</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <div class="password-requirements">
                        <strong>Yêu cầu mật khẩu:</strong>
                        <ul>
                            <li>Ít nhất 8 ký tự</li>
                            <li>Nên có chữ hoa, chữ thường</li>
                            <li>Nên có số và ký tự đặc biệt</li>
                            <li>Không sử dụng thông tin cá nhân</li>
                        </ul>
                    </div>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" name="current_password" class="form-control" 
                                   placeholder="Nhập mật khẩu hiện tại" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control" 
                                   placeholder="Nhập mật khẩu mới (tối thiểu 8 ký tự)" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" name="confirm_password" class="form-control" 
                                   placeholder="Nhập lại mật khẩu mới" required>
                        </div>

                        <button type="submit" class="btn btn-primary mb-3">Cập nhật mật khẩu</button>
                        
                        <div class="text-center">
                            <a href="profile.php" class="btn btn-outline-secondary me-2">Quay lại</a>
                            <a href="../index.php" class="btn btn-outline-primary">Trang chủ</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Kiểm tra mật khẩu realtime
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.querySelector('input[name="new_password"]');
    const confirmPassword = document.querySelector('input[name="confirm_password"]');
    
    function checkPasswordMatch() {
        if (newPassword.value && confirmPassword.value) {
            if (newPassword.value === confirmPassword.value) {
                confirmPassword.style.borderColor = '#28a745';
            } else {
                confirmPassword.style.borderColor = '#dc3545';
            }
        }
    }
    
    newPassword.addEventListener('input', checkPasswordMatch);
    confirmPassword.addEventListener('input', checkPasswordMatch);
});
</script>
</body>
</html>