<?php

declare(strict_types=1);

$serviceItems = json_decode((string) ($booking['services'] ?? '[]'), true);
if (!is_array($serviceItems)) {
    $serviceItems = [];
}

function bookingStatusBadgeClass(string $status): string
{
    return match ($status) {
        'pending' => 'bg-warning text-dark',
        'confirmed' => 'bg-primary',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
        default => 'bg-secondary',
    };
}

function paymentStatusBadgeClass(string $status): string
{
    return match ($status) {
        'paid' => 'bg-success',
        'unpaid' => 'bg-secondary',
        default => 'bg-secondary',
    };
}
?>

<div class="container">
    <h2 class="page-section-title">Chi tiết lịch hẹn</h2>
    <div class="page-section-subtitle">Thông tin booking của bạn</div>

    <div class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h4 class="fw-bold mb-1"><?= e($booking['salon_name']) ?></h4>
                    <div class="text-muted"><?= e($booking['salon_address'] ?? '') ?></div>
                </div>

                <div class="d-flex gap-2">
                    <span class="badge <?= bookingStatusBadgeClass((string) $booking['status']) ?>">
                        <?= e($booking['status']) ?>
                    </span>
                    <span class="badge <?= paymentStatusBadgeClass((string) $booking['payment_status']) ?>">
                        <?= e($booking['payment_status']) ?>
                    </span>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="p-3 bg-white border rounded-3 h-100">
                        <div class="fw-semibold mb-2">Thông tin khách hàng</div>
                        <div>Khách hàng: <?= e($booking['customer_name']) ?></div>
                        <div>Email: <?= e($booking['customer_email']) ?></div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-white border rounded-3 h-100">
                        <div class="fw-semibold mb-2">Thông tin lịch hẹn</div>
                        <div>Nhân viên: <?= e($booking['staff_name']) ?></div>
                        <div>Ngày: <?= e(formatDate($booking['booking_date'])) ?></div>
                        <div>Giờ: <?= e(formatTime($booking['start_time']) . ' - ' . formatTime($booking['end_time'])) ?></div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="fw-semibold">Tổng tiền</div>
                        <div class="fs-5 text-danger fw-bold"><?= e(formatMoney((float) $booking['total_price'])) ?></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="fw-semibold">Phương thức thanh toán</div>
                        <div><?= e($booking['payment_method']) ?></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="fw-semibold">Ghi chú</div>
                        <div><?= e((string) ($booking['notes'] ?? '')) ?: 'Không có' ?></div>
                    </div>
                </div>
            </div>

            <h5 class="fw-bold mb-3">Dịch vụ đã chọn</h5>

            <?php if (empty($serviceItems)): ?>
                <div class="alert alert-secondary">Không có dữ liệu dịch vụ.</div>
            <?php else: ?>
                <div class="row g-3 mb-4">
                    <?php foreach ($serviceItems as $service): ?>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-white">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <strong><?= e((string) ($service['name'] ?? '')) ?></strong>
                                    <span class="badge bg-dark">
                                        <?= e(formatMoney((float) ($service['price'] ?? 0))) ?>
                                    </span>
                                </div>

                                <?php if (!empty($service['description'])): ?>
                                    <div class="small text-muted mb-2">
                                        <?= e((string) $service['description']) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="small">
                                    <?= e((string) ($service['duration'] ?? 0)) ?> phút
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= e(BASE_URL . '/my-bookings') ?>" class="btn btn-dark">Quay lại</a>

                <?php if (($booking['payment_method'] ?? '') === 'online' && ($booking['payment_status'] ?? '') === 'unpaid'): ?>
                    <a href="<?= e(BASE_URL . '/payment/vnpay?booking_id=' . $booking['id']) ?>" class="btn btn-primary">Thanh toán VNPay</a>
                <?php endif; ?>

                <?php if (in_array($booking['status'], ['pending', 'confirmed'], true)): ?>
                    <form method="POST" action="<?= e(BASE_URL . '/cancel-booking') ?>">
                        <?= csrfInput() ?>
                        <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">
                        <button type="submit" class="btn btn-danger">Hủy lịch</button>
                    </form>
                <?php endif; ?>

                <?php if (($booking['status'] ?? '') === 'completed'): ?>
                    <?php if (empty($booking['review_id'])): ?>
                        <a href="<?= e(BASE_URL . '/write-review?booking_id=' . $booking['id']) ?>" class="btn btn-warning">Viết đánh giá</a>
                    <?php else: ?>
                        <a href="<?= e(BASE_URL . '/edit-review/' . $booking['review_id']) ?>" class="btn btn-outline-warning">Sửa đánh giá</a>
                        <form method="POST" action="<?= e(BASE_URL . '/delete-review/' . $booking['review_id']) ?>" data-confirm="Bạn chắc chắn muốn xóa đánh giá này?">
                            <?= csrfInput() ?>
                            <button type="submit" class="btn btn-outline-danger">Xóa đánh giá</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
