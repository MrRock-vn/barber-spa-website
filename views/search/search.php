<?php

declare(strict_types=1);
?>

<div class="container">
    <div class="mb-4">
        <h2 class="page-section-title">Kết quả tìm kiếm</h2>
        <div class="page-section-subtitle">Tìm salon phù hợp với nhu cầu của bạn</div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">Bộ lọc tìm kiếm</h4>

                    <form method="GET" action="<?= e(BASE_URL . '/search') ?>">
                        <div class="mb-3">
                            <label class="form-label">Từ khóa</label>
                            <input type="text" name="keyword" class="form-control" value="<?= e($_GET['keyword'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thành phố</label>
                            <input type="text" name="city" class="form-control" value="<?= e($_GET['city'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Quận/Huyện</label>
                            <input type="text" name="district" class="form-control" value="<?= e($_GET['district'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Danh mục</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= e((string) $category['id']) ?>" <?= (($_GET['category_id'] ?? '') === (string) $category['id']) ? 'selected' : '' ?>>
                                        <?= e($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Đánh giá tối thiểu</label>
                            <select name="min_rating" class="form-select">
                                <option value="">-- Chọn --</option>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?= $i ?>" <?= (($_GET['min_rating'] ?? '') === (string) $i) ? 'selected' : '' ?>>
                                        <?= $i ?> sao
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col">
                                <label class="form-label">Giá từ</label>
                                <input type="number" name="price_from" class="form-control" value="<?= e($_GET['price_from'] ?? '') ?>">
                            </div>
                            <div class="col">
                                <label class="form-label">Đến</label>
                                <input type="number" name="price_to" class="form-control" value="<?= e($_GET['price_to'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sắp xếp</label>
                            <select name="sort" class="form-select">
                                <option value="rating_desc" <?= (($_GET['sort'] ?? '') === 'rating_desc') ? 'selected' : '' ?>>Đánh giá cao nhất</option>
                                <option value="booking_desc" <?= (($_GET['sort'] ?? '') === 'booking_desc') ? 'selected' : '' ?>>Lượt đặt nhiều nhất</option>
                                <option value="price_asc" <?= (($_GET['sort'] ?? '') === 'price_asc') ? 'selected' : '' ?>>Giá thấp nhất</option>
                                <option value="price_desc" <?= (($_GET['sort'] ?? '') === 'price_desc') ? 'selected' : '' ?>>Giá cao nhất</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-dark w-100">Tìm kiếm</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="fw-bold mb-0">Kết quả tìm kiếm</h3>
                <div class="text-muted"><?= e((string) count($salons)) ?> salon</div>
            </div>

            <?php if (empty($salons)): ?>
                <div class="alert alert-info">Không tìm thấy salon phù hợp.</div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($salons as $salon): ?>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h4 class="fw-bold mb-0"><?= e($salon['name']) ?></h4>
                                        <span class="badge bg-warning text-dark">⭐ <?= e(number_format((float) ($salon['avg_rating'] ?? 0), 2)) ?></span>
                                    </div>

                                    <div class="text-muted mb-2"><?= e($salon['address']) ?></div>

                                    <?php if (!empty($salon['description'])): ?>
                                        <p class="text-muted"><?= e($salon['description']) ?></p>
                                    <?php endif; ?>

                                    <div class="d-flex justify-content-between small text-muted mb-3">
                                        <span>🕒 <?= e($salon['open_time']) ?> - <?= e($salon['close_time']) ?></span>
                                        <span>📌 <?= e((string) ($salon['total_bookings'] ?? 0)) ?> lượt đặt</span>
                                    </div>

                                    <a href="<?= e(BASE_URL . '/salon/' . $salon['id']) ?>" class="btn btn-danger w-100">Xem chi tiết</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>