<?php
// ============================================================
// public/booking-success.php — Đặt lịch thành công
// ============================================================
session_start();
require_once __DIR__ . '/../config/db.php';

requireLogin();

$user = currentUser();
$bookingId = (int)($_GET['id'] ?? 0);

if (!$bookingId) {
    header('Location: /barber-spa-website/public/index.php');
    exit;
}

// Lấy thông tin booking
$booking = fetchOne("
    SELECT b.*, s.name AS salon_name, s.address, s.phone, s.open_time, s.close_time
    FROM bookings b
    JOIN salons s ON s.id = b.salon_id
    WHERE b.id = $bookingId AND b.user_id = {$user['id']}
");

if (!$booking) {
    header('Location: /barber-spa-website/public/index.php');
    exit;
}

$services = json_decode($booking['services'], true);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch thành công — Barber & Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/barber-spa-website/public/css/style.css">
    <style>
        .success-container { max-width: 600px; margin: 60px auto; text-align: center; }
        .success-icon { font-size: 4rem; margin-bottom: 20px; }
        .success-title { font-size: 1.8rem; font-weight: 700; margin-bottom: 12px; color: var(--success); }
        .booking-code { background: var(--dark2); border: 2px solid var(--border); border-radius: 12px; padding: 20px; margin: 24px 0; }
        .code-label { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 8px; }
        .code-value { font-size: 1.8rem; font-weight: 700; color: var(--brand); font-family: monospace; }
        .detail-box { background: var(--dark2); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-bottom: 20px; text-align: left; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border); }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: var(--text-muted); }
        .detail-value { font-weight: 600; }
        .action-buttons { display: flex; gap: 12px; margin-top: 24px; }
        .btn-action { flex: 1; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.2s; }
        .btn-primary-action { background: var(--brand); color: #fff; }
        .btn-primary-action:hover { background: #c73652; }
        .btn-secondary-action { background: transparent; color: var(--text); border: 1.5px solid var(--border); }
        .btn-secondary-action:hover { border-color: var(--text); }
        .info-message { background: rgba(200, 150, 62, 0.1); border: 1px solid var(--primary); border-radius: 8px; padding: 16px; margin-bottom: 20px; color: #ffd699; font-size: 0.9rem; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="success-container">
    <div class="success-icon">✅</div>
    <h1 class="success-title">Đặt lịch thành công!</h1>
    <p style="color: var(--text-muted); margin-bottom: 24px;">
        Lịch hẹn của bạn đã được ghi nhận. Vui lòng lưu mã lịch để theo dõi.
    </p>

    <!-- MÃ LỊCH -->
    <div class="booking-code">
        <div class="code-label">Mã lịch hẹn</div>
        <div class="code-value">#<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></div>
    </div>

    <!-- THÔNG TIN CHI TIẾT -->
    <div class="detail-box">
        <div class="detail-row">
            <span class="detail-label">💈 Salon</span>
            <span class="detail-value"><?= htmlspecialchars($booking['salon_name']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">📍 Địa chỉ</span>
            <span class="detail-value"><?= htmlspecialchars($booking['address']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">📞 Điện thoại</span>
            <span class="detail-value"><?= htmlspecialchars($booking['phone']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">📅 Ngày hẹn</span>
            <span class="detail-value">
                <?= date('d/m/Y', strtotime($booking['booking_date'])) ?>
                (<?= ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][date('w', strtotime($booking['booking_date']))] ?>)
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">🕐 Giờ hẹn</span>
            <span class="detail-value"><?= substr($booking['start_time'], 0, 5) ?> - <?= substr($booking['end_time'], 0, 5) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">💇 Dịch vụ</span>
            <span class="detail-value">
                <?php foreach ($services as $sv): ?>
                    <div><?= htmlspecialchars($sv['name']) ?> (<?= $sv['duration'] ?> phút)</div>
                <?php endforeach; ?>
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">💰 Tổng tiền</span>
            <span class="detail-value" style="color: var(--brand);">
                <?= number_format($booking['total_price']) ?>đ
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">💳 Thanh toán</span>
            <span class="detail-value">
                <?= $booking['payment_method'] === 'online' ? 'Online' : 'Tại quầy' ?>
            </span>
        </div>
    </div>

    <!-- THÔNG BÁO -->
    <div class="info-message">
        <strong>📌 Lưu ý:</strong> Vui lòng đến salon trước 15 phút. Nếu không thể đến, hãy hủy lịch trước 2 giờ để tránh phí.
    </div>

    <!-- NÚT HÀNH ĐỘNG -->
    <div class="action-buttons">
        <a href="/barber-spa-website/public/my-bookings.php" class="btn-action btn-primary-action">
            📅 Xem lịch hẹn của tôi
        </a>
        <a href="/barber-spa-website/public/index.php" class="btn-action btn-secondary-action">
            🏠 Về trang chủ
        </a>
    </div>
</div>

<footer style="margin-top: 60px;"><div class="container"><p>© 2026 Barber & Spa</p></div></footer>
</body>
</html>
