<?php

declare(strict_types=1);
?>

<section class="auth-page">
    <div class="container py-5">
        <div class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-side">
                    <div class="auth-brand">BARBER SPA</div>
                    <h3 class="auth-side-title">Không nhớ mật khẩu?</h3>
                    <p class="auth-side-text">Chỉ cần nhập email và chúng tôi sẽ gửi liên kết khôi phục password đến bạn.</p>
                    <ul class="auth-features">
                        <li class="auth-feature-item">Bảo mật tài khoản tối ưu</li>
                        <li class="auth-feature-item">Khôi phục nhanh chóng</li>
                    </ul>
                </div>
                <div class="auth-card-body">
                    <form method="POST" action="<?= e(BASE_URL . '/forgot-password') ?>">
                        <?= csrfInput() ?>

                        <div class="mb-4 auth-form-group">
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

                        <button type="submit" class="btn btn-primary w-100 btn-lg">Gửi yêu cầu</button>
                    </form>

                    <div class="auth-footer">
                        <a href="<?= e(BASE_URL . '/login') ?>">Quay lại đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>