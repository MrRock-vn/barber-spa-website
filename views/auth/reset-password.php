<?php

declare(strict_types=1);
?>

<div class="container" style="max-width: 520px;">
    <div class="mb-4 text-center">
        <h2 class="page-section-title">Đặt lại mật khẩu</h2>
        <div class="page-section-subtitle">Nhập mật khẩu mới cho tài khoản của bạn</div>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="<?= e(BASE_URL . '/reset-password') ?>">
                <?= csrfInput() ?>

                <input type="hidden" name="token" value="<?= e($_GET['token'] ?? $_POST['token'] ?? '') ?>">

                <div class="mb-3">
                    <label class="form-label">Mật khẩu mới</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nhập lại mật khẩu mới</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-danger w-100">Cập nhật mật khẩu</button>
            </form>

            <div class="mt-3 text-center">
                <a href="<?= e(BASE_URL . '/login') ?>">Quay lại đăng nhập</a>
            </div>
        </div>
    </div>
</div>