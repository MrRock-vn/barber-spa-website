<?php

declare(strict_types=1);
?>

<section class="auth-page">
    <div class="container py-5">
        <div class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-side">
                    <div class="auth-brand">BARBER SPA</div>
                    <h3 class="auth-side-title">Bắt đầu ngay hôm nay</h3>
                    <p class="auth-side-text">Tạo tài khoản để truy cập ưu đãi, quản lý profile và đặt dịch vụ barber chuyên nghiệp.</p>
                    <ul class="auth-features">
                        <li class="auth-feature-item">Đặt lịch nhanh chóng</li>
                        <li class="auth-feature-item">Lưu thông tin tức thì</li>
                        <li class="auth-feature-item">Theo dõi lịch sử booking</li>
                    </ul>
                </div>
                <div class="auth-card-body">
                    <form method="POST" action="<?= e(BASE_URL . '/register') ?>">
                        <?= csrfInput() ?>

                        <div class="mb-3 auth-form-group">
                            <label class="form-label">Họ và tên</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control auth-form-control"
                                value="<?= e($_POST['name'] ?? '') ?>"
                                placeholder="Nguyễn Văn A"
                                required
                            >
                        </div>

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

                        <div class="row g-3 mb-3">
                            <div class="col-md-6 auth-form-group">
                                <label class="form-label">Mật khẩu</label>
                                <input
                                    type="password"
                                    name="password"
                                    class="form-control auth-form-control"
                                    placeholder="Tối thiểu 8 ký tự"
                                    required
                                    autocomplete="new-password"
                                >
                            </div>

                            <div class="col-md-6 auth-form-group">
                                <label class="form-label">Nhập lại mật khẩu</label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    class="form-control auth-form-control"
                                    placeholder="Nhập lại mật khẩu"
                                    required
                                >
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">Đăng ký</button>
                    </form>

                    <div class="auth-footer">
                        <a href="<?= e(BASE_URL . '/login') ?>">Đã có tài khoản? Đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>