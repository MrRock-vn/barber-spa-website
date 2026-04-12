<?php

declare(strict_types=1);
?>

<div class="container" style="max-width: 620px;">
    <div class="mb-4 text-center">
        <h2 class="page-section-title">Đăng ký tài khoản</h2>
        <div class="page-section-subtitle">Tạo tài khoản mới để đặt lịch nhanh hơn</div>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="<?= e(BASE_URL . '/register') ?>">
                <?= csrfInput() ?>

                <div class="mb-3">
                    <label class="form-label">Họ và tên</label>
                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="<?= e($_POST['name'] ?? '') ?>"
                        required
                    >
                </div>

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

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nhập lại mật khẩu</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100">Đăng ký</button>
            </form>

            <div class="mt-3 text-center">
                <a href="<?= e(BASE_URL . '/login') ?>">Đã có tài khoản? Đăng nhập</a>
            </div>
        </div>
    </div>
</div>