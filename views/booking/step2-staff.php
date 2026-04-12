<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch - Bước 2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="fw-bold mb-3">Bước 2: Chọn nhân viên</h2>
    <p class="text-muted"><?= e($salon['name']) ?></p>

    <?php if (hasFlash('error')): ?>
        <div class="alert alert-danger"><?= e(getFlash('error')) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= e(BASE_URL . '/booking/create?step=2') ?>">
        <?= csrfInput() ?>

        <div class="row g-3">
            <?php foreach ($staffList as $member): ?>
                <div class="col-md-6">
                    <label class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="staff_id"
                                    value="<?= e((string) $member['id']) ?>"
                                    id="staff_<?= e((string) $member['id']) ?>"
                                    required
                                >
                                <label class="form-check-label w-100" for="staff_<?= e((string) $member['id']) ?>">
                                    <strong><?= e($member['name']) ?></strong>
                                    <div class="text-muted small mt-2"><?= e($member['phone']) ?></div>
                                    <div class="small mt-2">Chuyên môn: <?= e((string) $member['specialties']) ?></div>
                                </label>
                            </div>
                        </div>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="<?= e(BASE_URL . '/booking/create?salon_id=' . $salon['id'] . '&step=1') ?>" class="btn btn-outline-secondary">Quay lại</a>
            <button type="submit" class="btn btn-danger">Tiếp tục</button>
        </div>
    </form>
</div>
</body>
</html>