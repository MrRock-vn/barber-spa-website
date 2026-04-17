<?php

declare(strict_types=1);
?>

<section class="auth-page">
    <div class="container py-5">
        <div class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-side">
                    <div class="auth-brand">BARBER SPA</div>
                    <h3 class="auth-side-title">Chào mừng trở lại</h3>
                    <p class="auth-side-text">Đăng nhập để quản lý lịch hẹn nhanh gọn, ưu đãi trực tiếp và thông tin cá nhân an toàn.</p>
                    <ul class="auth-features">
                        <li class="auth-feature-item">Quản lý lịch hẹn mọi lúc</li>
                        <li class="auth-feature-item">Lưu thông tin cá nhân</li>
                        <li class="auth-feature-item">Thanh toán nhanh và bảo mật</li>
                    </ul>
                </div>
                <div class="auth-card-body">
                    <form method="POST" action="<?= e(BASE_URL . '/login') ?>">
                        <?= csrfInput() ?>

                        <div class="mb-3 auth-form-group">
                            <label class="form-label">Email</label>
                            <input
                                type="email"
                                name="email"
                                class="form-control auth-form-control"
                                value="<?= e($_POST['email'] ?? '') ?>"
                                placeholder="mail@domain.com"
                                required
                                autocomplete="email"
                            >
                        </div>

                        <div class="mb-4 auth-form-group">
                            <label class="form-label">Mật khẩu</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control auth-form-control"
                                placeholder="Nhập mật khẩu"
                                required
                                autocomplete="current-password"
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">Đăng nhập</button>
                    </form>

                    <div class="auth-footer">
                        <a href="<?= e(BASE_URL . '/forgot-password') ?>">Quên mật khẩu?</a>
                        <a href="<?= e(BASE_URL . '/register') ?>">Chưa có tài khoản? Đăng ký</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>