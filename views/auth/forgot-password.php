<?php

declare(strict_types=1);
?>

<div class="container" style="max-width: 520px;">
    <div class="mb-4 text-center">
        <h2 class="page-section-title">Quên mật khẩu</h2>
        <div class="page-section-subtitle">Nhập email để tạo yêu cầu đặt lại mật khẩu</div>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="<?= e(BASE_URL . '/forgot-password') ?>">
                <?= csrfInput() ?>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= e($_POST['email'] ?? '') ?>"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-dark w-100">Gửi yêu cầu</button>
            </form>

            <div class="mt-3 text-center">
                <a href="<?= e(BASE_URL . '/login') ?>">Quay lại đăng nhập</a>
            </div>
        </div>
    </div>
</div>