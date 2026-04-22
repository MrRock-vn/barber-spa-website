<?php

declare(strict_types=1);
?>
<section class="auth-page">
    <div class="container py-5">
        <div class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-header">
                    <div class="auth-brand">BARBER SPA</div>
                    <h2 class="page-section-title">Chỉnh sửa hồ sơ</h2>
                    <p class="page-section-subtitle">Cập nhật thông tin để nhận ưu đãi và booking tốt hơn.</p>
                </div>
                <div class="auth-card-body">
                    <form method="POST">
                        <?= csrfInput() ?>
                        <div class="mb-3 auth-form-group">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" name="name" class="form-control auth-form-control"
                                   value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3 auth-form-group">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control auth-form-control"
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>

                        <div class="mb-3 auth-form-group">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="address" class="form-control auth-form-control"
                                   value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 auth-form-group">
                                <label class="form-label">Thành phố</label>
                                <input type="text" name="city" class="form-control auth-form-control"
                                       value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 auth-form-group">
                                <label class="form-label">Quận</label>
                                <input type="text" name="district" class="form-control auth-form-control"
                                       value="<?= htmlspecialchars($user['district'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="auth-actions d-flex flex-wrap gap-3 mt-4">
                            <a href="<?= e(BASE_URL . '/my-profile') ?>" class="btn btn-outline-secondary">Hủy</a>
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
