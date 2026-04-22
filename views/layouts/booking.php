<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? APP_NAME;
$content = $content ?? '';
$salonId = isset($salon['id']) ? (int) $salon['id'] : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(BASE_URL . '/public/css/style.css') ?>" rel="stylesheet">
    <style>
        .bk-page {
            background: linear-gradient(180deg, #f8f9fc 0%, #ffffff 100%);
            min-height: 100vh;
            padding: 32px 0 48px;
        }
        .bk-shell {
            max-width: 1200px;
        }
        .bk-progress {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }
        .bk-step {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid #e9eaf0;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(18, 38, 63, 0.04);
        }
        .bk-step.is-active {
            border-color: #ff5a5f;
            background: linear-gradient(135deg, #fff 0%, #fff5f5 100%);
        }
        .bk-step-index {
            width: 34px;
            height: 34px;
            flex-shrink: 0;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f1f3f9;
            color: #1d2433;
            font-weight: 700;
        }
        .bk-step.is-active .bk-step-index {
            background: #ff5a5f;
            color: #ffffff;
        }
        .bk-step-text {
            font-weight: 600;
            color: #283044;
        }
        .bk-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 24px;
            align-items: start;
        }
        .bk-panel,
        .bk-summary {
            background: #ffffff;
            border: 1px solid #eceef5;
            border-radius: 24px;
            box-shadow: 0 18px 40px rgba(19, 31, 55, 0.06);
        }
        .bk-panel {
            padding: 28px;
        }
        .bk-summary {
            padding: 22px;
            position: sticky;
            top: 24px;
        }
        .bk-head {
            margin-bottom: 22px;
        }
        .bk-kicker {
            margin: 0 0 8px;
            color: #ff5a5f;
            font-weight: 700;
            font-size: 14px;
        }
        .bk-title {
            margin: 0 0 8px;
            font-size: 42px;
            line-height: 1.1;
            color: #182033;
        }
        .bk-subtitle {
            margin: 0;
            color: #677189;
            font-size: 17px;
        }
        .bk-summary-title {
            margin: 0 0 18px;
            font-size: 20px;
            color: #182033;
        }
        .bk-summary-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px dashed #e7eaf1;
            color: #5f6b85;
        }
        .bk-summary-row strong {
            color: #1b2234;
            text-align: right;
        }
        .bk-summary-row.is-total {
            border-bottom: 0;
            padding-top: 18px;
            margin-top: 4px;
        }
        .bk-summary-row.is-total strong {
            color: #ff5a5f;
            font-size: 20px;
        }
        .bk-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }
        .bk-choice {
            position: relative;
        }
        .bk-choice > input[type="radio"],
        .bk-choice > input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .bk-choice-card {
            display: block;
            min-height: 160px;
            padding: 20px;
            border: 1.5px solid #e7eaf2;
            border-radius: 20px;
            background: #ffffff;
            cursor: pointer;
            transition: 0.2s ease;
        }
        .bk-choice-card:hover {
            border-color: #ffb7ba;
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(255, 90, 95, 0.08);
        }
        .bk-choice > input:checked + .bk-choice-card {
            border-color: #ff5a5f;
            background: linear-gradient(135deg, #fff 0%, #fff5f5 100%);
            box-shadow: 0 14px 30px rgba(255, 90, 95, 0.12);
        }
        .bk-choice-top {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .bk-choice-title {
            margin: 0;
            font-size: 24px;
            color: #182033;
        }
        .bk-choice-price {
            font-weight: 800;
            color: #ff5a5f;
            white-space: nowrap;
        }
        .bk-choice-desc {
            margin: 0 0 14px;
            color: #667189;
            line-height: 1.6;
        }
        .bk-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .bk-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 8px 12px;
            background: #f4f6fb;
            color: #33405c;
            font-size: 13px;
            font-weight: 600;
        }
        .bk-staff {
            display: flex;
            gap: 14px;
            align-items: center;
        }
        .bk-avatar {
            width: 64px;
            height: 64px;
            border-radius: 999px;
            overflow: hidden;
            background: #f3f5fa;
            flex-shrink: 0;
        }
        .bk-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .bk-avatar-fallback {
            width: 100%;
            height: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #33405c;
        }
        .bk-staff-name {
            margin: 0 0 6px;
            font-size: 20px;
            color: #182033;
        }
        .bk-staff-note {
            margin: 0;
            color: #6a748c;
        }
        .bk-box {
            border: 1px solid #e7eaf2;
            border-radius: 18px;
            padding: 18px;
            background: #fff;
        }
        .bk-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 28px;
            flex-wrap: wrap;
        }
        .bk-actions-side {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .bk-btn {
            appearance: none;
            border-radius: 14px;
            padding: 13px 18px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s ease;
        }
        .bk-btn-primary {
            border: 0;
            background: #ff5a5f;
            color: #fff;
        }
        .bk-btn-primary:hover {
            background: #ec4c51;
            color: #fff;
        }
        .bk-btn-secondary {
            background: #f3f5fa;
            color: #24314d;
            border: 1px solid #e5e8f0;
        }
        .bk-btn-secondary:hover {
            background: #e9edf5;
            color: #24314d;
        }
        .bk-confirm {
            border: 1px solid #e7eaf2;
            border-radius: 20px;
            padding: 20px;
            background: #fbfcff;
        }
        .bk-confirm-list {
            display: grid;
            gap: 14px;
        }
        .bk-confirm-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 12px;
            border-bottom: 1px dashed #e2e7f0;
        }
        .bk-confirm-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }
        @media (max-width: 991px) {
            .bk-layout {
                grid-template-columns: 1fr;
            }
            .bk-summary {
                position: static;
            }
            .bk-grid,
            .bk-progress {
                grid-template-columns: 1fr;
            }
            .bk-title {
                font-size: 34px;
            }
        }
    </style>
</head>
<body class="booking-flow-body d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark booking-flow-nav">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= e(BASE_URL . '/home') ?>"><?= e(APP_NAME) ?></a>
        <div class="d-flex gap-2">
            <?php if ($salonId > 0): ?>
                <a class="btn btn-outline-light btn-sm" href="<?= e(BASE_URL . '/salon/' . $salonId) ?>">Salon</a>
            <?php endif; ?>
            <a class="btn btn-outline-light btn-sm" href="<?= e(BASE_URL . '/my-bookings') ?>">Lịch hẹn</a>
        </div>
    </div>
</nav>

<main class="flex-grow-1">
    <?= $content ?>
</main>

<footer class="booking-flow-footer mt-auto">
    <div class="container py-3 text-center text-muted small">
        © <?= e((string) date('Y')) ?> <?= e(APP_NAME) ?>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function cleanupStaleModalBackdrop() {
    const activeModals = document.querySelectorAll('.modal.show');
    if (activeModals.length > 0) {
        return;
    }

    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
    document.querySelectorAll('.modal-backdrop').forEach((element) => element.remove());
}

document.addEventListener('DOMContentLoaded', cleanupStaleModalBackdrop);
window.addEventListener('pageshow', cleanupStaleModalBackdrop);

document.addEventListener('submit', function (event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const confirmMessage = form.dataset.confirm;
    if (confirmMessage && !window.confirm(confirmMessage)) {
        event.preventDefault();
        return;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton && !submitButton.disabled) {
        submitButton.dataset.originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Đang xử lý...';
    }
});
</script>
</body>
</html>
