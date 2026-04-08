<?php
session_start();
require_once __DIR__ . '/../config/db.php';
requireLogin();

$user = currentUser();
$userId = $user['id'];

$bookings = fetchAll("
    SELECT b.*, s.name AS salon_name, s.address, s.phone
    FROM bookings b
    JOIN salons s ON s.id = b.salon_id
    WHERE b.user_id = $userId
    ORDER BY b.booking_date DESC, b.start_time DESC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $bookingId = (int)$_POST['booking_id'];
    $check = fetchOne("SELECT * FROM bookings WHERE id = $bookingId AND user_id = $userId");
    
    if ($check) {
        $bookingTime = strtotime($check['booking_date'] . ' ' . $check['start_time']);
        $now = time();
        $canCancel = ($bookingTime - $now) > (2 * 3600);
        
        if ($canCancel) {
            execute("UPDATE bookings SET status = 'cancelled' WHERE id = $bookingId");
            header('Location: /barber-spa-website/public/my-bookings.php?msg=cancelled');
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
    <title>Lịch hẹn của tôi — Barber & Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/barber-spa-website/public/css/style.css">
    <style>
        .booking-card { background: #f8f9fa; border: 1px solid #ddd; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
        .booking-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px; }
        .booking-status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-pending { background: rgba(200, 150, 62, 0.2); color: #ffd699; }
        .status-confirmed { background: rgba(76, 175, 134, 0.2); color: #99ff99; }
        .status-completed { background: rgba(76, 175, 134, 0.2); color: #99ff99; }
        .status-cancelled { background: rgba(224, 82, 82, 0.2); color: #ff9999; }
        .booking-info { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 12px; }
        .info-label { color: #666; font-size: 0.85rem; }
        .info-value { font-weight: 600; margin-top: 4px; }
        .btn-cancel { background: rgba(224, 82, 82, 0.2); color: #ff9999; padding: 6px 12px; font-size: 0.85rem; border-radius: 6px; border: none; cursor: pointer; }
        .no-bookings { text-align: center; padding: 60px 20px; color: #666; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container" style="padding-top: 32px; padding-bottom: 60px; max-width: 800px;">
    <h1 style="margin-bottom: 32px;">📅 Lịch hẹn của tôi</h1>

    <?php if (empty($bookings)): ?>
        <div class="no-bookings">
            <p style="font-size: 1.2rem; margin-bottom: 12px;">😕 Bạn chưa có lịch hẹn nào</p>
            <p>Hãy <a href="/barber-spa-website/public/search.php" style="color: #e94560;">tìm kiếm salon</a> và đặt lịch ngay!</p>
        </div>
    <?php else: ?>
        <?php foreach ($bookings as $b): ?>
        <div class="booking-card">
            <div class="booking-header">
                <div>
                    <h3 style="margin: 0; margin-bottom: 4px;"><?= htmlspecialchars($b['salon_name']) ?></h3>
                    <p style="margin: 0; color: #666; font-size: 0.9rem;">Mã lịch: <strong>#<?= str_pad($b['id'], 6, '0', STR_PAD_LEFT) ?></strong></p>
                </div>
                <span class="booking-status status-<?= $b['status'] ?>">
                    <?php
                    $statusText = [
                        'pending' => '⏳ Chờ xác nhận',
                        'confirmed' => '✅ Đã xác nhận',
                        'completed' => '✓ Hoàn thành',
                        'cancelled' => '✗ Đã hủy'
                    ];
                    echo $statusText[$b['status']] ?? $b['status'];
                    ?>
                </span>
            </div>

            <div class="booking-info">
                <div>
                    <div class="info-label">📅 Ngày hẹn</div>
                    <div class="info-value"><?= date('d/m/Y', strtotime($b['booking_date'])) ?></div>
                </div>
                <div>
                    <div class="info-label">🕐 Giờ hẹn</div>
                    <div class="info-value"><?= substr($b['start_time'], 0, 5) ?> - <?= substr($b['end_time'], 0, 5) ?></div>
                </div>
            </div>

            <div style="color: #666; font-size: 0.85rem; margin-bottom: 12px;">
                📍 <?= htmlspecialchars($b['address']) ?> | 📞 <?= htmlspecialchars($b['phone']) ?>
            </div>

            <div>
                <?php
                $bookingTime = strtotime($b['booking_date'] . ' ' . $b['start_time']);
                $now = time();
                $canCancel = ($bookingTime - $now) > (2 * 3600) && $b['status'] !== 'cancelled';
                ?>
                <?php if ($canCancel): ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                    <button type="submit" name="cancel_booking" class="btn-cancel" onclick="return confirm('Bạn chắc chắn muốn hủy lịch này?')">✗ Hủy lịch</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<footer><div class="container"><p>© 2026 Barber & Spa</p></div></footer>
</body>
</html>
