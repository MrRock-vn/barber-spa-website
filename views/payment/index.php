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
        'failed' => 'bg-danger',
        default => 'bg-secondary',
    };
}

$serviceItems = json_decode((string) ($booking['services'] ?? '[]'), true);
if (!is_array($serviceItems)) {
    $serviceItems = [];
}
?>

<div class="container">
    <h2 class="page-section-title">Thanh toán booking</h2>
    <div class="page-section-subtitle">Xác nhận thanh toán cho lịch hẹn của bạn</div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <div>
                            <h4 class="fw-bold mb-1"><?= e($booking['salon_name']) ?></h4>
                            <div class="text-muted"><?= e($booking['salon_address'] ?? '') ?></div>
                        </div>

                        <div class="d-flex gap-2">
                            <span class="badge <?= bookingStatusBadgeClass((string) $booking['status']) ?>">
                                <?= e($booking['status']) ?>
                            </span>
                            <span class="badge <?= paymentStatusBadgeClass((string) $booking['payment_status']) ?>">
                                <?= e($booking['payment_status']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="fw-semibold mb-2">Thông tin lịch hẹn</div>
                                <div>Khách hàng: <?= e($booking['customer_name']) ?></div>
                                <div>Nhân viên: <?= e($booking['staff_name']) ?></div>
                                <div>Ngày: <?= e(formatDate($booking['booking_date'])) ?></div>
                                <div>Giờ: <?= e(formatTime($booking['start_time']) . ' - ' . formatTime($booking['end_time'])) ?></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="fw-semibold mb-2">Tóm tắt thanh toán</div>
                                <div>Phương thức booking: <?= e($booking['payment_method']) ?></div>
                                <div>Trạng thái thanh toán: <?= e($booking['payment_status']) ?></div>
                                <div class="mt-2 fs-5 text-danger fw-bold">
                                    <?= e(formatMoney((float) $booking['total_price'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3">Dịch vụ đã chọn</h5>

                    <?php if (empty($serviceItems)): ?>
                        <div class="alert alert-secondary">Không có dữ liệu dịch vụ.</div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($serviceItems as $service): ?>
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100 bg-white">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <strong><?= e((string) ($service['name'] ?? '')) ?></strong>
                                            <span class="badge bg-dark">
                                                <?= e(formatMoney((float) ($service['price'] ?? 0))) ?>
                                            </span>
                                        </div>

                                        <?php if (!empty($service['description'])): ?>
                                            <div class="small text-muted mb-2">
                                                <?= e((string) $service['description']) ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="small">
                                            ⏱ <?= e((string) ($service['duration'] ?? 0)) ?> phút
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($payment): ?>
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Giao dịch hiện tại</h5>
                        <div>Mã giao dịch: <strong><?= e($payment['transaction_id']) ?></strong></div>
                        <div>Gateway: <strong><?= e($payment['gateway']) ?></strong></div>
                        <div>Trạng thái: <strong><?= e($payment['status']) ?></strong></div>
                        <div>Số tiền: <strong><?= e(formatMoney((float) $payment['amount'])) ?></strong></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Thanh toán tại quầy</h5>
                    <p class="text-muted small">
                        Chọn phương án này nếu bạn muốn thanh toán trực tiếp tại salon khi đến lịch hẹn.
                    </p>

                    <form method="POST" action="<?= e(BASE_URL . '/payment/confirm') ?>">
                        <?= csrfInput() ?>
                        <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">
                        <input type="hidden" name="action" value="mark_counter">

                        <button type="submit" class="btn btn-outline-dark w-100">
                            Xác nhận thanh toán tại quầy
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Thanh toán online qua VNPay</h5>
                    <p class="text-muted small">
                        Bấm nút bên dưới để chuyển sang cổng thanh toán VNPay.
                    </p>

                    <a href="<?= e(BASE_URL . '/payment/vnpay?booking_id=' . (int) $booking['id']) ?>"
                       class="btn btn-primary w-100">
                        Thanh toán qua VNPay
                    </a>
                </div>
            </div>

            <a href="<?= e(BASE_URL . '/booking/' . $booking['id']) ?>" class="btn btn-secondary w-100">
                Quay lại chi tiết booking
            </a>
        </div>
    </div>
</div>