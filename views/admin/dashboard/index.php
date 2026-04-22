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

function adminDashPaymentStatusBadgeClass(string $status): string
{
    return match ($status) {
        'success' => 'bg-success',
        'pending' => 'bg-warning text-dark',
        'failed' => 'bg-danger',
        'refunded' => 'bg-info text-dark',
        default => 'bg-secondary',
    };
}
?>

<div class="admin-page admin-dashboard container-fluid">
    <div class="admin-page-header mb-4">
        <div>
            <h2 class="page-section-title">Admin Dashboard</h2>
            <div class="page-section-subtitle">Tổng quan user, salon, booking, payment và review.</div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['label' => 'Users', 'value' => $totalUsers, 'meta' => 'Inactive: ' . $inactiveUsers],
            ['label' => 'Salons', 'value' => $totalSalons, 'meta' => 'Active: ' . $activeSalons . ' | Pending: ' . $pendingSalons],
            ['label' => 'Bookings', 'value' => $totalBookings, 'meta' => 'Today: ' . $todayBookings],
            ['label' => 'Revenue', 'value' => formatMoney((float) $totalRevenue), 'meta' => 'Completed bookings'],
            ['label' => 'Payments success', 'value' => $successfulPayments, 'meta' => 'Total payments: ' . $totalPayments],
            ['label' => 'Reviews', 'value' => $totalReviews, 'meta' => 'Flagged: ' . $flaggedReviews],
            ['label' => 'Categories', 'value' => $totalCategories, 'meta' => 'Service groups'],
            ['label' => 'Pending bookings', 'value' => $pendingBookings, 'meta' => 'Confirmed: ' . $confirmedBookings],
        ];
        ?>
        <?php foreach ($cards as $card): ?>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="text-muted small mb-2"><?= e($card['label']) ?></div>
                        <div class="fs-3 fw-bold"><?= e((string) $card['value']) ?></div>
                        <div class="small text-muted mt-2"><?= e($card['meta']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Booking 7 ngày gần nhất</h5>
                    <canvas id="bookingChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Doanh thu 6 tháng</h5>
                    <canvas id="revenueChart" height="170"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Booking theo trạng thái</h5>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between"><span>pending</span><strong><?= e((string) $pendingBookings) ?></strong></div>
                        <div class="d-flex justify-content-between"><span>confirmed</span><strong><?= e((string) $confirmedBookings) ?></strong></div>
                        <div class="d-flex justify-content-between"><span>completed</span><strong><?= e((string) $completedBookings) ?></strong></div>
                        <div class="d-flex justify-content-between"><span>cancelled</span><strong><?= e((string) $cancelledBookings) ?></strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Payment theo trạng thái</h5>
                    <?php foreach ($paymentStatusCounts as $status => $count): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge <?= adminDashPaymentStatusBadgeClass((string) $status) ?>"><?= e((string) $status) ?></span>
                            <strong><?= e((string) $count) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Top salon nhiều lịch</h5>
                    <?php if (empty($topSalons)): ?>
                        <div class="text-muted">Chưa có dữ liệu.</div>
                    <?php else: ?>
                        <?php foreach ($topSalons as $salon): ?>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span><?= e($salon['name']) ?></span>
                                <strong><?= e((string) $salon['total_bookings']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Booking mới</h5>
                    <?php foreach ($recentBookings as $booking): ?>
                        <div class="border rounded-3 p-3 mb-2">
                            <div class="fw-semibold">#<?= e((string) $booking['id']) ?> - <?= e($booking['salon_name']) ?></div>
                            <div class="small text-muted"><?= e($booking['customer_name']) ?> | <?= e(formatDate($booking['booking_date'])) ?></div>
                            <span class="badge <?= adminDashBookingStatusBadgeClass((string) $booking['status']) ?> mt-2"><?= e($booking['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Payment mới</h5>
                    <?php foreach ($recentPayments as $payment): ?>
                        <div class="border rounded-3 p-3 mb-2">
                            <div class="fw-semibold"><?= e($payment['gateway']) ?> - <?= e(formatMoney((float) $payment['amount'])) ?></div>
                            <div class="small text-muted"><?= e((string) ($payment['customer_name'] ?? '')) ?> | <?= e((string) ($payment['salon_name'] ?? '')) ?></div>
                            <span class="badge <?= adminDashPaymentStatusBadgeClass((string) $payment['status']) ?> mt-2"><?= e($payment['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Top dịch vụ</h5>
                    <?php if (empty($topServices)): ?>
                        <div class="text-muted">Chưa có dữ liệu.</div>
                    <?php else: ?>
                        <?php foreach ($topServices as $service): ?>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span><?= e($service['name']) ?></span>
                                <strong><?= e((string) $service['total']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const bookingChartData = <?= json_encode($bookingChart, JSON_UNESCAPED_UNICODE) ?>;
const revenueChartData = <?= json_encode($revenueChart, JSON_UNESCAPED_UNICODE) ?>;

new Chart(document.getElementById('bookingChart'), {
    type: 'line',
    data: {
        labels: bookingChartData.map(item => item.label),
        datasets: [{
            label: 'Bookings',
            data: bookingChartData.map(item => item.total),
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220, 53, 69, 0.12)',
            tension: 0.35,
            fill: true
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: revenueChartData.map(item => item.label),
        datasets: [{
            label: 'Revenue',
            data: revenueChartData.map(item => item.revenue),
            backgroundColor: '#212529'
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>
