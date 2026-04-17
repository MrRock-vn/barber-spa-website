<?php

declare(strict_types=1);

function ownerDashBookingStatusBadgeClass(string $status): string
{
    return match ($status) {
        'pending' => 'bg-warning text-dark',
        'confirmed' => 'bg-primary',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
        default => 'bg-secondary',
    };
}
?>

<div class="admin-page owner-page">
    <div class="admin-page-header mb-4">
        <div class="admin-page-title-group">
            <h2 class="page-section-title">Owner Dashboard</h2>
            <div class="page-section-subtitle"><?= e($salon['name']) ?></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-2">Tổng booking</div>
                    <div class="fs-3 fw-bold"><?= e((string) $totalBookings) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-2">Booking hôm nay</div>
                    <div class="fs-3 fw-bold"><?= e((string) $todayBookings) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-2">Booking sắp tới</div>
                    <div class="fs-3 fw-bold"><?= e((string) $upcomingBookings) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-2">Booking hoàn thành</div>
                    <div class="fs-3 fw-bold"><?= e((string) $completedBookings) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Doanh thu tạm tính</h5>
                    <div class="fs-2 text-danger fw-bold"><?= e(formatMoney($revenue)) ?></div>
                    <div class="text-muted mt-2">Tính trên các booking có trạng thái completed.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Thông tin salon</h5>
                    <div class="mb-1"><strong>Tên salon:</strong> <?= e($salon['name']) ?></div>
                    <div class="mb-1"><strong>Địa chỉ:</strong> <?= e($salon['address']) ?></div>
                    <div class="mb-1"><strong>Khu vực:</strong> <?= e($salon['district']) ?>, <?= e($salon['city']) ?></div>
                    <div class="mb-1"><strong>Điện thoại:</strong> <?= e($salon['phone']) ?></div>
                    <div class="mb-1"><strong>Trạng thái:</strong> <?= e($salon['status']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h5 class="fw-bold mb-0">Booking gần đây</h5>
                <a href="<?= e(BASE_URL . '/owner/bookings') ?>" class="btn btn-outline-dark btn-sm">Xem tất cả</a>
            </div>

            <?php if (empty($recentBookings)): ?>
                <div class="alert alert-info mb-0">Chưa có booking nào.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Khách hàng</th>
                                <th>Nhân viên</th>
                                <th>Ngày</th>
                                <th>Giờ</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $booking): ?>
                                <tr>
                                    <td><?= e((string) $booking['id']) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= e($booking['customer_name']) ?></div>
                                        <div class="small text-muted"><?= e($booking['customer_email']) ?></div>
                                    </td>
                                    <td><?= e($booking['staff_name']) ?></td>
                                    <td><?= e(formatDate($booking['booking_date'])) ?></td>
                                    <td><?= e(formatTime($booking['start_time']) . ' - ' . formatTime($booking['end_time'])) ?></td>
                                    <td><?= e(formatMoney((float) $booking['total_price'])) ?></td>
                                    <td>
                                        <span class="badge <?= ownerDashBookingStatusBadgeClass((string) $booking['status']) ?>">
                                            <?= e($booking['status']) ?>
                                        </span>
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