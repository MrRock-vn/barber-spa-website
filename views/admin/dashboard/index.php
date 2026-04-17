<?php

declare(strict_types=1);

function adminDashBookingStatusBadgeClass(string $status): string
{
    return match ($status) {
        'pending' => 'bg-warning text-dark',
        'confirmed' => 'bg-primary',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
        default => 'bg-secondary',
    };
}

function adminDashSalonStatusBadgeClass(string $status): string
{
    return match ($status) {
        'pending' => 'bg-warning text-dark',
        'active' => 'bg-success',
        'hidden' => 'bg-secondary',
        'rejected' => 'bg-danger',
        'deleted' => 'bg-dark',
        default => 'bg-secondary',
    };
}
?>

<div class="admin-page admin-dashboard container">
    <div class="admin-page-header mb-4">
        <div class="admin-page-title-group">
            <h2 class="page-section-title">Admin Dashboard</h2>
            <div class="page-section-subtitle">Tổng quan hệ thống Barber Spa</div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-5">
            <div class="card admin-card-surface admin-table-card h-100">
                <div class="card-body">
                    <h5 class="admin-section-title mb-3">KPI chính</h5>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <th class="py-3 border-top-0">Tổng users</th>
                                    <td class="py-3 text-end"><?= e((string) $totalUsers) ?></td>
                                </tr>
                                <tr>
                                    <th>Tổng salons active</th>
                                    <td class="text-end"><?= e((string) $activeSalons) ?></td>
                                </tr>
                                <tr>
                                    <th>Bookings completed</th>
                                    <td class="text-end"><?= e((string) $completedBookings) ?></td>
                                </tr>
                                <tr>
                                    <th>Bookings cancelled</th>
                                    <td class="text-end"><?= e((string) $cancelledBookings) ?></td>
                                </tr>
                                <tr>
                                    <th>Total revenue</th>
                                    <td class="text-end text-danger fw-bold"><?= e(formatMoney($totalRevenue)) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card admin-card-surface h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="admin-section-title mb-1">Thông báo hệ thống</h5>
                            <div class="small text-muted">Thông tin quan trọng dành cho admin</div>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 border-0 py-2">
                            <div class="fw-semibold">Salons đang chờ duyệt</div>
                            <div class="small text-muted">Hiện tại có <?= e((string) $pendingSalons) ?> salon cần kiểm tra.</div>
                        </div>
                        <div class="list-group-item px-0 border-0 py-2">
                            <div class="fw-semibold">Bookings hôm nay</div>
                            <div class="small text-muted">Có <?= e((string) $todayBookings) ?> booking mới trong ngày.</div>
                        </div>
                        <div class="list-group-item px-0 border-0 py-2">
                            <div class="fw-semibold">Tổng doanh thu</div>
                            <div class="small text-muted">Doanh thu hoàn tất hiện tại: <?= e(formatMoney($totalRevenue)) ?>.</div>
                        </div>
                        <div class="list-group-item px-0 border-0 py-2">
                            <div class="fw-semibold">Tình trạng booking</div>
                            <div class="small text-muted">Hoàn tất: <?= e((string) $completedBookings) ?>, Hủy: <?= e((string) $cancelledBookings) ?>.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-2">Tổng users</div>
                    <div class="fs-3 fw-bold"><?= e((string) $totalUsers) ?></div>
                    <div class="small text-muted mt-2">Inactive: <?= e((string) $inactiveUsers) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-2">Tổng salons</div>
                    <div class="fs-3 fw-bold"><?= e((string) $totalSalons) ?></div>
                    <div class="small text-muted mt-2">Pending: <?= e((string) $pendingSalons) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-2">Tổng bookings</div>
                    <div class="fs-3 fw-bold"><?= e((string) $totalBookings) ?></div>
                    <div class="small text-muted mt-2">Hôm nay: <?= e((string) $todayBookings) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-2">Tổng categories</div>
                    <div class="fs-3 fw-bold"><?= e((string) $totalCategories) ?></div>
                    <div class="small text-muted mt-2">Revenue: <?= e(formatMoney($totalRevenue)) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Users mới nhất</h5>

                    <?php if (empty($recentUsers)): ?>
                        <div class="alert alert-info mb-0">Không có user.</div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($recentUsers as $user): ?>
                                <div class="border rounded-3 p-3">
                                    <div class="fw-semibold"><?= e($user['name']) ?></div>
                                    <div class="small text-muted"><?= e($user['email']) ?></div>
                                    <div class="small mt-1">Role: <?= e($user['role']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Salons mới nhất</h5>

                    <?php if (empty($recentSalons)): ?>
                        <div class="alert alert-info mb-0">Không có salon.</div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($recentSalons as $salon): ?>
                                <div class="border rounded-3 p-3">
                                    <div class="fw-semibold"><?= e($salon['name']) ?></div>
                                    <div class="small text-muted"><?= e($salon['district']) ?>, <?= e($salon['city']) ?></div>
                                    <div class="small mt-1"><?= e($salon['owner_name']) ?> - <?= e($salon['owner_email']) ?></div>
                                    <div class="mt-2">
                                        <span class="badge <?= adminDashSalonStatusBadgeClass((string) $salon['status']) ?>">
                                            <?= e($salon['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Bookings mới nhất</h5>

                    <?php if (empty($recentBookings)): ?>
                        <div class="alert alert-info mb-0">Không có booking.</div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($recentBookings as $booking): ?>
                                <div class="border rounded-3 p-3">
                                    <div class="fw-semibold">#<?= e((string) $booking['id']) ?> - <?= e($booking['salon_name']) ?></div>
                                    <div class="small text-muted"><?= e($booking['customer_name']) ?> - <?= e($booking['staff_name']) ?></div>
                                    <div class="small mt-1"><?= e(formatDate($booking['booking_date'])) ?> | <?= e(formatTime($booking['start_time'])) ?></div>
                                    <div class="small mt-1 text-danger fw-semibold"><?= e(formatMoney((float) $booking['total_price'])) ?></div>
                                    <div class="mt-2">
                                        <span class="badge <?= adminDashBookingStatusBadgeClass((string) $booking['status']) ?>">
                                            <?= e($booking['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>