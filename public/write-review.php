<?php
// ============================================================
// public/write-review.php — Viết đánh giá (REVIEW-01)
// ============================================================
session_start();
require_once __DIR__ . '/../config/db.php';

requireLogin();

$user = currentUser();
$bookingId = (int)($_GET['booking_id'] ?? 0);

if (!$bookingId) {
    header('Location: /barber-spa-website/public/my-bookings.php');
    exit;
}

// Lấy thông tin booking
$booking = fetchOne("
    SELECT b.*, s.id AS salon_id, s.name AS salon_name
    FROM bookings b
    JOIN salons s ON s.id = b.salon_id
    WHERE b.id = $bookingId AND b.user_id = {$user['id']} AND b.status = 'completed'
");

if (!$booking) {
    header('Location: /barber-spa-website/public/my-bookings.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = 'Vui lòng chọn đánh giá từ 1 đến 5 sao.';
    } elseif (empty($comment)) {
        $error = 'Vui lòng nhập nội dung đánh giá.';
    } elseif (strlen($comment) < 10) {
        $error = 'Nội dung đánh giá phải có ít nhất 10 ký tự.';
    } else {
        // Kiểm tra xem đã review lịch này chưa
        $existingReview = fetchOne("SELECT id FROM reviews WHERE booking_id = $bookingId");
        if ($existingReview) {
            $error = 'Bạn đã đánh giá lịch hẹn này rồi.';
        } else {
            // Lưu review vào database
            $commentSafe = escape($comment);
            execute("INSERT INTO reviews (booking_id, user_id, salon_id, staff_id, rating, content, status) 
                     VALUES ($bookingId, {$user['id']}, {$booking['salon_id']}, " . ($booking['staff_id'] ?: 'NULL') . ", $rating, '$commentSafe', 'published')");
            
            // Cập nhật avg_rating và total_reviews của salon
            $stats = fetchOne("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE salon_id = {$booking['salon_id']} AND status = 'published'");
            $avgRating = round($stats['avg_rating'], 1);
            $totalReviews = $stats['total_reviews'];
            execute("UPDATE salons SET avg_rating = $avgRating, total_reviews = $totalReviews WHERE id = {$booking['salon_id']}");
            
            $success = 'Cảm ơn bạn đã đánh giá! Đánh giá của bạn đã được lưu.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viết đánh giá — Barber & Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/barber-spa-website/public/css/style.css">
    <style>
        .review-container { max-width: 600px; margin: 40px auto; }
        .rating-input { display: flex; gap: 12px; margin: 16px 0; }
        .star { font-size: 2.5rem; cursor: pointer; color: var(--text-muted); transition: all 0.2s; }
        .star:hover, .star.active { color: var(--brand); }
        .form-group { margin-bottom: 20px; }
        .form-label { color: var(--text); font-weight: 600; margin-bottom: 8px; display: block; }
        textarea { background: var(--dark2); border: 1px solid var(--border); color: var(--text); padding: 12px; border-radius: 8px; font-family: inherit; }
        textarea:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(233,69,96,.1); }
        .btn-submit { background: var(--brand); color: #fff; padding: 12px 32px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .btn-submit:hover { background: #c73652; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="review-container">
    <h1 style="margin-bottom: 8px;">⭐ Viết đánh giá</h1>
    <p style="color: var(--text-muted); margin-bottom: 24px;">
        Salon: <strong><?= htmlspecialchars($booking['salon_name']) ?></strong>
    </p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="/barber-spa-website/public/my-bookings.php" class="btn-submit">← Quay lại lịch hẹn</a>
        </div>
    <?php else: ?>
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Đánh giá của bạn</label>
            <div class="rating-input" id="ratingInput">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="star" data-rating="<?= $i ?>" onclick="setRating(<?= $i ?>)">★</span>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="ratingValue" value="0">
        </div>

        <div class="form-group">
            <label class="form-label">Nội dung đánh giá</label>
            <textarea name="comment" rows="6" placeholder="Chia sẻ trải nghiệm của bạn tại salon này..." required></textarea>
            <small style="color: var(--text-muted);">Tối thiểu 10 ký tự</small>
        </div>

        <div style="display: flex; gap: 12px;">
            <button type="submit" class="btn-submit">✅ Gửi đánh giá</button>
            <a href="/barber-spa-website/public/my-bookings.php" style="padding: 12px 32px; border: 1px solid var(--border); border-radius: 8px; text-decoration: none; color: var(--text);">← Quay lại</a>
        </div>
    </form>
    <?php endif; ?>
</div>

<footer style="margin-top: 60px;"><div class="container"><p>© 2026 Barber & Spa</p></div></footer>

<script>
function setRating(rating) {
    document.getElementById('ratingValue').value = rating;
    document.querySelectorAll('.star').forEach((star, i) => {
        if (i < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}
</script>
</body>
</html>
