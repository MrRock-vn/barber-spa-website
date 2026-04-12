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

<div class="container">
    <div class="mb-4">
        <h2 class="page-section-title">Admin Dashboard</h2>
        <div class="page-section-subtitle">Tổng quan hệ thống Barber Spa</div>
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