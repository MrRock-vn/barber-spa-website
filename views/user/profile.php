<?php

declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(BASE_URL . '/public/css/style.css') ?>">
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="profile-avatar">
                            <?= htmlspecialchars(strtoupper(substr($user['name'] ?? 'B', 0, 1))) ?>
                        </div>
                        <div>
                            <h2 class="mb-1">Hồ sơ của bạn</h2>
                            <p class="mb-0">Quản lý thông tin cá nhân và lịch hẹn đến salon.</p>
                        </div>
                    </div>
                </div>
                <div class="profile-card-body">
                    <?php if (!empty($user)): ?>
                        <div class="row profile-details">
                            <div class="col-sm-4 profile-label">Tên</div>
                            <div class="col-sm-8 profile-value"><?= htmlspecialchars($user['name'] ?? '') ?></div>
                        </div>
                        <div class="row profile-details">
                            <div class="col-sm-4 profile-label">Email</div>
                            <div class="col-sm-8 profile-value"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                        </div>
                        <div class="row profile-details">
                            <div class="col-sm-4 profile-label">Số điện thoại</div>
                            <div class="col-sm-8 profile-value"><?= htmlspecialchars($user['phone'] ?? 'Chưa cập nhật') ?></div>
                        </div>
                        <div class="row profile-details">
                            <div class="col-sm-4 profile-label">Địa chỉ</div>
                            <div class="col-sm-8 profile-value"><?= htmlspecialchars($user['address'] ?? 'Chưa cập nhật') ?></div>
                        </div>
                        <div class="row profile-details">
                            <div class="col-sm-4 profile-label">Thành phố</div>
                            <div class="col-sm-8 profile-value"><?= htmlspecialchars($user['city'] ?? 'Chưa cập nhật') ?></div>
                        </div>
                        <div class="row profile-details">
                            <div class="col-sm-4 profile-label">Quận</div>
                            <div class="col-sm-8 profile-value"><?= htmlspecialchars($user['district'] ?? 'Chưa cập nhật') ?></div>
                        </div>
                        <div class="profile-meta">
                            <span class="profile-badge">Vai trò: <?= htmlspecialchars($user['role'] ?? 'Khách hàng') ?></span>
                            <span class="profile-badge"><?= !empty($user['is_active']) ? 'Đang hoạt động' : 'Tạm khóa' ?></span>
                        </div>

                        <div class="mt-4 d-flex flex-wrap gap-3">
                            <a href="<?= e(BASE_URL . '/edit-profile') ?>" class="btn btn-primary">Chỉnh sửa hồ sơ</a>
                            <a href="<?= e(BASE_URL . '/my-bookings') ?>" class="btn btn-outline-secondary">Xem lịch hẹn</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>