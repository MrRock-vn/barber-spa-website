<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch - Bước 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="fw-bold mb-3">Bước 1: Chọn dịch vụ</h2>
    <p class="text-muted"><?= e($salon['name']) ?></p>

    <?php if (hasFlash('error')): ?>
        <div class="alert alert-danger"><?= e(getFlash('error')) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= e(BASE_URL . '/booking/create?salon_id=' . $salon['id'] . '&step=1') ?>">
        <?= csrfInput() ?>

        <div class="row g-3">
            <?php foreach ($services as $service): ?>
                <div class="col-md-6">
                    <label class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="service_ids[]"
                                    value="<?= e((string) $service['id']) ?>"
                                    id="service_<?= e((string) $service['id']) ?>"
                                >
                                <label class="form-check-label w-100" for="service_<?= e((string) $service['id']) ?>">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= e($service['name']) ?></strong>
                                        <span class="badge bg-dark"><?= e(formatMoney((float) $service['price'])) ?></span>
                                    </div>
                                    <div class="text-muted small mt-2"><?= e($service['description']) ?></div>
                                    <div class="small mt-2">⏱ <?= e((string) $service['duration']) ?> phút</div>
                                </label>
                            </div>
                        </div>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="<?= e(BASE_URL . '/salon/' . $salon['id']) ?>" class="btn btn-outline-secondary">Quay lại</a>
            <button type="submit" class="btn btn-danger">Tiếp tục</button>
        </div>
    </form>
</div>
</body>
</html>