<?php

declare(strict_types=1);
?>

<div class="container" style="max-width: 520px;">
    <div class="mb-4 text-center">
        <h2 class="page-section-title">Đăng nhập</h2>
        <div class="page-section-subtitle">Truy cập tài khoản Barber Spa của bạn</div>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="<?= e(BASE_URL . '/login') ?>">
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

                <div class="mb-3">
                    <label class="form-label">Mật khẩu</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-dark w-100">Đăng nhập</button>
            </form>

            <div class="mt-3 d-flex justify-content-between flex-wrap gap-2">
                <a href="<?= e(BASE_URL . '/forgot-password') ?>">Quên mật khẩu?</a>
                <a href="<?= e(BASE_URL . '/register') ?>">Chưa có tài khoản? Đăng ký</a>
            </div>
        </div>
    </div>
</div>