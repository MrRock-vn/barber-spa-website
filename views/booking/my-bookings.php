<?php

declare(strict_types=1);

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

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="page-section-title">Lịch hẹn của tôi</h2>
            <div class="page-section-subtitle">Quản lý booking đã tạo và xem trạng thái nhanh chóng.</div>
        </div>
        <a href="<?= e(BASE_URL . '/home') ?>" class="btn btn-outline-dark">Về trang chủ</a>
    </div>

    <div class="row g-4">
        <?php foreach ($bookings as $booking): ?>
            <div class="col-md-6">
                <div class="card booking-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <h5 class="fw-bold mb-1"><?= e($booking['salon_name']) ?></h5>
                                <div class="text-muted">Nhân viên: <?= e($booking['staff_name']) ?></div>
                            </div>
                            <span class="booking-status <?= bookingStatusBadgeClass((string) $booking['status']) ?>">
                                <?= e(ucfirst($booking['status'])) ?>
                            </span>
                        </div>

                        <div class="row gx-3 gy-2 mb-3">
                            <div class="col-6">
                                <div class="text-muted small">Ngày</div>
                                <div class="fw-semibold"><?= e(formatDate($booking['booking_date'])) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Giờ</div>
                                <div class="fw-semibold"><?= e(formatTime($booking['start_time']) . ' - ' . formatTime($booking['end_time'])) ?></div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap align-items-center mb-3">
                            <span class="payment-status <?= paymentStatusBadgeClass((string) $booking['payment_status']) ?>">
                                <?= e(ucfirst($booking['payment_status'])) ?>
                            </span>
                        </div>

                        <div class="booking-actions">
                            <a href="<?= e(BASE_URL . '/booking/' . $booking['id']) ?>" class="btn btn-sm btn-dark">Xem chi tiết</a>

                            <?php if (in_array($booking['status'], ['pending', 'confirmed'], true)): ?>
                                <form method="POST" action="<?= e(BASE_URL . '/cancel-booking') ?>" class="m-0">
                                    <?= csrfInput() ?>
                                    <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hủy</button>
                                </form>
                            <?php endif; ?>

                            <?php if (($booking['payment_method'] ?? '') === 'online' && ($booking['payment_status'] ?? '') === 'unpaid'): ?>
                                <a href="<?= e(BASE_URL . '/payment/momo?booking_id=' . $booking['id']) ?>" class="btn btn-sm btn-danger">Thanh toán MoMo</a>
                                <a href="<?= e(BASE_URL . '/payment/vnpay?booking_id=' . $booking['id']) ?>" class="btn btn-sm btn-primary">Thanh toán VNPay</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($bookings)): ?>
            <div class="col-12">
                <div class="booking-empty">Bạn chưa có lịch hẹn nào. Hãy tạo một lịch hẹn mới để thưởng thức dịch vụ Barber Spa.</div>
            </div>
        <?php endif; ?>
    </div>
</div>