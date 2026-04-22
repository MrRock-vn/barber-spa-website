<?php declare(strict_types=1); ?>
<?php
$selectedServiceIds = array_map('intval', (array) ($wizard['service_ids'] ?? []));
$selectedServices = (array) ($wizard['summary']['services'] ?? []);
$selectedServiceNames = array_map(
    static fn (array $service): string => (string) ($service['name'] ?? ''),
    $selectedServices
);
$selectedServiceText = empty($selectedServiceNames) ? 'Chưa chọn' : implode(', ', array_filter($selectedServiceNames));
$selectedPrice = !empty($wizard['summary']['total_price']) ? formatMoney((float) $wizard['summary']['total_price']) : '0đ';
?>
<div class="bk-page">
    <div class="bk-shell container">
        <div class="bk-progress">
            <div class="bk-step is-active">
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
                        <p class="bk-kicker">Bước 1 trong 4</p>
                        <h1 class="bk-title">Chọn dịch vụ</h1>
                        <p class="bk-subtitle"><?= e($salon['name']) ?></p>
                    </div>

                    <form method="POST" action="<?= e(BASE_URL . '/booking/create?salon_id=' . $salon['id'] . '&step=1') ?>">
                        <?= csrfInput() ?>

                        <?php if (empty($services)): ?>
                            <div class="ui-empty-state">
                                <div class="ui-empty-icon">S</div>
                                <h5 class="fw-bold">Chưa có dịch vụ</h5>
                                <p class="text-muted mb-0">Salon này hiện chưa có dịch vụ khả dụng.</p>
                            </div>
                        <?php else: ?>
                            <div class="bk-grid">
                                <?php foreach ($services as $service): ?>
                                    <?php $serviceId = (int) $service['id']; ?>
                                    <div class="bk-choice">
                                        <input
                                            type="checkbox"
                                            id="service_<?= $serviceId ?>"
                                            name="service_ids[]"
                                            value="<?= $serviceId ?>"
                                            <?= in_array($serviceId, $selectedServiceIds, true) ? 'checked' : '' ?>
                                        >
                                        <label class="bk-choice-card" for="service_<?= $serviceId ?>">
                                            <div class="bk-choice-top">
                                                <h3 class="bk-choice-title"><?= e((string) $service['name']) ?></h3>
                                                <div class="bk-choice-price"><?= e(formatMoney((float) $service['price'])) ?></div>
                                            </div>

                                            <p class="bk-choice-desc">
                                                <?= e((string) ($service['description'] ?? 'Dịch vụ chăm sóc và tạo kiểu chuyên nghiệp.')) ?>
                                            </p>

                                            <div class="bk-badges">
                                                <span class="bk-badge"><?= e((string) $service['duration']) ?> phút</span>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="bk-actions">
                            <div class="bk-actions-side">
                                <a href="<?= e(BASE_URL . '/salon/' . $salon['id']) ?>" class="bk-btn bk-btn-secondary">
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
                        <strong>Chưa chọn</strong>
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
