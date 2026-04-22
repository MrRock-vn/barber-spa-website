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

<div class="admin-page owner-page container-fluid">
    <div class="admin-page-header mb-4">
        <div>
            <h2 class="page-section-title">Owner Dashboard</h2>
            <div class="page-section-subtitle"><?= e($salon['name']) ?> - điều hành salon hằng ngày.</div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['label' => 'Tổng booking', 'value' => $totalBookings, 'meta' => 'Hôm nay: ' . $todayBookings],
            ['label' => 'Doanh thu', 'value' => formatMoney((float) $revenue), 'meta' => 'Booking completed'],
            ['label' => 'Nhân viên', 'value' => $staffCount, 'meta' => 'Đang quản lý'],
            ['label' => 'Dịch vụ', 'value' => $serviceCount, 'meta' => 'Trong salon'],
            ['label' => 'Rating', 'value' => number_format((float) ($salon['avg_rating'] ?? 0), 2), 'meta' => $reviewCount . ' reviews'],
            ['label' => 'Sắp tới', 'value' => $upcomingBookings, 'meta' => 'Pending/confirmed'],
            ['label' => 'Hoàn thành', 'value' => $completedBookings, 'meta' => 'Đã phục vụ'],
            ['label' => 'Review', 'value' => $reviewCount, 'meta' => 'Đánh giá khách hàng'],
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
                    <canvas id="ownerBookingChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Doanh thu 6 tháng</h5>
                    <canvas id="ownerRevenueChart" height="170"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Khung giờ đông khách</h5>
                    <?php if (empty($busyHours)): ?>
                        <div class="text-muted">Chưa có dữ liệu.</div>
                    <?php else: ?>
                        <?php foreach ($busyHours as $hour): ?>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span><?= e($hour['hour_label']) ?></span>
                                <strong><?= e((string) $hour['total_bookings']) ?> lịch</strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Nhân viên được đặt nhiều</h5>
                    <?php if (empty($topStaff)): ?>
                        <div class="text-muted">Chưa có dữ liệu.</div>
                    <?php else: ?>
                        <?php foreach ($topStaff as $staff): ?>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span><?= e($staff['name']) ?></span>
                                <strong><?= e((string) $staff['total_bookings']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Dịch vụ được chọn nhiều</h5>
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

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="fw-bold mb-0">5 booking gần nhất</h5>
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
                                        <th>Tiền</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentBookings as $booking): ?>
                                        <tr>
                                            <td><?= e((string) $booking['id']) ?></td>
                                            <td><?= e($booking['customer_name']) ?></td>
                                            <td><?= e($booking['staff_name']) ?></td>
                                            <td><?= e(formatDate($booking['booking_date'])) ?></td>
                                            <td><?= e(formatMoney((float) $booking['total_price'])) ?></td>
                                            <td><span class="badge <?= ownerDashBookingStatusBadgeClass((string) $booking['status']) ?>"><?= e($booking['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="fw-bold mb-0">5 review mới nhất</h5>
                        <a href="<?= e(BASE_URL . '/owner/reviews') ?>" class="btn btn-outline-dark btn-sm">Quản lý review</a>
                    </div>

                    <?php if (empty($recentReviews)): ?>
                        <div class="alert alert-info mb-0">Chưa có review nào.</div>
                    <?php else: ?>
                        <?php foreach ($recentReviews as $review): ?>
                            <div class="border rounded-3 p-3 mb-2">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong><?= e($review['customer_name']) ?></strong>
                                    <span class="badge bg-warning text-dark"><?= e((string) $review['rating']) ?>/5</span>
                                </div>
                                <div class="small text-muted mb-2"><?= e(formatDate($review['created_at'])) ?></div>
                                <div class="small"><?= e(mb_strimwidth((string) $review['content'], 0, 120, '...')) ?></div>
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
const ownerBookingChartData = <?= json_encode($bookingChart, JSON_UNESCAPED_UNICODE) ?>;
const ownerRevenueChartData = <?= json_encode($revenueChart, JSON_UNESCAPED_UNICODE) ?>;

new Chart(document.getElementById('ownerBookingChart'), {
    type: 'line',
    data: {
        labels: ownerBookingChartData.map(item => item.label),
        datasets: [{
            label: 'Bookings',
            data: ownerBookingChartData.map(item => item.total),
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220, 53, 69, 0.12)',
            tension: 0.35,
            fill: true
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('ownerRevenueChart'), {
    type: 'bar',
    data: {
        labels: ownerRevenueChartData.map(item => item.label),
        datasets: [{
            label: 'Revenue',
            data: ownerRevenueChartData.map(item => item.revenue),
            backgroundColor: '#212529'
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>
