<?php

declare(strict_types=1);
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="page-section-title"><?= e($salon['name']) ?></h2>
            <div class="page-section-subtitle"><?= e($salon['address']) ?></div>
        </div>
        <a href="<?= e(BASE_URL . '/search') ?>" class="btn btn-outline-dark">Quay lại tìm kiếm</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <div>
                            <h4 class="fw-bold mb-1"><?= e($salon['name']) ?></h4>
                            <div class="text-muted"><?= e($salon['address']) ?></div>
                        </div>

                        <div>
                            <span class="badge bg-warning text-dark">⭐ <?= e(number_format((float) ($salon['avg_rating'] ?? 0), 2)) ?></span>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <div class="fw-semibold">Giờ mở cửa</div>
                                <div><?= e($salon['open_time']) ?> - <?= e($salon['close_time']) ?></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <div class="fw-semibold">Điện thoại</div>
                                <div><?= e($salon['phone']) ?></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <div class="fw-semibold">Lượt đặt</div>
                                <div><?= e((string) ($salon['total_bookings'] ?? 0)) ?></div>
                            </div>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-2">Giới thiệu</h4>
                    <p class="text-muted mb-0"><?= e((string) ($salon['description'] ?? 'Chưa có mô tả')) ?></p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-3">Đặt lịch ngay</h4>
                    <p class="text-muted">Chọn dịch vụ, nhân viên và giờ hẹn phù hợp chỉ trong vài bước.</p>

                    <a href="<?= e(BASE_URL . '/booking/create?salon_id=' . $salon['id'] . '&step=1') ?>" class="btn btn-danger w-100">
                        Đặt lịch ngay
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($images)): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="fw-bold mb-3">Hình ảnh salon</h4>
                <div class="row g-3">
                    <?php foreach ($images as $image): ?>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-4 text-center bg-white">
                                <div class="text-muted small mb-2">Ảnh salon</div>
                                <div class="fw-semibold"><?= e($image['image_path']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="fw-bold mb-3">Dịch vụ</h4>

            <?php if (empty($services)): ?>
                <div class="alert alert-info mb-0">Chưa có dịch vụ nào.</div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($services as $service): ?>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <strong><?= e($service['name']) ?></strong>
                                    <span class="badge bg-dark"><?= e(formatMoney((float) $service['price'])) ?></span>
                                </div>
                                <div class="small text-muted mb-2"><?= e($service['category_name'] ?? '') ?></div>
                                <div class="text-muted mb-2"><?= e((string) ($service['description'] ?? '')) ?></div>
                                <div class="small">⏱ <?= e((string) $service['duration']) ?> phút</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($staffList)): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="fw-bold mb-3">Nhân viên</h4>
                <div class="row g-3">
                    <?php foreach ($staffList as $staff): ?>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="fw-semibold mb-1"><?= e($staff['name']) ?></div>
                                <div class="small text-muted mb-2"><?= e((string) ($staff['phone'] ?? '')) ?></div>
                                <div class="small"><?= e((string) ($staff['specialties'] ?? '')) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($reviews)): ?>
        <div class="card">
            <div class="card-body">
                <h4 class="fw-bold mb-3">Đánh giá gần đây</h4>
                <div class="row g-3">
                    <?php foreach ($reviews as $review): ?>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong><?= e($review['customer_name']) ?></strong>
                                    <span class="badge bg-warning text-dark">⭐ <?= e((string) $review['rating']) ?></span>
                                </div>
                                <div class="text-muted small mb-2"><?= e(formatDate($review['created_at'])) ?></div>
                                <div><?= e((string) ($review['content'] ?? '')) ?></div>

                                <?php if (!empty($review['owner_reply'])): ?>
                                    <div class="mt-3 p-2 bg-light rounded small">
                                        <strong>Phản hồi salon:</strong> <?= e($review['owner_reply']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>