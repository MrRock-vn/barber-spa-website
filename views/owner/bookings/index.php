<?php

declare(strict_types=1);

function ownerBookingStatusBadgeClass(string $status): string
{
    return match ($status) {
        'pending' => 'bg-warning text-dark',
        'confirmed' => 'bg-primary',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
        default => 'bg-secondary',
    };
}

function ownerPaymentStatusBadgeClass(string $status): string
{
    return match ($status) {
        'paid' => 'bg-success',
        'unpaid' => 'bg-secondary',
        default => 'bg-secondary',
    };
}
?>

<div class="container">
    <div class="mb-4">
        <h2 class="page-section-title">Quản lý booking</h2>
        <div class="page-section-subtitle"><?= e($salon['name']) ?></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= e(BASE_URL . '/owner/bookings') ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>pending</option>
                            <option value="confirmed" <?= ($_GET['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>confirmed</option>
                            <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>completed</option>
                            <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Ngày hẹn</label>
                        <input type="date" name="booking_date" class="form-control" value="<?= e($_GET['booking_date'] ?? '') ?>">
                    </div>

                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Lọc</button>
                        <a href="<?= e(BASE_URL . '/owner/bookings') ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="alert alert-info">Chưa có booking nào.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($bookings as $booking): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3 align-items-start">
                                <div class="col-lg-8">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                        <h5 class="fw-bold mb-0">Booking #<?= e((string) $booking['id']) ?></h5>

                                        <div class="d-flex gap-2">
                                            <span class="badge <?= ownerBookingStatusBadgeClass((string) $booking['status']) ?>">
                                                <?= e($booking['status']) ?>
                                            </span>
                                            <span class="badge <?= ownerPaymentStatusBadgeClass((string) $booking['payment_status']) ?>">
                                                <?= e($booking['payment_status']) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-1"><strong>Khách hàng:</strong> <?= e($booking['customer_name']) ?></div>
                                    <div class="mb-1"><strong>Email:</strong> <?= e($booking['customer_email']) ?></div>
                                    <div class="mb-1"><strong>Nhân viên:</strong> <?= e($booking['staff_name']) ?></div>
                                    <div class="mb-1"><strong>Ngày:</strong> <?= e(formatDate($booking['booking_date'])) ?></div>
                                    <div class="mb-1"><strong>Giờ:</strong> <?= e(formatTime($booking['start_time']) . ' - ' . formatTime($booking['end_time'])) ?></div>
                                    <div class="mb-1"><strong>Tổng tiền:</strong> <?= e(formatMoney((float) $booking['total_price'])) ?></div>
                                    <div class="mb-1"><strong>Thanh toán:</strong> <?= e($booking['payment_method']) ?></div>

                                    <?php if (!empty($booking['notes'])): ?>
                                        <div class="mb-1"><strong>Ghi chú:</strong> <?= e($booking['notes']) ?></div>
                                    <?php endif; ?>

                                    <?php if (!empty($booking['cancel_reason'])): ?>
                                        <div class="mb-1 text-danger"><strong>Lý do hủy:</strong> <?= e($booking['cancel_reason']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-lg-4">
                                    <div class="border rounded-3 p-3 bg-light">
                                        <div class="fw-semibold mb-3">Thao tác</div>

                                        <div class="d-flex flex-wrap gap-2">
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <form method="POST" action="<?= e(BASE_URL . '/owner/bookings') ?>">
                                                    <?= csrfInput() ?>
                                                    <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">
                                                    <input type="hidden" name="action" value="confirm">
                                                    <button type="submit" class="btn btn-sm btn-primary">Xác nhận</button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (in_array($booking['status'], ['pending', 'confirmed'], true)): ?>
                                                <form method="POST" action="<?= e(BASE_URL . '/owner/bookings') ?>" data-confirm="Xac nhan huy booking nay?">
                                                    <?= csrfInput() ?>
                                                    <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">
                                                    <input type="hidden" name="action" value="complete">
                                                    <button type="submit" class="btn btn-sm btn-success">Hoàn thành</button>
                                                </form>

                                                <form method="POST" action="<?= e(BASE_URL . '/owner/bookings') ?>">
                                                    <?= csrfInput() ?>
                                                    <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <button type="submit" class="btn btn-sm btn-danger">Hủy</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
