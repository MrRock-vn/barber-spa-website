<?php
// ============================================================
// public/index.php  — SEARCH-01 (Trang chủ)
// Người làm: Nguyễn Công Sơn
// Branch: feature/search-homepage
// ============================================================
session_start();
require_once __DIR__ . '/../config/db.php';

$db = getDB();

// Lấy Top salon nổi bật (rating cao nhất, status = active)
$topSalons = $db->query(
    "SELECT s.*, si.image_path
     FROM salons s
     LEFT JOIN salon_images si ON si.salon_id = s.id AND si.is_primary = 1
     WHERE s.status = 'active'
     ORDER BY s.avg_rating DESC, s.total_bookings DESC
     LIMIT 6"
)->fetchAll();

// Lấy danh mục dịch vụ nổi bật
$categories = $db->query(
    "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order LIMIT 5"
)->fetchAll();

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barber & Spa — Đặt lịch cắt tóc & làm đẹp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --brand: #e94560; --dark: #1a1a2e; }
        body { font-family: 'Segoe UI', sans-serif; }

        /* ── NAVBAR ── */
        .navbar { background: var(--dark) !important; }
        .navbar-brand { color: #fff !important; font-weight: 700; font-size: 1.3rem; }
        .navbar-brand span { color: var(--brand); }

        /* ── HERO ── */
        .hero {
            background: linear-gradient(135deg, var(--dark) 0%, #16213e 100%);
            padding: 80px 0 60px; color: #fff; text-align: center;
        }
        .hero h1 { font-size: 2.4rem; font-weight: 800; margin-bottom: 12px; }
        .hero p  { color: #aaa; font-size: 1.1rem; margin-bottom: 32px; }
        .search-box {
            max-width: 640px; margin: 0 auto;
            background: #fff; border-radius: 50px;
            display: flex; overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,.3);
        }
        .search-box input {
            flex: 1; border: none; outline: none;
            padding: 16px 24px; font-size: 1rem; color: #333;
        }
        .search-box button {
            background: var(--brand); color: #fff; border: none;
            padding: 0 28px; font-weight: 600; cursor: pointer;
            transition: background .2s;
        }
        .search-box button:hover { background: #c73652; }

        /* ── CATEGORY CHIPS ── */
        .category-chip {
            display: inline-block; padding: 8px 20px;
            background: #f0f0f0; border-radius: 50px;
            text-decoration: none; color: #333; font-size: .9rem;
            transition: all .2s; margin: 4px;
        }
        .category-chip:hover { background: var(--brand); color: #fff; }

        /* ── SALON CARD ── */
        .salon-card {
            border: none; border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            transition: transform .2s, box-shadow .2s;
            overflow: hidden;
        }
        .salon-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,.14);
        }
        .salon-card img { height: 200px; object-fit: cover; width: 100%; }
        .rating-badge {
            background: var(--brand); color: #fff;
            padding: 2px 8px; border-radius: 20px; font-size: .8rem;
        }
        .price-tag { color: var(--brand); font-weight: 600; }

        /* ── SECTION TITLE ── */
        .section-title { font-weight: 700; font-size: 1.4rem; color: var(--dark); }
        .section-title::after {
            content: ''; display: block; width: 40px; height: 3px;
            background: var(--brand); margin-top: 6px; border-radius: 2px;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="/">✂ Barber<span>&Spa</span></a>
        <div class="ms-auto d-flex gap-2 align-items-center">
            <?php if ($user): ?>
                <span class="text-white small">Xin chào, <?= htmlspecialchars($user['name']) ?></span>
                <a href="/booking.php" class="btn btn-sm" style="background:var(--brand);color:#fff">Đặt lịch</a>
                <a href="/logout.php" class="btn btn-sm btn-outline-light">Đăng xuất</a>
            <?php else: ?>
                <a href="/login.php"    class="btn btn-sm btn-outline-light">Đăng nhập</a>
                <a href="/register.php" class="btn btn-sm" style="background:var(--brand);color:#fff">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- HERO SECTION -->
<section class="hero">
    <div class="container">
        <h1>Đặt lịch cắt tóc & làm đẹp<br>chỉ trong vài giây ✂</h1>
        <p>Hàng trăm salon uy tín tại Hà Nội — Đặt lịch online, không cần chờ đợi</p>
        <form class="search-box" action="/search.php" method="GET">
            <input type="text" name="q" placeholder="Tìm tên salon, dịch vụ...">
            <button type="submit">🔍 Tìm kiếm</button>
        </form>
    </div>
</section>

<!-- DANH MỤC DỊCH VỤ -->
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="section-title mb-4">Dịch vụ phổ biến</h2>
        <div>
            <?php foreach ($categories as $cat): ?>
                <a href="/search.php?category=<?= $cat['id'] ?>"
                   class="category-chip">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SALON NỔI BẬT -->
<section class="py-5" style="background:#f8f9fa">
    <div class="container">
        <h2 class="section-title mb-4">Salon nổi bật</h2>
        <div class="row g-4">
            <?php foreach ($topSalons as $salon): ?>
            <div class="col-md-6 col-lg-4">
                <a href="/salon-detail.php?id=<?= $salon['id'] ?>" class="text-decoration-none text-dark">
                <div class="salon-card card h-100">
                    <img src="<?= htmlspecialchars($salon['image_path'] ?? 'https://placehold.co/400x200/1a1a2e/fff?text=Salon') ?>"
                         alt="<?= htmlspecialchars($salon['name']) ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="card-title mb-0 fw-bold"><?= htmlspecialchars($salon['name']) ?></h6>
                            <span class="rating-badge">★ <?= $salon['avg_rating'] ?></span>
                        </div>
                        <p class="text-muted small mb-2">
                            📍 <?= htmlspecialchars($salon['district'] . ', ' . $salon['city']) ?>
                        </p>
                        <p class="small text-muted mb-0">
                            <?= $salon['total_reviews'] ?> đánh giá ·
                            <?= $salon['total_bookings'] ?> lượt đặt
                        </p>
                    </div>
                </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="/search.php" class="btn btn-outline-dark px-4">Xem tất cả salon →</a>
        </div>
    </div>
</section>

<footer class="py-4 text-center text-muted small" style="background:#1a1a2e;color:#888!important">
    <p class="mb-0" style="color:#888">© 2026 Barber & Spa — LTWNC-D18CNPM2</p>
</footer>

</body>
</html>
