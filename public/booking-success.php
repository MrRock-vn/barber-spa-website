<?php
// public/booking-success.php — Trang đặt lịch thành công
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) { header('Location: /barber-spa-website/public/login.php'); exit; }

$db        = getDB();
$bookingId = (int)($_GET['id'] ?? 0);
$user      = $_SESSION['user'];

// Lấy booking (chỉ của user đang đăng nhập)
$stmt = $db->prepare("
    SELECT b.*, s.name AS salon_name, s.address AS salon_address, s.phone AS salon_phone,
           st.name AS staff_name
    FROM bookings b
    JOIN salons s  ON s.id  = b.salon_id
    LEFT JOIN staff st ON st.id = b.staff_id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$bookingId, $user['id']]);
$booking = $stmt->fetch();

if (!$booking) { header('Location: /barber-spa-website/public/index.php'); exit; }

$services = json_decode($booking['services'], true);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đặt lịch thành công! — Barber &amp; Spa</title>
  <link rel="stylesheet" href="/barber-spa-website/public/css/style.css">
  <style>
    @keyframes popIn { from{transform:scale(.5);opacity:0} to{transform:scale(1);opacity:1} }
    @keyframes fadeUp { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
    .pop-icon { animation: popIn .5s cubic-bezier(.34,1.56,.64,1) forwards; }
    .fade-up  { animation: fadeUp .5s ease forwards; animation-delay: .3s; opacity:0; }
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container" style="max-width:600px;padding-top:60px;padding-bottom:80px;text-align:center">

  <!-- Icon -->
  <div class="pop-icon" style="width:90px;height:90px;background:rgba(76,175,130,.15);border:2px solid rgba(76,175,130,.4);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:2.5rem">
    ✅
  </div>

  <div class="fade-up">
    <h1 style="font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:8px">Đặt lịch thành công!</h1>
    <p style="color:var(--text-muted);margin-bottom:32px">Salon sẽ liên hệ xác nhận lịch hẹn của bạn sớm nhất.</p>

    <!-- Chi tiết -->
    <div class="summary-box" style="text-align:left;margin-bottom:28px">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid rgba(255,255,255,.07)">
        <span style="font-size:.8rem;text-transform:uppercase;letter-spacing:2px;color:var(--text-muted)">Mã lịch hẹn</span>
        <span style="font-family:monospace;font-size:1.1rem;color:var(--primary);font-weight:700">#<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></span>
      </div>

      <div class="summary-row"><span style="color:var(--text-muted)">Salon</span><span style="font-weight:600"><?= htmlspecialchars($booking['salon_name']) ?></span></div>
      <div class="summary-row"><span style="color:var(--text-muted)">Địa chỉ</span><span><?= htmlspecialchars($booking['salon_address']) ?></span></div>

      <?php if ($booking['salon_phone']): ?>
      <div class="summary-row"><span style="color:var(--text-muted)">Điện thoại</span><a href="tel:<?= $booking['salon_phone'] ?>" style="color:var(--primary)"><?= $booking['salon_phone'] ?></a></div>
      <?php endif; ?>

      <div class="summary-row">
        <span style="color:var(--text-muted)">Dịch vụ</span>
        <span style="text-align:right;max-width:65%"><?= implode(', ', array_column($services, 'name')) ?></span>
      </div>

      <div class="summary-row">
        <span style="color:var(--text-muted)">Nhân viên</span>
        <span><?= $booking['staff_name'] ? htmlspecialchars($booking['staff_name']) : 'Bất kỳ' ?></span>
      </div>

      <div class="summary-row">
        <span style="color:var(--text-muted)">Ngày hẹn</span>
        <span style="font-weight:600"><?= date('d/m/Y', strtotime($booking['booking_date'])) ?></span>
      </div>

      <div class="summary-row">
        <span style="color:var(--text-muted)">Giờ hẹn</span>
        <span style="font-weight:600"><?= substr($booking['start_time'],0,5) ?> – <?= substr($booking['end_time'],0,5) ?></span>
      </div>

      <div class="summary-row">
        <span style="color:var(--text-muted)">Thanh toán</span>
        <span><?= $booking['payment_method']==='online' ? '💳 Online' : '🏪 Tại quầy' ?></span>
      </div>

      <div class="summary-row" style="border-bottom:none;padding-top:14px;border-top:1px solid rgba(255,255,255,.07)">
        <span style="font-weight:700">Tổng tiền</span>
        <span class="summary-total"><?= number_format($booking['total_price']) ?>đ</span>
      </div>
    </div>

    <!-- Trạng thái -->
    <div class="alert alert-info" style="margin-bottom:24px">
      ⏳ Trạng thái: <strong>Chờ xác nhận</strong> — Salon sẽ xác nhận trong vòng 30 phút.
    </div>

    <!-- Ghi chú -->
    <?php if ($booking['notes']): ?>
    <div class="alert" style="background:var(--dark2);border:1px solid rgba(255,255,255,.1);text-align:left;margin-bottom:24px">
      📝 <strong>Ghi chú của bạn:</strong> <?= nl2br(htmlspecialchars($booking['notes'])) ?>
    </div>
    <?php endif; ?>

    <!-- Buttons -->
    <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center">
      <a href="/barber-spa-website/public/my-bookings.php" class="btn-primary-custom">
        📋 Xem lịch hẹn của tôi
      </a>
      <a href="/barber-spa-website/public/search.php" class="btn-outline-custom">
        🔍 Đặt lịch khác
      </a>
    </div>
  </div>
</div>

<footer><div class="container"><p>© 2026 Barber &amp; Spa</p></div></footer>
</body>
</html>
