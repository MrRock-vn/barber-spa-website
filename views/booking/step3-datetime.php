<?php declare(strict_types=1); ?>
<?php
$selectedServices = (array) ($summary['services'] ?? []);
$selectedServiceNames = array_map(
    static fn (array $service): string => (string) ($service['name'] ?? ''),
    $selectedServices
);
$selectedServiceText = empty($selectedServiceNames) ? 'Chưa chọn' : implode(', ', array_filter($selectedServiceNames));
$selectedPrice = !empty($summary['total_price']) ? formatMoney((float) $summary['total_price']) : '0đ';
$selectedDate = (string) ($wizard['booking_date'] ?? '');
$selectedTime = (string) ($wizard['start_time'] ?? '');
?>
<div class="bk-page">
    <div class="bk-shell container">
        <div class="bk-progress">
            <div class="bk-step">
                <span class="bk-step-index">1</span>
                <span class="bk-step-text">Dịch vụ</span>
            </div>
            <div class="bk-step">
                <span class="bk-step-index">2</span>
                <span class="bk-step-text">Nhân viên</span>
            </div>
            <div class="bk-step is-active">
                <span class="bk-step-index">3</span>
                <span class="bk-step-text">Ngày giờ</span>
            </div>
            <div class="bk-step">
                <span class="bk-step-index">4</span>
                <span class="bk-step-text">Xác nhận</span>
            </div>
        </div>

        <?php if (hasFlash('error')): ?>
            <div class="alert alert-danger rounded-4 mb-4"><?= e(getFlash('error')) ?></div>
        <?php endif; ?>

        <div class="bk-layout">
            <div>
                <div class="bk-panel">
                    <div class="bk-head">
                        <p class="bk-kicker">Bước 3 trong 4</p>
                        <h1 class="bk-title">Chọn ngày giờ</h1>
                        <p class="bk-subtitle"><?= e($salon['name']) ?> · <?= e((string) ($staff['name'] ?? '')) ?></p>
                    </div>

                    <div class="bk-confirm mb-4">
                        <div class="bk-confirm-list">
                            <div class="bk-confirm-row">
                                <span>Tổng thời lượng</span>
                                <strong><?= e((string) ($summary['total_duration'] ?? 0)) ?> phút</strong>
                            </div>
                            <div class="bk-confirm-row">
                                <span>Tổng tiền</span>
                                <strong><?= e($selectedPrice) ?></strong>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="<?= e(BASE_URL . '/booking/create?step=3') ?>">
                        <?= csrfInput() ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="bk-box">
                                    <label for="booking_date" class="form-label fw-bold">Chọn ngày</label>
                                    <input
                                        type="date"
                                        id="booking_date"
                                        name="booking_date"
                                        class="form-control booking-form-control"
                                        value="<?= e($selectedDate) ?>"
                                        min="<?= e(date('Y-m-d')) ?>"
                                        required
                                    >
                                    <div class="form-text">Chỉ có thể chọn từ ngày hiện tại trở đi.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="bk-box h-100">
                                    <label for="start_time" class="form-label fw-bold">Khung giờ khả dụng</label>
                                    <select name="start_time" id="start_time" class="form-select booking-form-control" required>
                                        <option value="">-- Chọn giờ --</option>
                                        <?php if ($selectedTime !== ''): ?>
                                            <option value="<?= e($selectedTime) ?>" selected><?= e($selectedTime) ?></option>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text">Khung giờ sẽ được giữ tạm sau khi bạn chọn.</div>
                                </div>
                            </div>
                        </div>

                        <div class="bk-actions">
                            <div class="bk-actions-side">
                                <a href="<?= e(BASE_URL . '/booking/create?step=2') ?>" class="bk-btn bk-btn-secondary">
                                    Quay lại
                                </a>
                            </div>

                            <div class="bk-actions-side">
                                <button type="submit" class="bk-btn bk-btn-primary">
                                    Tiếp tục
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <aside>
                <div class="bk-summary">
                    <h3 class="bk-summary-title">Tóm tắt lịch hẹn</h3>

                    <div class="bk-summary-row">
                        <span>Salon</span>
                        <strong><?= e($salon['name'] ?? 'Chưa chọn') ?></strong>
                    </div>

                    <div class="bk-summary-row">
                        <span>Dịch vụ</span>
                        <strong><?= e($selectedServiceText) ?></strong>
                    </div>

                    <div class="bk-summary-row">
                        <span>Nhân viên</span>
                        <strong><?= e((string) ($staff['name'] ?? 'Chưa chọn')) ?></strong>
                    </div>

                    <div class="bk-summary-row">
                        <span>Ngày giờ</span>
                        <strong><?= $selectedDate !== '' && $selectedTime !== '' ? e($selectedDate . ' ' . $selectedTime) : 'Chưa chọn' ?></strong>
                    </div>

                    <div class="bk-summary-row is-total">
                        <span>Tạm tính</span>
                        <strong><?= e($selectedPrice) ?></strong>
                    </div>
                </div>
            </aside>
        </div>
    </div>
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

        if (!data.success || !Array.isArray(data.slots) || data.slots.length === 0) {
            startTimeSelect.innerHTML = '<option value="">Không có khung giờ phù hợp</option>';
        }
    } catch (error) {
        startTimeSelect.innerHTML = '<option value="">Không tải được slot</option>';
    }
});

startTimeSelect.addEventListener('change', async function () {
    const bookingDate = bookingDateInput.value;
    const startTime = this.value;

    if (!bookingDate || !startTime) {
        return;
    }

    const formData = new FormData();
    formData.append('staff_id', staffId);
    formData.append('booking_date', bookingDate);
    formData.append('start_time', startTime);
    formData.append('duration', duration);

    try {
        const response = await fetch('<?= e(BASE_URL) ?>/api/hold-slot.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (!data.success) {
            alert(data.message || 'Không giữ được khung giờ.');
            this.value = '';
        }
    } catch (error) {
        alert('Không giữ được khung giờ. Vui lòng thử lại.');
        this.value = '';
    }
});
</script>
