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

function bookingTimelineClass(string $bookingStatus, string $step): string
{
    if ($bookingStatus === 'cancelled') {
        return $step === 'cancelled' ? 'is-cancelled' : '';
    }

    $order = ['pending' => 1, 'confirmed' => 2, 'completed' => 3];
    $stepOrder = ['pending' => 1, 'confirmed' => 2, 'completed' => 3];

    if (!isset($order[$bookingStatus], $stepOrder[$step])) {
        return '';
    }

    if ($stepOrder[$step] < $order[$bookingStatus]) {
        return 'is-done';
    }

    return $stepOrder[$step] === $order[$bookingStatus] ? 'is-active' : '';
}

$currentStatus = (string) ($_GET['status'] ?? '');
$statusTabs = [
    '' => 'Tất cả',
    'pending' => 'Pending',
    'confirmed' => 'Confirmed',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
];
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <span class="ui-eyebrow">My bookings</span>
            <h2 class="page-section-title mt-3 mb-2">Lịch hẹn của tôi</h2>
            <div class="page-section-subtitle">Quản lý booking đã tạo, thanh toán, hủy lịch và đánh giá sau khi hoàn thành.</div>
        </div>
        <a href="<?= e(BASE_URL . '/home') ?>" class="btn btn-outline-dark rounded-3 fw-bold">Về trang chủ</a>
    </div>

    <div class="booking-tabs">
        <?php foreach ($statusTabs as $statusValue => $statusLabel): ?>
            <?php $tabUrl = $statusValue === '' ? BASE_URL . '/my-bookings' : BASE_URL . '/my-bookings?status=' . $statusValue; ?>
            <a href="<?= e($tabUrl) ?>" class="btn <?= $currentStatus === $statusValue ? 'btn-dark' : 'btn-outline-dark' ?> btn-sm">
                <?= e($statusLabel) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="ui-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="<?= e(BASE_URL . '/my-bookings') ?>" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Tìm kiếm</label>
                    <input type="text" name="keyword" class="form-control booking-form-control" placeholder="Mã booking, salon, nhân viên" value="<?= e($_GET['keyword'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select booking-form-control">
                        <option value="">-- Tất cả --</option>
                        <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>pending</option>
                        <option value="confirmed" <?= ($_GET['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>confirmed</option>
                        <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>completed</option>
                        <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark rounded-3 fw-bold flex-fill">Lọc</button>
                    <a href="<?= e(BASE_URL . '/my-bookings') ?>" class="btn btn-outline-secondary rounded-3 fw-bold">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($bookings as $booking): ?>
            <div class="col-md-6">
                <div class="card booking-card booking-card-enhanced h-100">
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

                        <div class="booking-timeline" aria-label="Booking status timeline">
                            <span class="booking-timeline-item <?= bookingTimelineClass((string) $booking['status'], 'pending') ?>"></span>
                            <span class="booking-timeline-item <?= bookingTimelineClass((string) $booking['status'], 'confirmed') ?>"></span>
                            <span class="booking-timeline-item <?= bookingTimelineClass((string) $booking['status'], 'completed') ?>"></span>
                            <span class="booking-timeline-item <?= bookingTimelineClass((string) $booking['status'], 'cancelled') ?>"></span>
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

                            <?php if (($booking['status'] ?? '') === 'completed'): ?>
                                <?php if (empty($booking['review_id'])): ?>
                                    <a href="<?= e(BASE_URL . '/write-review?booking_id=' . $booking['id']) ?>" class="btn btn-sm btn-warning rounded-pill fw-bold">Đánh giá</a>
                                <?php else: ?>
                                    <a href="<?= e(BASE_URL . '/edit-review/' . $booking['review_id']) ?>" class="btn btn-sm btn-outline-warning rounded-pill fw-bold">Sửa đánh giá</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="booking-actions">
                            <a href="<?= e(BASE_URL . '/booking/' . $booking['id']) ?>" class="btn btn-sm btn-dark rounded-pill fw-bold">Xem chi tiết</a>

                            <?php if (in_array($booking['status'], ['pending', 'confirmed'], true)): ?>
                                <form method="POST" action="<?= e(BASE_URL . '/cancel-booking') ?>" class="m-0" data-confirm="Bạn chắc chắn muốn hủy lịch hẹn này?">
                                    <?= csrfInput() ?>
                                    <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill fw-bold">Hủy</button>
                                </form>
                            <?php endif; ?>

                            <?php if (($booking['payment_method'] ?? '') === 'online' && ($booking['payment_status'] ?? '') === 'unpaid'): ?>
                                <a href="<?= e(BASE_URL . '/payment/vnpay?booking_id=' . $booking['id']) ?>" class="btn btn-sm btn-primary rounded-pill fw-bold">Thanh toán VNPay</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($bookings)): ?>
            <div class="col-12">
                <div class="ui-empty-state">
                    <div class="ui-empty-icon">B</div>
                    <h5 class="fw-bold">Chưa có lịch hẹn</h5>
                    <p class="text-muted mb-3">Hãy tạo một lịch hẹn mới để sử dụng dịch vụ Barber Spa.</p>
                    <a href="<?= e(BASE_URL . '/search') ?>" class="btn btn-danger rounded-3 fw-bold">Tìm salon</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
