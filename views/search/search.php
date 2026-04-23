<?php

declare(strict_types=1);
?>

<div class="search-page-enhanced">
    <div class="ui-card search-toolbar-card mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <span class="ui-eyebrow">Search</span>
                <h2 class="page-section-title mt-3 mb-2">Kết quả tìm kiếm</h2>
                <div class="page-section-subtitle">Tìm salon phù hợp theo khu vực, danh mục, rating và khoảng giá.</div>
            </div>
            <div class="ui-soft-badge"><?= e((string) count($salons)) ?> salon</div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="ui-card search-filter-panel">
                <h4 class="fw-bold mb-3">Bộ lọc tìm kiếm</h4>

                <form method="GET" action="<?= e(BASE_URL . '/search') ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Từ khóa</label>
                        <input type="text" name="keyword" class="form-control" placeholder="Tên salon, dịch vụ..." value="<?= e($_GET['keyword'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Thành phố</label>
                        <input type="text" name="city" class="form-control" value="<?= e($_GET['city'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quận/Huyện</label>
                        <input type="text" name="district" class="form-control" value="<?= e($_GET['district'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Danh mục</label>
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
                        <label class="form-label fw-semibold">Đánh giá tối thiểu</label>
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
                            <label class="form-label fw-semibold">Giá từ</label>
                            <input type="number" name="price_from" class="form-control" value="<?= e($_GET['price_from'] ?? '') ?>">
                        </div>
                        <div class="col">
                            <label class="form-label fw-semibold">Đến</label>
                            <input type="number" name="price_to" class="form-control" value="<?= e($_GET['price_to'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sắp xếp</label>
                        <select name="sort" class="form-select">
                            <option value="rating_desc" <?= (($_GET['sort'] ?? '') === 'rating_desc') ? 'selected' : '' ?>>Đánh giá cao nhất</option>
                            <option value="booking_desc" <?= (($_GET['sort'] ?? '') === 'booking_desc') ? 'selected' : '' ?>>Lượt đặt nhiều nhất</option>
                            <option value="price_asc" <?= (($_GET['sort'] ?? '') === 'price_asc') ? 'selected' : '' ?>>Giá thấp nhất</option>
                            <option value="price_desc" <?= (($_GET['sort'] ?? '') === 'price_desc') ? 'selected' : '' ?>>Giá cao nhất</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-dark rounded-3 fw-bold">Tìm kiếm</button>
                        <a href="<?= e(BASE_URL . '/search') ?>" class="btn btn-outline-secondary rounded-3 fw-bold">Xóa bộ lọc</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h3 class="fw-bold mb-0">Salon phù hợp</h3>
                <div class="text-muted"><?= e((string) count($salons)) ?> kết quả</div>
            </div>

            <?php if (empty($salons)): ?>
                <div class="ui-empty-state">
                    <div class="ui-empty-icon">0</div>
                    <h5 class="fw-bold">Không tìm thấy salon phù hợp</h5>
                    <p class="text-muted mb-0">Thử đổi từ khóa, khu vực hoặc giảm bớt điều kiện lọc.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php
                        $salonImages = [
                            BASE_URL . '/public/images/salon1.jpg',
                            BASE_URL . '/public/images/salon2.jpg',
                            BASE_URL . '/public/images/salon3.jpg',
                            BASE_URL . '/public/images/salon4.jpg',
                            BASE_URL . '/public/images/salon5.jpg',
                            BASE_URL . '/public/images/salon6.jpg',
                            BASE_URL . '/public/images/salon7.jpg'
                        ];
                    ?>
                    <?php foreach ($salons as $salon): ?>
                        <?php $salonImg = $salonImages[$salon['id'] % count($salonImages)]; ?>
                        <div class="col-md-6">
                            <div class="ui-card ui-card-hover salon-result-card h-100">
                                <div class="salon-result-media" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.2), rgba(13, 110, 253, 0.12)), url('<?= e($salon['image_url'] ?? $salonImg) ?>') center/cover no-repeat;"></div>
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <h4 class="fw-bold mb-0"><?= e($salon['name']) ?></h4>
                                        <span class="badge bg-warning text-dark rounded-pill">Rating <?= e(number_format((float) ($salon['avg_rating'] ?? 0), 2)) ?></span>
                                    </div>

                                    <div class="text-muted mb-2"><?= e($salon['address']) ?></div>

                                    <?php if (!empty($salon['description'])): ?>
                                        <p class="text-muted"><?= e($salon['description']) ?></p>
                                    <?php endif; ?>

                                    <div class="d-flex justify-content-between small text-muted mb-3">
                                        <span><?= e($salon['open_time']) ?> - <?= e($salon['close_time']) ?></span>
                                        <span><?= e((string) ($salon['total_bookings'] ?? 0)) ?> lượt đặt</span>
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
