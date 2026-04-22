<?php declare(strict_types=1); ?>
<?php
$selectedServices = (array) ($summary['services'] ?? []);
$selectedServiceNames = array_map(
    static fn (array $service): string => (string) ($service['name'] ?? ''),
    $selectedServices
);
$selectedServiceText = empty($selectedServiceNames) ? 'Chưa chọn' : implode(', ', array_filter($selectedServiceNames));
$selectedPrice = !empty($summary['total_price']) ? formatMoney((float) $summary['total_price']) : '0đ';
$dateTimeText = formatDate((string) ($_SESSION['booking_wizard']['booking_date'] ?? '')) . ' · ' .
    formatTime((string) ($_SESSION['booking_wizard']['start_time'] ?? '')) . ' - ' .
    formatTime((string) ($_SESSION['booking_wizard']['end_time'] ?? ''));
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
            <div class="bk-step">
                <span class="bk-step-index">3</span>
                <span class="bk-step-text">Ngày giờ</span>
            </div>
            <div class="bk-step is-active">
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
                        <p class="bk-kicker">Bước 4 trong 4</p>
                        <h1 class="bk-title">Xác nhận đặt lịch</h1>
                        <p class="bk-subtitle">Kiểm tra thông tin trước khi tạo booking</p>
                    </div>

                    <div class="bk-confirm">
                        <div class="bk-confirm-list">
                            <div class="bk-confirm-row">
                                <span>Salon</span>
                                <strong><?= e($salon['name']) ?></strong>
                            </div>
                            <div class="bk-confirm-row">
                                <span>Dịch vụ</span>
                                <strong><?= e($selectedServiceText) ?></strong>
                            </div>
                            <div class="bk-confirm-row">
                                <span>Nhân viên</span>
                                <strong><?= e((string) ($staff['name'] ?? '')) ?></strong>
                            </div>
                            <div class="bk-confirm-row">
                                <span>Thời gian</span>
                                <strong><?= e($dateTimeText) ?></strong>
                            </div>
                            <div class="bk-confirm-row">
                                <span>Tổng tiền</span>
                                <strong><?= e($selectedPrice) ?></strong>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="<?= e(BASE_URL . '/booking/create?step=4') ?>" class="mt-4">
                        <?= csrfInput() ?>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Phương thức thanh toán</label>
                            <select name="payment_method" class="form-select booking-form-control" required>
                                <option value="at_counter">Thanh toán tại quầy</option>
                                <option value="online">Thanh toán online</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghi chú</label>
                            <textarea name="notes" class="form-control rounded-4" rows="4" placeholder="Nhập ghi chú nếu có..."></textarea>
                        </div>

                        <div class="bk-actions">
                            <div class="bk-actions-side">
                                <a href="<?= e(BASE_URL . '/booking/create?step=3') ?>" class="bk-btn bk-btn-secondary">
                                    Quay lại
                                </a>
                            </div>

                            <div class="bk-actions-side">
                                <button type="submit" class="bk-btn bk-btn-primary">
                                    Xác nhận đặt lịch
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
                        <strong><?= e($dateTimeText) ?></strong>
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
