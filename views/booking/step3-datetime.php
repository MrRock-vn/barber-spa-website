<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch - Bước 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="fw-bold mb-3">Bước 3: Chọn ngày & giờ</h2>
    <p class="text-muted"><?= e($salon['name']) ?> - <?= e($staff['name'] ?? '') ?></p>

    <?php if (hasFlash('error')): ?>
        <div class="alert alert-danger"><?= e(getFlash('error')) ?></div>
    <?php endif; ?>

    <div class="alert alert-info">
        Tổng thời lượng: <strong><?= e((string) ($summary['total_duration'] ?? 0)) ?> phút</strong> |
        Tổng tiền: <strong><?= e(formatMoney((float) ($summary['total_price'] ?? 0))) ?></strong>
    </div>

    <form method="POST" action="<?= e(BASE_URL . '/booking/create?step=3') ?>">
        <?= csrfInput() ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Ngày hẹn</label>
                <input type="date" name="booking_date" id="booking_date" class="form-control" required min="<?= e(date('Y-m-d')) ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Giờ hẹn</label>
                <select name="start_time" id="start_time" class="form-select" required>
                    <option value="">-- Chọn giờ --</option>
                </select>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="<?= e(BASE_URL . '/booking/create?step=2') ?>" class="btn btn-outline-secondary">Quay lại</a>
            <button type="submit" class="btn btn-danger">Tiếp tục</button>
        </div>
    </form>
</div>

<script>
const bookingDateInput = document.getElementById('booking_date');
const startTimeSelect = document.getElementById('start_time');
const staffId = <?= (int) ($_SESSION['booking_wizard']['staff_id'] ?? 0) ?>;
const duration = <?= (int) ($summary['total_duration'] ?? 0) ?>;

bookingDateInput.addEventListener('change', async function () {
    const bookingDate = this.value;
    startTimeSelect.innerHTML = '<option value="">Đang tải...</option>';

    if (!bookingDate) {
        startTimeSelect.innerHTML = '<option value="">-- Chọn giờ --</option>';
        return;
    }

    const url = `<?= e(BASE_URL) ?>/api/get-slots.php?staff_id=${staffId}&booking_date=${bookingDate}&duration=${duration}`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        startTimeSelect.innerHTML = '<option value="">-- Chọn giờ --</option>';

        if (data.success && Array.isArray(data.slots)) {
            data.slots.forEach(slot => {
                const option = document.createElement('option');
                option.value = slot;
                option.textContent = slot;
                startTimeSelect.appendChild(option);
            });
        }
    } catch (error) {
        startTimeSelect.innerHTML = '<option value="">Không tải được slot</option>';
    }
});
</script>
</body>
</html>