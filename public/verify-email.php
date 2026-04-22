<?php
// ============================================================================
// public/verify-email.php - Xác thực email (Standalone version)
// Người làm: Nguyễn Văn Quang
// ============================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

$token = trim($_GET['token'] ?? '');
$message = '';
$success = false;

if (empty($token)) {
    $message = 'Token xác thực không hợp lệ.';
} else {
    $db = getDB();
    
    // Tìm user với token
    $stmt = $db->prepare('SELECT * FROM users WHERE email_token = ? AND email_verified_at IS NULL LIMIT 1');
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $message = 'Token xác thực không hợp lệ hoặc đã được sử dụng.';
    } else {
        // Kiểm tra token có hết hạn không (24 giờ)
        $createdAt = strtotime($user['created_at']);
        $now = time();
        $hoursDiff = ($now - $createdAt) / 3600;
        
        if ($hoursDiff > 24) {
            $message = 'Token xác thực đã hết hạn. Vui lòng đăng ký lại.';
        } else {
            // Xác thực thành công
            $stmt = $db->prepare('UPDATE users SET email_verified_at = NOW(), email_token = NULL WHERE id = ?');
            $stmt->execute([$user['id']]);
            
            $message = 'Xác thực email thành công! Bạn có thể đăng nhập ngay bây giờ.';
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực email - Barber Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .verify-page { min-height: 100vh; display: flex; align-items: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .verify-card { background: white; border-radius: 20px; padding: 50px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 500px; width: 100%; }
        .verify-icon { width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 30px; display: flex; align-items: center; justify-content: center; font-size: 40px; }
        .verify-icon.success { background: #d4edda; color: #155724; }
        .verify-icon.error { background: #f8d7da; color: #721c24; }
        .verify-title { font-size: 24px; font-weight: 600; margin-bottom: 15px; }
        .verify-message { color: #666; margin-bottom: 30px; line-height: 1.6; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px 30px; border-radius: 8px; }
        .btn-outline-secondary { border-radius: 8px; padding: 12px 30px; }
    </style>
</head>
<body>
<div class="verify-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="verify-card">
                    <div class="verify-icon <?= $success ? 'success' : 'error' ?>">
                        <?= $success ? '✓' : '✗' ?>
                    </div>
                    
                    <h3 class="verify-title">
                        <?= $success ? 'Xác thực thành công!' : 'Xác thực thất bại' ?>
                    </h3>
                    
                    <p class="verify-message">
                        <?= htmlspecialchars($message) ?>
                    </p>
                    
                    <?php if ($success): ?>
                        <div class="d-grid gap-2">
                            <a href="login.php" class="btn btn-primary">Đăng nhập ngay</a>
                            <a href="../index.php" class="btn btn-outline-secondary">Về trang chủ</a>
                        </div>
                    <?php else: ?>
                        <div class="d-grid gap-2">
                            <a href="register.php" class="btn btn-primary">Đăng ký lại</a>
                            <a href="login.php" class="btn btn-outline-secondary">Đăng nhập</a>
                        </div>
                    <?php endif; ?>
                    
                    <hr class="my-4">
                    
                    <div class="text-muted">
                        <small>
                            <strong>Lưu ý:</strong><br>
                            • Token xác thực có hiệu lực trong 24 giờ<br>
                            • Mỗi token chỉ sử dụng được 1 lần<br>
                            • Nếu gặp vấn đề, vui lòng liên hệ hỗ trợ
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>