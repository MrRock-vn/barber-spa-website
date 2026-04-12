<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch - Bước 4</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="fw-bold mb-3">Bước 4: Xác nhận đặt lịch</h2>

    <?php if (hasFlash('error')): ?>
        <div class="alert alert-danger"><?= e(getFlash('error')) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h5 class="fw-bold"><?= e($salon['name']) ?></h5>
            <p class="mb-2">Nhân viên: <strong><?= e($staff['name'] ?? '') ?></strong></p>
            <p class="mb-2">Ngày: <strong><?= e(formatDate((string) ($_SESSION['booking_wizard']['booking_date'] ?? ''))) ?></strong></p>
            <p class="mb-2">Giờ: <strong><?= e(formatTime((string) ($_SESSION['booking_wizard']['start_time'] ?? '')) . ' - ' . formatTime((string) ($_SESSION['booking_wizard']['end_time'] ?? ''))) ?></strong></p>
            <p class="mb-3">Tổng tiền: <strong><?= e(formatMoney((float) ($summary['total_price'] ?? 0))) ?></strong></p>

            <h6 class="fw-bold">Dịch vụ đã chọn</h6>
            <ul class="mb-0">
                <?php foreach (($summary['services'] ?? []) as $service): ?>
                    <li><?= e($service['name']) ?> - <?= e(formatMoney((float) $service['price'])) ?> - <?= e((string) $service['duration']) ?> phút</li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <form method="POST" action="<?= e(BASE_URL . '/booking/create?step=4') ?>">
        <?= csrfInput() ?>

        <div class="mb-3">
            <label class="form-label">Phương thức thanh toán</label>
            <select name="payment_method" class="form-select" required>
                <option value="at_counter">Thanh toán tại quầy</option>
                <option value="online">Thanh toán online</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Ghi chú</label>
            <textarea name="notes" class="form-control" rows="4" placeholder="Nhập ghi chú nếu có..."></textarea>
        </div>

        <div class="d-flex justify-content-between">
            <a href="<?= e(BASE_URL . '/booking/create?step=3') ?>" class="btn btn-outline-secondary">Quay lại</a>
            <button type="submit" class="btn btn-danger">Xác nhận đặt lịch</button>
        </div>
    </form>
</div>
</body>
</html>