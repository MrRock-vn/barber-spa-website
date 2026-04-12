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

<div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="page-section-title">Lịch hẹn của tôi</h2>
            <div class="page-section-subtitle">Theo dõi các booking đã tạo</div>
        </div>
        <a href="<?= e(BASE_URL . '/home') ?>" class="btn btn-outline-dark">Về trang chủ</a>
    </div>

    <div class="row g-3">
        <?php foreach ($bookings as $booking): ?>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <h5 class="fw-bold mb-0"><?= e($booking['salon_name']) ?></h5>
                            <span class="badge <?= bookingStatusBadgeClass((string) $booking['status']) ?>">
                                <?= e($booking['status']) ?>
                            </span>
                        </div>

                        <p class="mb-1">Nhân viên: <?= e($booking['staff_name']) ?></p>
                        <p class="mb-1">Ngày: <?= e(formatDate($booking['booking_date'])) ?></p>
                        <p class="mb-1">Giờ: <?= e(formatTime($booking['start_time']) . ' - ' . formatTime($booking['end_time'])) ?></p>
                        <p class="mb-3">
                            Thanh toán:
                            <span class="badge <?= paymentStatusBadgeClass((string) $booking['payment_status']) ?>">
                                <?= e($booking['payment_status']) ?>
                            </span>
                        </p>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="<?= e(BASE_URL . '/booking/' . $booking['id']) ?>" class="btn btn-sm btn-dark">Xem chi tiết</a>

                            <?php if (in_array($booking['status'], ['pending', 'confirmed'], true)): ?>
                                <form method="POST" action="<?= e(BASE_URL . '/cancel-booking') ?>">
                                    <?= csrfInput() ?>
                                    <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Hủy</button>
                                </form>
                            <?php endif; ?>

                            <?php if (($booking['payment_method'] ?? '') === 'online' && ($booking['payment_status'] ?? '') === 'unpaid'): ?>
                                <a href="<?= e(BASE_URL . '/payment?booking_id=' . $booking['id']) ?>" class="btn btn-sm btn-success">Thanh toán</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($bookings)): ?>
            <div class="col-12">
                <div class="alert alert-info">Bạn chưa có lịch hẹn nào.</div>
            </div>
        <?php endif; ?>
    </div>
</div>