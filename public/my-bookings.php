<?php
// public/my-bookings.php — Lịch hẹn của tôi
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) { header('Location: /barber-spa-website/public/login.php'); exit; }

$db   = getDB();
$user = $_SESSION['user'];

// Xử lý hủy lịch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancelId = (int)$_POST['cancel_id'];
    // Chỉ hủy được lịch pending/confirmed và trước 2h
    $check = $db->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status IN ('pending','confirmed')");
    $check->execute([$cancelId, $user['id']]);
    $b = $check->fetch();
    if ($b) {
        $bookingTime = strtotime($b['booking_date'] . ' ' . $b['start_time']);
        if ($bookingTime - time() >= 7200) {
            $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?")->execute([$cancelId]);
            $cancelMsg = 'success';
        } else {
            $cancelMsg = 'toolate';
        }
    }
    header('Location: /barber-spa-website/public/my-bookings.php?msg=' . ($cancelMsg??'error'));
    exit;
}

$msg = $_GET['msg'] ?? '';

// Lấy tất cả lịch hẹn của user
$bookings = $db->prepare("
    SELECT b.*, s.name AS salon_name, s.address AS salon_address,
           st.name AS staff_name
    FROM bookings b
    JOIN salons s ON s.id = b.salon_id
    LEFT JOIN staff st ON st.id = b.staff_id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC, b.start_time DESC
");
$bookings->execute([$user['id']]);
$bookings = $bookings->fetchAll();

$statusLabel = ['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
$statusColor = ['pending'=>'#f5c518','confirmed'=>'var(--success)','completed'=>'var(--primary)','cancelled'=>'var(--danger)'];
$statusBg    = ['pending'=>'rgba(245,197,24,.1)','confirmed'=>'rgba(76,175,130,.1)','completed'=>'rgba(200,150,62,.1)','cancelled'=>'rgba(224,82,82,.1)'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lịch hẹn của tôi — Barber &amp; Spa</title>
  <link rel="stylesheet" href="/barber-spa-website/public/css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container" style="padding-top:36px;padding-bottom:80px;max-width:860px">

  <h1 style="font-family:'Playfair Display',serif;font-size:1.9rem;margin-bottom:6px">📋 Lịch hẹn của tôi</h1>
  <p style="color:var(--text-muted);margin-bottom:28px">Xin chào, <strong><?= htmlspecialchars($user['name']) ?></strong>!</p>

  <?php if ($msg === 'success'): ?>
    <div class="alert alert-success">✅ Đã hủy lịch hẹn thành công.</div>
  <?php elseif ($msg === 'toolate'): ?>
    <div class="alert alert-danger">⚠️ Không thể hủy lịch trong vòng 2 giờ trước giờ hẹn. Vui lòng liên hệ trực tiếp salon.</div>
  <?php endif; ?>

  <?php if (empty($bookings)): ?>
    <div style="text-align:center;padding:64px 0;color:var(--text-muted)">
      <div style="font-size:3rem;margin-bottom:16px">📅</div>
      <h3 style="margin-bottom:8px">Bạn chưa có lịch hẹn nào</h3>
      <p style="margin-bottom:24px">Hãy tìm một salon và đặt lịch ngay!</p>
      <a href="/barber-spa-website/public/search.php" class="btn-primary-custom">🔍 Tìm salon ngay</a>
    </div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:16px">
    <?php foreach ($bookings as $b):
      $services  = json_decode($b['services'], true);
      $isPast    = strtotime($b['booking_date'] . ' ' . $b['start_time']) < time();
      $canCancel = in_array($b['status'],['pending','confirmed'])
                   && (strtotime($b['booking_date'] . ' ' . $b['start_time']) - time()) >= 7200;
      $status    = $b['status'];
    ?>
    <div style="background:var(--dark2);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:20px">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;margin-bottom:16px">
        <div>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
            <h3 style="font-size:1.05rem;font-weight:700"><?= htmlspecialchars($b['salon_name']) ?></h3>
            <span style="font-size:.75rem;font-weight:600;padding:3px 10px;border-radius:20px;background:<?= $statusBg[$status] ?>;color:<?= $statusColor[$status] ?>">
              <?= $statusLabel[$status] ?>
            </span>
          </div>
          <div style="font-size:.85rem;color:var(--text-muted)">📍 <?= htmlspecialchars($b['salon_address']) ?></div>
        </div>
        <div style="text-align:right">
          <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:2px">Mã lịch</div>
          <div style="font-family:monospace;font-weight:700;color:var(--primary)">#<?= str_pad($b['id'],6,'0',STR_PAD_LEFT) ?></div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:16px">
        <div style="background:var(--dark3);border-radius:8px;padding:10px 14px">
          <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:3px">📅 Ngày hẹn</div>
          <div style="font-weight:600"><?= date('d/m/Y', strtotime($b['booking_date'])) ?></div>
        </div>
        <div style="background:var(--dark3);border-radius:8px;padding:10px 14px">
          <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:3px">🕐 Giờ</div>
          <div style="font-weight:600"><?= substr($b['start_time'],0,5) ?> – <?= substr($b['end_time'],0,5) ?></div>
        </div>
        <div style="background:var(--dark3);border-radius:8px;padding:10px 14px">
          <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:3px">👤 Nhân viên</div>
          <div style="font-weight:600"><?= $b['staff_name'] ? htmlspecialchars($b['staff_name']) : 'Bất kỳ' ?></div>
        </div>
        <div style="background:var(--dark3);border-radius:8px;padding:10px 14px">
          <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:3px">💳 Thanh toán</div>
          <div style="font-weight:600"><?= $b['payment_method']==='online'?'Online':'Tại quầy' ?> · <?= $b['payment_status']==='paid'?'<span style="color:var(--success)">Đã thanh toán</span>':'<span style="color:#f5c518">Chưa thanh toán</span>' ?></div>
        </div>
      </div>

      <!-- Dịch vụ -->
      <div style="margin-bottom:14px">
        <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:8px">✂️ Dịch vụ đã đặt</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
          <?php foreach ($services as $sv): ?>
          <span style="background:rgba(200,150,62,.1);border:1px solid rgba(200,150,62,.2);color:var(--primary);padding:4px 12px;border-radius:20px;font-size:.82rem">
            <?= htmlspecialchars($sv['name']) ?> · <?= number_format($sv['price']) ?>đ
          </span>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Total + Actions -->
      <div style="display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:1px solid rgba(255,255,255,.07)">
        <div>
          <span style="color:var(--text-muted);font-size:.88rem">Tổng tiền: </span>
          <span style="font-weight:700;color:var(--primary);font-size:1.05rem"><?= number_format($b['total_price']) ?>đ</span>
        </div>
        <div style="display:flex;gap:10px">
          <?php if ($status === 'completed'): ?>
            <?php
            // Kiểm tra đã review chưa
            $hasReview = $db->prepare("SELECT id FROM reviews WHERE booking_id = ?");
            $hasReview->execute([$b['id']]);
            ?>
            <?php if (!$hasReview->fetch()): ?>
              <a href="/barber-spa-website/public/write-review.php?booking_id=<?= $b['id'] ?>" class="btn-outline-custom" style="padding:8px 16px;font-size:.85rem">
                ⭐ Viết đánh giá
              </a>
            <?php else: ?>
              <span style="color:var(--success);font-size:.85rem;align-self:center">✓ Đã đánh giá</span>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ($canCancel): ?>
            <form method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy lịch hẹn này?')">
              <input type="hidden" name="cancel_id" value="<?= $b['id'] ?>">
              <button type="submit" style="background:rgba(224,82,82,.1);border:1px solid rgba(224,82,82,.3);color:var(--danger);padding:8px 16px;border-radius:8px;cursor:pointer;font-size:.85rem;font-family:inherit">
                🗑 Hủy lịch
              </button>
            </form>
          <?php elseif (in_array($status,['pending','confirmed']) && !$canCancel): ?>
            <span style="color:var(--text-muted);font-size:.8rem;align-self:center">Liên hệ salon để hủy</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<footer><div class="container"><p>© 2026 Barber &amp; Spa</p></div></footer>
</body>
</html>
