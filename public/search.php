<?php
// ============================================================
// public/search.php — SEARCH-01 (Tìm kiếm & Khám phá Salon)
// PHP THUẦN - MySQLi
// ============================================================
session_start();
require_once __DIR__ . '/../config/db.php';

$user = currentUser();

// Lấy tham số tìm kiếm
$q = trim($_GET['q'] ?? '');
$category = (int)($_GET['category'] ?? 0);
$city = trim($_GET['city'] ?? '');
$minRating = (float)($_GET['min_rating'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

// Build query
$where = ["s.status = 'active'"];
$params = [];

if ($q) {
    $q_safe = escape($q);
    $where[] = "(s.name LIKE '%$q_safe%' OR s.description LIKE '%$q_safe%')";
}

if ($category) {
    $where[] = "EXISTS (SELECT 1 FROM services sv WHERE sv.salon_id = s.id AND sv.category_id = $category)";
}

if ($city) {
    $city_safe = escape($city);
    $where[] = "s.city LIKE '%$city_safe%'";
}

if ($minRating > 0) {
    $where[] = "s.avg_rating >= $minRating";
}

$whereClause = implode(' AND ', $where);

// Đếm tổng
$countResult = fetchOne("SELECT COUNT(*) as total FROM salons s WHERE $whereClause");
$total = $countResult['total'];
$totalPages = ceil($total / $perPage);

// Lấy dữ liệu
$offset = ($page - 1) * $perPage;
$salons = fetchAll(
    "SELECT s.*, si.image_path
    FROM salons s
    LEFT JOIN salon_images si ON si.salon_id = s.id AND si.is_primary = 1
    WHERE $whereClause
    ORDER BY s.avg_rating DESC, s.total_bookings DESC
    LIMIT $perPage OFFSET $offset"
);

// Lấy danh mục
$categories = fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");

// Lấy danh sách thành phố
$cities = fetchAll("SELECT DISTINCT city FROM salons WHERE status = 'active' ORDER BY city");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm Salon — Barber & Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/barber-spa-website/public/css/style.css">
    <style>
        .filter-section { background: #f8f9fa; border: 1px solid #ddd; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .filter-title { font-weight: 700; margin-bottom: 12px; color: #1a1a2e; font-size: 0.95rem; }
        .filter-option { display: flex; align-items: center; margin-bottom: 8px; }
        .filter-option input { margin-right: 8px; }
        .filter-option label { margin: 0; cursor: pointer; color: #333; font-size: 0.9rem; }
        .salon-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .salon-card { background: #fff; border: 1px solid #ddd; border-radius: 12px; overflow: hidden; transition: all 0.2s; cursor: pointer; }
        .salon-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.14); }
        .salon-card img { width: 100%; height: 200px; object-fit: cover; }
        .salon-info { padding: 16px; }
        .salon-name { font-weight: 700; margin-bottom: 4px; color: #1a1a2e; }
        .salon-address { font-size: 0.85rem; color: #666; margin-bottom: 8px; }
        .salon-rating { display: flex; justify-content: space-between; align-items: center; }
        .rating-badge { background: #e94560; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .pagination { margin-top: 32px; }
        .pagination a, .pagination span { color: #333; text-decoration: none; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; margin: 0 4px; display: inline-block; }
        .pagination a:hover { background: #e94560; border-color: #e94560; color: #fff; }
        .pagination .active { background: #e94560; border-color: #e94560; color: #fff; }
        .no-results { text-align: center; padding: 40px 20px; color: #666; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container" style="padding-top: 32px; padding-bottom: 60px;">
    <h1 style="margin-bottom: 32px; color: #1a1a2e;">🔍 Tìm kiếm Salon</h1>

    <div style="display: grid; grid-template-columns: 250px 1fr; gap: 24px;">
        <!-- FILTER SIDEBAR -->
        <div>
            <!-- Tìm kiếm -->
            <div class="filter-section">
                <div class="filter-title">Tìm kiếm</div>
                <form method="GET" style="display: flex; gap: 8px;">
                    <input type="text" name="q" class="form-control" placeholder="Tên salon..."
                           value="<?= htmlspecialchars($q) ?>" style="font-size: 0.9rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">🔍</button>
                </form>
            </div>

            <!-- Danh mục -->
            <div class="filter-section">
                <div class="filter-title">Danh mục dịch vụ</div>
                <?php foreach ($categories as $cat): ?>
                <div class="filter-option">
                    <input type="radio" name="category" id="cat<?= $cat['id'] ?>" value="<?= $cat['id'] ?>"
                           <?= $category === $cat['id'] ? 'checked' : '' ?>
                           onchange="document.location='?category=<?= $cat['id'] ?>'">
                    <label for="cat<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></label>
                </div>
                <?php endforeach; ?>
                <div class="filter-option">
                    <input type="radio" name="category" id="catAll" value=""
                           <?= $category === 0 ? 'checked' : '' ?>
                           onchange="document.location='?'">
                    <label for="catAll">Tất cả</label>
                </div>
            </div>

            <!-- Thành phố -->
            <div class="filter-section">
                <div class="filter-title">Thành phố</div>
                <?php foreach ($cities as $c): ?>
                <div class="filter-option">
                    <input type="radio" name="city" id="city<?= $c['city'] ?>" value="<?= $c['city'] ?>"
                           <?= $city === $c['city'] ? 'checked' : '' ?>
                           onchange="document.location='?city=<?= urlencode($c['city']) ?>'">
                    <label for="city<?= $c['city'] ?>"><?= htmlspecialchars($c['city']) ?></label>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Đánh giá -->
            <div class="filter-section">
                <div class="filter-title">Đánh giá</div>
                <div class="filter-option">
                    <input type="radio" name="rating" id="rating4" value="4"
                           <?= $minRating === 4.0 ? 'checked' : '' ?>
                           onchange="document.location='?min_rating=4'">
                    <label for="rating4">⭐⭐⭐⭐+ (4.0+)</label>
                </div>
                <div class="filter-option">
                    <input type="radio" name="rating" id="rating3" value="3"
                           <?= $minRating === 3.0 ? 'checked' : '' ?>
                           onchange="document.location='?min_rating=3'">
                    <label for="rating3">⭐⭐⭐+ (3.0+)</label>
                </div>
                <div class="filter-option">
                    <input type="radio" name="rating" id="ratingAll" value=""
                           <?= $minRating === 0.0 ? 'checked' : '' ?>
                           onchange="document.location='?'">
                    <label for="ratingAll">Tất cả</label>
                </div>
            </div>
        </div>

        <!-- RESULTS -->
        <div>
            <div style="margin-bottom: 16px; color: #666; font-size: 0.9rem;">
                Tìm thấy <strong style="color: #1a1a2e;"><?= $total ?></strong> salon
                <?php if ($q): ?> cho "<strong><?= htmlspecialchars($q) ?></strong>"<?php endif; ?>
            </div>

            <?php if (empty($salons)): ?>
                <div class="no-results">
                    <p style="font-size: 1.2rem; margin-bottom: 12px;">😕 Không tìm thấy salon nào</p>
                    <p>Vui lòng thử lại với từ khóa khác hoặc thay đổi bộ lọc.</p>
                </div>
            <?php else: ?>
                <div class="salon-grid">
                    <?php foreach ($salons as $salon): ?>
                    <a href="/barber-spa-website/public/salon-detail.php?id=<?= $salon['id'] ?>" style="text-decoration: none; color: inherit;">
                    <div class="salon-card">
                        <img src="<?= htmlspecialchars($salon['image_path'] ?? 'https://placehold.co/400x200/1a1a2e/fff?text=Salon') ?>"
                             alt="<?= htmlspecialchars($salon['name']) ?>">
                        <div class="salon-info">
                            <div class="salon-name"><?= htmlspecialchars($salon['name']) ?></div>
                            <div class="salon-address">
                                📍 <?= htmlspecialchars($salon['district'] . ', ' . $salon['city']) ?>
                            </div>
                            <div class="salon-rating">
                                <span style="font-size: 0.85rem; color: #666;">
                                    <?= $salon['total_reviews'] ?> đánh giá
                                </span>
                                <span class="rating-badge">★ <?= $salon['avg_rating'] ?></span>
                            </div>
                        </div>
                    </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- PAGINATION -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?q=<?= urlencode($q) ?>&category=<?= $category ?>&city=<?= urlencode($city) ?>&min_rating=<?= $minRating ?>&page=<?= $page - 1 ?>">← Trang trước</a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?q=<?= urlencode($q) ?>&category=<?= $category ?>&city=<?= urlencode($city) ?>&min_rating=<?= $minRating ?>&page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?q=<?= urlencode($q) ?>&category=<?= $category ?>&city=<?= urlencode($city) ?>&min_rating=<?= $minRating ?>&page=<?= $page + 1 ?>">Trang sau →</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer><div class="container"><p>© 2026 Barber & Spa</p></div></footer>
</body>
</html>
