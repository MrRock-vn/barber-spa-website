<?php
// ============================================================================
// public/profile.php - Trang thông tin cá nhân (Standalone version)
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

// Lấy thông tin chi tiết từ database
$db = getDB();
$stmt = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$user['id']]);
$userDetail = $stmt->fetch();

if (!$userDetail) {
    header('Location: logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || mb_strlen($name) < 2) {
        $error = 'Họ tên phải có ít nhất 2 ký tự.';
    } elseif (!empty($phone) && !preg_match('/^[0-9+\-\s()]{10,15}$/', $phone)) {
        $error = 'Số điện thoại không hợp lệ.';
    } else {
        // Cập nhật thông tin
        $stmt = $db->prepare('UPDATE users SET name=?, phone=?, address=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([$name, $phone, $address, $user['id']]);

        // Cập nhật session
        $_SESSION['user']['name'] = $name;
        
        $success = 'Cập nhật thông tin thành công!';
        
        // Refresh user data
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$user['id']]);
        $userDetail = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - Barber Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-page { min-height: 100vh; background: #f8f9fa; padding: 40px 0; }
        .profile-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .profile-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .profile-avatar { width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 40px; }
        .profile-body { padding: 30px; }
        .form-label { font-weight: 600; color: #333; }
        .form-control { border-radius: 8px; padding: 12px 15px; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px 30px; border-radius: 8px; }
        .stats-card { background: #f8f9fa; border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 20px; }
        .stats-number { font-size: 24px; font-weight: bold; color: #667eea; }
        .stats-label { color: #666; font-size: 14px; }
    </style>
</head>
<body>
<div class="profile-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?= strtoupper(substr($userDetail['name'], 0, 1)) ?>
                        </div>
                        <h3><?= htmlspecialchars($userDetail['name']) ?></h3>
                        <p><?= htmlspecialchars($userDetail['email']) ?></p>
                        <small>Thành viên từ: <?= date('d/m/Y', strtotime($userDetail['created_at'])) ?></small>
                    </div>

                    <div class="profile-body">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-number">
                                        <?php
                                        $stmt = $db->prepare('SELECT COUNT(*) as count FROM bookings WHERE user_id = ?');
                                        $stmt->execute([$user['id']]);
                                        echo $stmt->fetch()['count'];
                                        ?>
                                    </div>
                                    <div class="stats-label">Lịch hẹn</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-number">
                                        <?php
                                        $stmt = $db->prepare('SELECT COUNT(*) as count FROM reviews WHERE user_id = ?');
                                        $stmt->execute([$user['id']]);
                                        echo $stmt->fetch()['count'];
                                        ?>
                                    </div>
                                    <div class="stats-label">Đánh giá</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-number">
                                        <?= ucfirst($userDetail['role']) ?>
                                    </div>
                                    <div class="stats-label">Vai trò</div>
                                </div>
                            </div>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Họ và tên</label>
                                    <input type="text" name="name" class="form-control" 
                                           value="<?= htmlspecialchars($userDetail['name']) ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" 
                                           value="<?= htmlspecialchars($userDetail['email']) ?>" disabled>
                                    <small class="text-muted">Email không thể thay đổi</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?= htmlspecialchars($userDetail['phone'] ?? '') ?>" 
                                           placeholder="0123456789">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày tham gia</label>
                                    <input type="text" class="form-control" 
                                           value="<?= date('d/m/Y H:i', strtotime($userDetail['created_at'])) ?>" disabled>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <textarea name="address" class="form-control" rows="3" 
                                          placeholder="Nhập địa chỉ của bạn"><?= htmlspecialchars($userDetail['address'] ?? '') ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                                <div>
                                    <a href="change-password.php" class="btn btn-outline-secondary me-2">Đổi mật khẩu</a>
                                    <a href="../index.php" class="btn btn-outline-primary">Về trang chủ</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>