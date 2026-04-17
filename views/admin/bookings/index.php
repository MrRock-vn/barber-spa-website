<?php

declare(strict_types=1);

function adminBookingStatusBadgeClass(string $status): string
{
    return match ($status) {
        'pending' => 'bg-warning text-dark',
        'confirmed' => 'bg-primary',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
        default => 'bg-secondary',
    };
}

function adminBookingPaymentBadgeClass(string $status): string
{
    return match ($status) {
        'paid' => 'bg-success',
        'unpaid' => 'bg-secondary',
        default => 'bg-secondary',
    };
}
?>

<div class="admin-page admin-bookings-page">
    <div class="admin-page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="page-section-title">Quản lý bookings</h2>
            <div class="page-section-subtitle">Xem và quản lý toàn bộ booking hệ thống</div>
        </div>
        <div class="text-muted small text-end">
            Hiện có <strong><?= e((string) count($bookings)) ?></strong> booking.
        </div>
    </div>

    <div class="card admin-card-surface mb-4 admin-filter-card">
        <div class="card-body">
            <form method="GET" action="<?= e(BASE_URL . '/admin/bookings') ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Từ khóa</label>
                        <input
                            type="text"
                            name="keyword"
                            class="form-control"
                            value="<?= e($_GET['keyword'] ?? '') ?>"
                            placeholder="Khách, salon, staff..."
                        >
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>pending</option>
                            <option value="confirmed" <?= ($_GET['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>confirmed</option>
                            <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>completed</option>
                            <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Ngày hẹn</label>
                        <input
                            type="date"
                            name="booking_date"
                            class="form-control"
                            value="<?= e($_GET['booking_date'] ?? '') ?>"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Salon</label>
                        <select name="salon_id" class="form-select">
                            <option value="">-- Tất cả salons --</option>
                            <?php foreach ($salons as $salon): ?>
                                <option
                                    value="<?= e((string) $salon['id']) ?>"
                                    <?= (($_GET['salon_id'] ?? '') === (string) $salon['id']) ? 'selected' : '' ?>
                                >
                                    <?= e($salon['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Lọc</button>
                        <a href="<?= e(BASE_URL . '/admin/bookings') ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card admin-card-surface admin-table-card">
        <div class="card-body p-0">
            <?php if (empty($bookings)): ?>
                <div class="alert alert-info mb-0">Không có booking nào phù hợp.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Khách hàng</th>
                                <th>Salon</th>
                                <th>Nhân viên</th>
                                <th>Ngày</th>
                                <th>Giờ</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thanh toán</th>
                                <th style="min-width: 220px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?= e((string) $booking['id']) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= e($booking['customer_name']) ?></div>
                                        <div class="small text-muted"><?= e($booking['customer_email']) ?></div>
                                    </td>
                                    <td><?= e($booking['salon_name']) ?></td>
                                    <td><?= e($booking['staff_name']) ?></td>
                                    <td><?= e(formatDate($booking['booking_date'])) ?></td>
                                    <td><?= e(formatTime($booking['start_time']) . ' - ' . formatTime($booking['end_time'])) ?></td>
                                    <td class="text-danger fw-semibold"><?= e(formatMoney((float) $booking['total_price'])) ?></td>
                                    <td>
                                        <span class="badge <?= adminBookingStatusBadgeClass((string) $booking['status']) ?>">
                                            <?= e($booking['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= adminBookingPaymentBadgeClass((string) $booking['payment_status']) ?>">
                                            <?= e($booking['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="admin-table-actions d-flex gap-2 flex-wrap">
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <form method="POST" action="<?= e(BASE_URL . '/admin/bookings') ?>">
                                                    <?= csrfInput() ?>
                                                    <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">
                                                    <input type="hidden" name="action" value="confirm">
                                                    <button type="submit" class="btn btn-sm btn-primary">Xác nhận</button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (in_array($booking['status'], ['pending', 'confirmed'], true)): ?>
                                                <form method="POST" action="<?= e(BASE_URL . '/admin/bookings') ?>">
                                                    <?= csrfInput() ?>
                                                    <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">
                                                    <input type="hidden" name="action" value="complete">
                                                    <button type="submit" class="btn btn-sm btn-success">Hoàn thành</button>
                                                </form>

                                                <form method="POST" action="<?= e(BASE_URL . '/admin/bookings') ?>">
                                                    <?= csrfInput() ?>
                                                    <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <button type="submit" class="btn btn-sm btn-danger">Hủy</button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (in_array($booking['status'], ['completed', 'cancelled'], true)): ?>
                                                <span class="text-muted small">Không có thao tác</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>