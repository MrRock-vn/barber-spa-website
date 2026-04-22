<?php declare(strict_types=1); ?>
<?php
$summary = (array) ($wizard['summary'] ?? []);
$selectedServices = (array) ($summary['services'] ?? []);
$selectedServiceNames = array_map(
    static fn (array $service): string => (string) ($service['name'] ?? ''),
    $selectedServices
);
$selectedServiceText = empty($selectedServiceNames) ? 'Chưa chọn' : implode(', ', array_filter($selectedServiceNames));
$selectedStaffId = (int) ($wizard['staff_id'] ?? 0);
$selectedPrice = !empty($summary['total_price']) ? formatMoney((float) $summary['total_price']) : '0đ';
$selectedStaffName = 'Chưa chọn';

foreach ($staffList as $member) {
    if ((int) $member['id'] === $selectedStaffId) {
        $selectedStaffName = (string) ($member['name'] ?? 'Chưa chọn');
        break;
    }
}
?>
<div class="bk-page">
    <div class="bk-shell container">
        <div class="bk-progress">
            <div class="bk-step">
                <span class="bk-step-index">1</span>
                <span class="bk-step-text">Dịch vụ</span>
            </div>
            <div class="bk-step is-active">
                <span class="bk-step-index">2</span>
                <span class="bk-step-text">Nhân viên</span>
            </div>
            <div class="bk-step">
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
                        <p class="bk-kicker">Bước 2 trong 4</p>
                        <h1 class="bk-title">Chọn nhân viên</h1>
                        <p class="bk-subtitle"><?= e($salon['name']) ?></p>
                    </div>

                    <form method="POST" action="<?= e(BASE_URL . '/booking/create?step=2') ?>">
                        <?= csrfInput() ?>

                        <?php if (empty($staffList)): ?>
                            <div class="ui-empty-state">
                                <div class="ui-empty-icon">N</div>
                                <h5 class="fw-bold">Chưa có nhân viên</h5>
                                <p class="text-muted mb-0">Salon này hiện chưa có nhân viên khả dụng.</p>
                            </div>
                        <?php else: ?>
                            <div class="bk-grid">
                                <?php foreach ($staffList as $member): ?>
                                    <?php $memberId = (int) $member['id']; ?>
                                    <div class="bk-choice">
                                        <input
                                            type="radio"
                                            id="staff_<?= $memberId ?>"
                                            name="staff_id"
                                            value="<?= $memberId ?>"
                                            <?= $selectedStaffId === $memberId ? 'checked' : '' ?>
                                            required
                                        >
                                        <label class="bk-choice-card" for="staff_<?= $memberId ?>">
                                            <div class="bk-staff">
                                                <div class="bk-avatar">
                                                    <?php if (!empty($member['avatar'])): ?>
                                                        <img src="<?= e(BASE_URL . '/' . ltrim((string) $member['avatar'], '/')) ?>" alt="<?= e((string) $member['name']) ?>">
                                                    <?php else: ?>
                                                        <div class="bk-avatar-fallback"><?= e(strtoupper(substr((string) $member['name'], 0, 1))) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <h4 class="bk-staff-name"><?= e((string) $member['name']) ?></h4>
                                                    <p class="bk-staff-note"><?= e((string) ($member['specialties'] ?? 'Nhân viên tạo kiểu chuyên nghiệp')) ?></p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="bk-actions">
                            <div class="bk-actions-side">
                                <a href="<?= e(BASE_URL . '/booking/create?salon_id=' . $salon['id'] . '&step=1') ?>" class="bk-btn bk-btn-secondary">
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
                        <strong><?= e($selectedStaffName) ?></strong>
                    </div>

                    <div class="bk-summary-row">
                        <span>Ngày giờ</span>
                        <strong>Chưa chọn</strong>
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
