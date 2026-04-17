<?php

declare(strict_types=1);

function getSalonBannerImage(int $salonId): string
{
    $banners = [
        1 => 'https://images.unsplash.com/photo-1585747860715-cd4628902d4a?w=1200&h=400&fit=crop',
        2 => 'https://images.unsplash.com/photo-1516975080664-ed2fc6a32937?w=1200&h=400&fit=crop',
        3 => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=1200&h=400&fit=crop',
        4 => 'https://images.unsplash.com/photo-1521490494784-f489156e6e0d?w=1200&h=400&fit=crop',
        5 => 'https://images.unsplash.com/photo-1600881333171-0ac8b8f89477?w=1200&h=400&fit=crop',
        6 => 'https://images.unsplash.com/photo-1599623166732-a47b7ce179e2?w=1200&h=400&fit=crop',
    ];
    return $banners[$salonId] ?? 'https://images.unsplash.com/photo-1585747860715-cd4628902d4a?w=1200&h=400&fit=crop';
}
?>

<div class="container">
    <div class="mb-4">
        <a href="<?= e(BASE_URL . '/search') ?>" class="btn btn-outline-dark btn-sm mb-3">← Quay lại tìm kiếm</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card overflow-hidden mb-4">
                <div style="height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); position: relative; overflow: hidden;">
                    <img src="<?= e(getSalonBannerImage((int) $salon['id'])) ?>" alt="<?= e($salon['name']) ?>" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.9;">
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h2 class="page-section-title mb-1"><?= e($salon['name']) ?></h2>
                            <div class="page-section-subtitle"><?= e($salon['address']) ?> • <?= e($salon['district']) ?>, <?= e($salon['city']) ?></div>
                        </div>
                        <div class="text-center">
                            <div class="badge bg-warning text-dark p-2 mb-2" style="font-size: 1.1rem;">⭐ <?= e(number_format((float) ($salon['avg_rating'] ?? 0), 2)) ?></div>
                            <div class="small text-muted"><?= e((string) ($salon['total_reviews'] ?? 0)) ?> đánh giá</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <div class="text-muted small">🕒 Giờ mở cửa</div>
                                <div class="fw-bold mt-1"><?= e($salon['open_time']) ?> - <?= e($salon['close_time']) ?></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <div class="text-muted small">📱 Điện thoại</div>
                                <div class="fw-bold mt-1"><?= e($salon['phone']) ?></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <div class="text-muted small">📊 Lượt đặt</div>
                                <div class="fw-bold mt-1"><?= e((string) ($salon['total_bookings'] ?? 0)) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <h5 class="fw-bold mb-2">📝 Giới thiệu</h5>
                        <p class="text-muted mb-0"><?= e((string) ($salon['description'] ?? 'Chưa có mô tả')) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-3">🎯 Đặt lịch ngay</h4>
                    <p class="text-muted small mb-3">Chọn dịch vụ, nhân viên và giờ hẹn phù hợp chỉ trong vài bước.</p>
                    <a href="<?= e(BASE_URL . '/booking/create?salon_id=' . $salon['id'] . '&step=1') ?>" class="btn btn-danger w-100 fw-bold">
                        Đặt lịch ngay →
                    </a>
                    <div class="mt-3 p-2 bg-info bg-opacity-10 rounded text-center small">
                        ✓ Xác nhận ngay • ✓ Thanh toán linh hoạt
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($images)): ?>
        <div class="card mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">📸 Hình ảnh salon</h5>
                <div class="row g-3">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="col-md-4 col-sm-6">
                            <img src="https://picsum.photos/400/300?random=<?= $index ?>" alt="Salon <?= $salon['name'] ?>" class="rounded-3 w-100" style="height: 200px; object-fit: cover; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="document.getElementById('modalImage').src=this.src">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3">💇 Dịch vụ</h5>

            <?php if (empty($services)): ?>
                <div class="alert alert-info mb-0">Chưa có dịch vụ nào.</div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($services as $serviceIndex => $service): ?>
                        <div class="col-md-6">
                            <div class="card border-0 bg-white shadow-sm h-100 overflow-hidden" style="transition: transform 0.2s, box-shadow 0.2s;">
                                <div style="height: 150px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); overflow: hidden;">
                                    <img src="https://picsum.photos/400/150?random=<?= $service['id'] ?>" alt="<?= $service['name'] ?>" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.9;">
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <strong class="fs-6"><?= e($service['name']) ?></strong>
                                        <span class="badge bg-danger"><?= e(formatMoney((float) $service['price'])) ?></span>
                                    </div>
                                    <div class="small text-muted mb-2">📂 <?= e($service['category_name'] ?? 'Danh mục') ?></div>
                                    <div class="small text-truncate mb-2"><?= e((string) ($service['description'] ?? '')) ?></div>
                                    <div class="small">⏱ <?= e((string) $service['duration']) ?> phút</div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($staffList)): ?>
        <div class="card mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">👥 Nhân viên</h5>
                <div class="row g-3">
                    <?php foreach ($staffList as $staffIndex => $staff): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="card border-0 bg-white shadow-sm h-100 text-center overflow-hidden">
                                <div style="height: 180px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); overflow: hidden;">
                                    <img src="https://i.pravatar.cc/200?img=<?= ($staff['id'] ?? rand(1, 100)) ?>" alt="<?= $staff['name'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div class="card-body">
                                    <h6 class="fw-bold mb-1"><?= e($staff['name']) ?></h6>
                                    <div class="small text-muted mb-2">📱 <?= e((string) ($staff['phone'] ?? 'N/A')) ?></div>
                                    <div class="small bg-light rounded p-2"><?= e((string) ($staff['specialties'] ?? 'Chuyên viên')) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($reviews)): ?>
        <div class="card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">⭐ Đánh giá gần đây</h5>
                <div class="row g-3">
                    <?php foreach ($reviews as $review): ?>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong><?= e($review['customer_name']) ?></strong>
                                        <span class="badge bg-warning text-dark">⭐ <?= e((string) $review['rating']) ?>/5</span>
                                    </div>
                                    <div class="text-muted small mb-2">📅 <?= e(formatDate($review['created_at'])) ?></div>
                                    <p class="mb-3 small"><?= e((string) ($review['content'] ?? 'Không có nội dung')) ?></p>

                                    <?php if (!empty($review['owner_reply'])): ?>
                                        <div class="p-2 bg-white border-start border-3 border-success rounded small">
                                            <strong class="text-success">✓ Phản hồi salon:</strong> <?= e($review['owner_reply']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal xem ảnh -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hình ảnh <?= e($salon['name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <img id="modalImage" src="" alt="Salon image" class="w-100 rounded">
            </div>
        </div>
    </div>
</div>
