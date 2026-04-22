<?php
// ============================================================================
// public/user-dashboard.php - Dashboard khách hàng (Standalone version)
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
$db = getDB();

// Lấy thống kê
$stats = [];

// Tổng số lịch hẹn
$stmt = $db->prepare('SELECT COUNT(*) as count FROM bookings WHERE user_id = ?');
$stmt->execute([$user['id']]);
$stats['total_bookings'] = $stmt->fetch()['count'];

// Lịch hẹn sắp tới
$stmt = $db->prepare('SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND booking_date >= CURDATE() AND status IN ("pending", "confirmed")');
$stmt->execute([$user['id']]);
$stats['upcoming_bookings'] = $stmt->fetch()['count'];

// Tổng số đánh giá
$stmt = $db->prepare('SELECT COUNT(*) as count FROM reviews WHERE user_id = ?');
$stmt->execute([$user['id']]);
$stats['total_reviews'] = $stmt->fetch()['count'];

// Tổng tiền đã chi
$stmt = $db->prepare('SELECT SUM(total_price) as total FROM bookings WHERE user_id = ? AND status = "completed"');
$stmt->execute([$user['id']]);
$stats['total_spent'] = $stmt->fetch()['total'] ?? 0;

// Lịch hẹn gần đây
$stmt = $db->prepare('
    SELECT b.*, s.name as salon_name, s.address as salon_address 
    FROM bookings b 
    LEFT JOIN salons s ON b.salon_id = s.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC 
    LIMIT 5
');
$stmt->execute([$user['id']]);
$recentBookings = $stmt->fetchAll();

// Salon yêu thích (đặt nhiều nhất)
$stmt = $db->prepare('
    SELECT s.name, s.address, COUNT(*) as booking_count
    FROM bookings b 
    LEFT JOIN salons s ON b.salon_id = s.id 
    WHERE b.user_id = ? 
    GROUP BY b.salon_id 
    ORDER BY booking_count DESC 
    LIMIT 3
');
$stmt->execute([$user['id']]);
$favoriteSalons = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($user['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-page { background: #f8f9fa; min-height: 100vh; padding: 20px 0; }
        .dashboard-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .stats-card { background: white; border-radius: 15px; padding: 25px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .stats-number { font-size: 32px; font-weight: bold; color: #667eea; }
        .stats-label { color: #666; font-size: 14px; margin-top: 5px; }
        .content-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .booking-item { border-left: 4px solid #667eea; padding: 15px; margin-bottom: 15px; background: #f8f9fa; border-radius: 0 8px 8px 0; }
        .booking-status { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .quick-actions { display: flex; gap: 15px; flex-wrap: wrap; }
        .quick-action { background: white; border: 2px solid #667eea; color: #667eea; padding: 15px 25px; border-radius: 10px; text-decoration: none; text-align: center; flex: 1; min-width: 150px; transition: all 0.3s; }
        .quick-action:hover { background: #667eea; color: white; }
        .welcome-text { font-size: 18px; opacity: 0.9; }
    </style>
</head>
<body>
<div class="dashboard-page">
    <div class="container">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Chào mừng, <?= htmlspecialchars($user['name']) ?>!</h2>
                    <p class="welcome-text mb-0">Quản lý lịch hẹn và trải nghiệm dịch vụ của bạn tại Barber Spa</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="quick-actions">
                        <a href="../index.php?path=booking/create" class="btn btn-light">Đặt lịch mới</a>
                        <a href="logout.php" class="btn btn-outline-light">Đăng xuất</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['total_bookings'] ?></div>
                    <div class="stats-label">Tổng lịch hẹn</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['upcoming_bookings'] ?></div>
                    <div class="stats-label">Lịch sắp tới</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['total_reviews'] ?></div>
                    <div class="stats-label">Đánh giá đã viết</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= number_format($stats['total_spent']) ?>đ</div>
                    <div class="stats-label">Tổng chi tiêu</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Lịch hẹn gần đây -->
            <div class="col-md-8">
                <div class="content-card">
                    <h5 class="mb-4">Lịch hẹn gần đây</h5>
                    
                    <?php if (empty($recentBookings)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted">Bạn chưa có lịch hẹn nào.</p>
                            <a href="../index.php?path=search" class="btn btn-primary">Tìm salon ngay</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentBookings as $booking): ?>
                            <div class="booking-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($booking['salon_name']) ?></h6>
                                        <p class="mb-1 text-muted"><?= htmlspecialchars($booking['salon_address']) ?></p>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($booking['booking_date'] . ' ' . $booking['start_time'])) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="booking-status status-<?= $booking['status'] ?>">
                                            <?php
                                            $statusText = [
                                                'pending' => 'Chờ xác nhận',
                                                'confirmed' => 'Đã xác nhận',
                                                'completed' => 'Hoàn thành',
                                                'cancelled' => 'Đã hủy'
                                            ];
                                            echo $statusText[$booking['status']] ?? $booking['status'];
                                            ?>
                                        </span>
                                        <div class="mt-1">
                                            <strong><?= number_format($booking['total_price']) ?>đ</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-3">
                            <a href="../index.php?path=my-bookings" class="btn btn-outline-primary">Xem tất cả lịch hẹn</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Quick Actions -->
                <div class="content-card">
                    <h5 class="mb-4">Thao tác nhanh</h5>
                    <div class="d-grid gap-2">
                        <a href="../index.php?path=search" class="btn btn-outline-primary">Tìm salon</a>
                        <a href="../index.php?path=my-bookings" class="btn btn-outline-primary">Lịch hẹn của tôi</a>
                        <a href="profile.php" class="btn btn-outline-primary">Thông tin cá nhân</a>
                        <a href="change-password.php" class="btn btn-outline-secondary">Đổi mật khẩu</a>
                    </div>
                </div>

                <!-- Salon yêu thích -->
                <?php if (!empty($favoriteSalons)): ?>
                <div class="content-card">
                    <h5 class="mb-4">Salon yêu thích</h5>
                    <?php foreach ($favoriteSalons as $salon): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($salon['name']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($salon['address']) ?></small>
                            </div>
                            <span class="badge bg-primary"><?= $salon['booking_count'] ?> lần</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Thông tin tài khoản -->
                <div class="content-card">
                    <h5 class="mb-4">Thông tin tài khoản</h5>
                    <div class="mb-2">
                        <strong>Email:</strong><br>
                        <span class="text-muted"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Vai trò:</strong><br>
                        <span class="badge bg-success"><?= ucfirst($user['role']) ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Thành viên từ:</strong><br>
                        <span class="text-muted">
                            <?php
                            $stmt = $db->prepare('SELECT created_at FROM users WHERE id = ?');
                            $stmt->execute([$user['id']]);
                            $userInfo = $stmt->fetch();
                            echo date('d/m/Y', strtotime($userInfo['created_at']));
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>