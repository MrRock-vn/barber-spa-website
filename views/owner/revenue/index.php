<?php

declare(strict_types=1);
?>

<div class="container">
    <div class="mb-4">
        <h2 class="page-section-title">Doanh thu</h2>
        <div class="page-section-subtitle"><?= e($salon['name']) ?></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= e(BASE_URL . '/owner/revenue') ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="date_from" class="form-control" value="<?= e($dateFrom) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="date_to" class="form-control" value="<?= e($dateTo) ?>">
                    </div>

                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Lọc</button>
                        <a href="<?= e(BASE_URL . '/owner/revenue') ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-2">Tổng doanh thu</div>
                    <div class="fs-3 fw-bold text-danger"><?= e(formatMoney($totalRevenue)) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-2">Booking hoàn thành</div>
                    <div class="fs-3 fw-bold"><?= e((string) $completedCount) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-2">Trung bình / booking</div>
                    <div class="fs-3 fw-bold"><?= e(formatMoney($averageRevenue)) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Danh sách booking hoàn thành</h5>

            <?php if (empty($completedBookings)): ?>
                <div class="alert alert-info mb-0">Không có dữ liệu doanh thu trong khoảng thời gian này.</div>
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
                                <th>Thanh toán</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completedBookings as $booking): ?>
                                <tr>
                                    <td><?= e((string) $booking['id']) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= e($booking['customer_name']) ?></div>
                                        <div class="small text-muted"><?= e($booking['customer_email']) ?></div>
                                    </td>
                                    <td><?= e($booking['staff_name']) ?></td>
                                    <td><?= e(formatDate($booking['booking_date'])) ?></td>
                                    <td><?= e(formatTime($booking['start_time']) . ' - ' . formatTime($booking['end_time'])) ?></td>
                                    <td class="fw-semibold text-danger"><?= e(formatMoney((float) $booking['total_price'])) ?></td>
                                    <td><?= e($booking['payment_status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>