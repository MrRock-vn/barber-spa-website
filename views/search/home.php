<?php

declare(strict_types=1);
?>

<div class="container">
    <section class="rounded-4 overflow-hidden mb-5" style="background: linear-gradient(135deg, #1a1a2e 0%, #e94560 100%);">
        <div class="row g-0 align-items-center">
            <div class="col-lg-7">
                <div class="p-5 text-white">
                    <h1 class="display-3 fw-bold mb-3">Đặt lịch cắt tóc &amp; làm đẹp nhanh chóng</h1>
                    <p class="fs-3 mb-4 text-white-50">
                        Tìm salon, barber, spa phù hợp theo khu vực, dịch vụ, giá và đánh giá.
                    </p>

                    <form method="GET" action="<?= e(BASE_URL . '/search') ?>" class="bg-white rounded-4 p-3 shadow-sm">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input
                                    type="text"
                                    name="keyword"
                                    class="form-control form-control-lg"
                                    placeholder="Tìm salon, dịch vụ, khu vực..."
                                >
                            </div>

                            <div class="col-md-3">
                                <input
                                    type="text"
                                    name="city"
                                    class="form-control form-control-lg"
                                    placeholder="Thành phố"
                                >
                            </div>

                            <div class="col-md-2">
                                <input
                                    type="text"
                                    name="district"
                                    class="form-control form-control-lg"
                                    placeholder="Quận/Huyện"
                                >
                            </div>

                            <div class="col-md-2 d-grid">
                                <button type="submit" class="btn btn-danger btn-lg">Tìm kiếm</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="p-5">
                    <div class="bg-white rounded-4 shadow p-4">
                        <h3 class="fw-bold mb-3">Khám phá nhanh</h3>
                        <ul class="mb-0 ps-3 fs-5">
                            <li class="mb-2">Barber hiện đại</li>
                            <li class="mb-2">Spa chăm sóc da</li>
                            <li class="mb-2">Gội đầu &amp; Massage</li>
                            <li>Uốn, nhuộm, phục hồi tóc</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($categories)): ?>
        <section class="mb-5">
            <h2 class="page-section-title">Danh mục nổi bật</h2>
            <div class="row g-3">
                <?php foreach ($categories as $category): ?>
                    <div class="col-md-6 col-lg-4 col-xl">
                        <div class="card h-100 text-center">
                            <div class="card-body p-4">
                                <div class="display-6 mb-3"><?= e((string) ($category['icon'] ?? '✂')) ?></div>
                                <h5 class="fw-bold"><?= e($category['name']) ?></h5>
                                <p class="text-muted mb-0"><?= e((string) ($category['description'] ?? '')) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($featuredSalons)): ?>
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h2 class="page-section-title mb-0">Salon nổi bật</h2>
                <a href="<?= e(BASE_URL . '/search') ?>" class="btn btn-outline-dark">Xem tất cả</a>
            </div>

            <div class="row g-3">
                <?php foreach ($featuredSalons as $salon): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <h4 class="fw-bold mb-0"><?= e($salon['name']) ?></h4>
                                    <span class="badge bg-warning text-dark">
                                        ⭐ <?= e(number_format((float) ($salon['avg_rating'] ?? 0), 2)) ?>
                                    </span>
                                </div>

                                <div class="text-muted mb-2">
                                    <?= e($salon['district']) ?>, <?= e($salon['city']) ?>
                                </div>

                                <p class="text-muted">
                                    <?= e((string) ($salon['description'] ?? '')) ?>
                                </p>

                                <div class="small text-muted mb-3">
                                    🕒 <?= e((string) ($salon['open_time'] ?? '08:00')) ?> -
                                    <?= e((string) ($salon['close_time'] ?? '20:00')) ?>
                                </div>

                                <a href="<?= e(BASE_URL . '/salon/' . $salon['id']) ?>" class="btn btn-danger w-100">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>