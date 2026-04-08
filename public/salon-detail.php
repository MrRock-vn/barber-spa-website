<?php
// ============================================================
// public/salon-detail.php — Chi tiết Salon (SEARCH-01)
// PHP THUẦN - MySQLi
// ============================================================
session_start();
require_once __DIR__ . '/../config/db.php';

$user = currentUser();
$salonId = (int)($_GET['id'] ?? 0);

if (!$salonId) {
    header('Location: /barber-spa-website/public/search.php');
    exit;
}

// Lấy thông tin salon
$salon = fetchOne("SELECT * FROM salons WHERE id = $salonId AND status = 'active'");

if (!$salon) {
    header('Location: /barber-spa-website/public/search.php');
    exit;
}

// Lấy ảnh salon
$images = fetchAll("SELECT * FROM salon_images WHERE salon_id = $salonId ORDER BY is_primary DESC, sort_order");

// Lấy dịch vụ
$services = fetchAll("
    SELECT sv.*, c.name AS category_name FROM services sv
    LEFT JOIN categories c ON c.id = sv.category_id
    WHERE sv.salon_id = $salonId AND sv.is_active = 1
    ORDER BY c.name, sv.sort_order
");

// Lấy nhân viên
$staff = fetchAll("SELECT * FROM staff WHERE salon_id = $salonId AND is_active = 1");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($salon['name']) ?> — Barber & Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/barber-spa-website/public/css/style.css">
    <style>
        .gallery { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 12px; margin-bottom: 32px; }
        .gallery-main { grid-row: span 2; border-radius: 12px; overflow: hidden; }
        .gallery-main img { width: 100%; height: 100%; object-fit: cover; }
        .gallery-thumb { border-radius: 8px; overflow: hidden; cursor: pointer; }
        .gallery-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .info-box { background: #f8f9fa; border: 1px solid #ddd; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ddd; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #666; }
        .service-item { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 12px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; }
        .service-name { font-weight: 600; }
        .service-price { color: #e94560; font-weight: 700; }
        .service-duration { color: #666; font-size: 0.85rem; }
        .staff-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; }
        .staff-item { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 12px; text-align: center; }
        .staff-avatar { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto 8px; border: 2px solid #ddd; }
        .staff-name { font-weight: 600; font-size: 0.9rem; }
        .sticky-btn { position: sticky; bottom: 20px; }
        .btn-book { background: #e94560; color: #fff; padding: 14px 32px; border-radius: 8px; font-weight: 700; text-decoration: none; display: inline-block; transition: all 0.2s; }
        .btn-book:hover { background: #c73652; transform: translateY(-2px); }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container" style="padding-top: 32px; padding-bottom: 60px;">
    <!-- GALLERY -->
    <?php if (!empty($images)): ?>
    <div class="gallery">
        <div class="gallery-main">
            <img src="<?= htmlspecialchars($images[0]['image_path']) ?>" alt="<?= htmlspecialchars($salon['name']) ?>" id="mainImage">
        </div>
        <?php for ($i = 1; $i < min(5, count($images)); $i++): ?>
        <div class="gallery-thumb" onclick="document.getElementById('mainImage').src='<?= htmlspecialchars($images[$i]['image_path']) ?>'">
            <img src="<?= htmlspecialchars($images[$i]['image_path']) ?>" alt="">
        </div>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 300px; gap: 32px;">
        <!-- MAIN CONTENT -->
        <div>
            <!-- HEADER -->
            <div style="margin-bottom: 32px;">
                <h1 style="font-size: 2rem; margin-bottom: 8px;"><?= htmlspecialchars($salon['name']) ?></h1>
                <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 12px;">
                    <span style="background: #e94560; color: #fff; padding: 6px 12px; border-radius: 20px; font-size: 0.9rem;">
                        ★ <?= $salon['avg_rating'] ?> (<?= $salon['total_reviews'] ?> đánh giá)
                    </span>
                    <span style="color: #666;"><?= $salon['total_bookings'] ?> lượt đặt</span>
                </div>
            </div>

            <!-- INFO BOX -->
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">📍 Địa chỉ</span>
                    <span><?= htmlspecialchars($salon['address']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">📞 Điện thoại</span>
                    <span><?= htmlspecialchars($salon['phone'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">🕐 Giờ mở cửa</span>
                    <span><?= substr($salon['open_time'], 0, 5) ?> - <?= substr($salon['close_time'], 0, 5) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">📅 Ngày làm việc</span>
                    <span>T2 - T7</span>
                </div>
            </div>

            <!-- DESCRIPTION -->
            <?php if ($salon['description']): ?>
            <div style="margin-bottom: 32px;">
                <h3 style="margin-bottom: 12px;">Giới thiệu</h3>
                <p style="color: #666; line-height: 1.8;">
                    <?= htmlspecialchars($salon['description']) ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- SERVICES -->
            <div style="margin-bottom: 32px;">
                <h3 style="margin-bottom: 16px;">Dịch vụ</h3>
                <?php
                $byCategory = [];
                foreach ($services as $sv) {
                    $cat = $sv['category_name'] ?? 'Khác';
                    if (!isset($byCategory[$cat])) $byCategory[$cat] = [];
                    $byCategory[$cat][] = $sv;
                }
                foreach ($byCategory as $catName => $list):
                ?>
                <p style="color: #e94560; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; margin-top: 16px;">
                    <?= htmlspecialchars($catName) ?>
                </p>
                <?php foreach ($list as $sv): ?>
                <div class="service-item">
                    <div>
                        <div class="service-name"><?= htmlspecialchars($sv['name']) ?></div>
                        <div class="service-duration">⏱ <?= $sv['duration'] ?> phút</div>
                    </div>
                    <div class="service-price"><?= number_format($sv['price']) ?>đ</div>
                </div>
                <?php endforeach; ?>
                <?php endforeach; ?>
            </div>

            <!-- STAFF -->
            <?php if (!empty($staff)): ?>
            <div style="margin-bottom: 32px;">
                <h3 style="margin-bottom: 16px;">Nhân viên</h3>
                <div class="staff-grid">
                    <?php foreach ($staff as $s): ?>
                    <div class="staff-item">
                        <img src="<?= htmlspecialchars($s['avatar'] ?? 'https://placehold.co/80/252a35/c8963e?text=NV') ?>"
                             alt="<?= htmlspecialchars($s['name']) ?>" class="staff-avatar">
                        <div class="staff-name"><?= htmlspecialchars($s['name']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- SIDEBAR -->
        <div>
            <div class="sticky-btn">
                <?php if ($user): ?>
                    <a href="/barber-spa-website/public/booking.php?salon_id=<?= $salonId ?>" class="btn-book w-100 text-center">
                        📅 Đặt lịch ngay
                    </a>
                <?php else: ?>
                    <a href="/barber-spa-website/public/login.php?redirect=/barber-spa-website/public/booking.php?salon_id=<?= $salonId ?>" class="btn-book w-100 text-center">
                        📅 Đặt lịch ngay
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<footer><div class="container"><p>© 2026 Barber & Spa</p></div></footer>
</body>
</html>
