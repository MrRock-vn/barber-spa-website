<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(BASE_URL . '/public/css/style.css') ?>">
</head>
<body>
<section class="auth-page">
    <div class="container py-5">
        <div class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-side">
                    <div class="auth-brand">BARBER SPA</div>
                    <h3 class="auth-side-title">Đặt lại mật khẩu</h3>
                    <p class="auth-side-text">Thiết lập mật khẩu mới để bạn tiếp tục đặt lịch và sử dụng dịch vụ an toàn.</p>
                    <ul class="auth-features">
                        <li class="auth-feature-item">Mật khẩu bảo mật cao</li>
                        <li class="auth-feature-item">Tiếp tục đặt lịch dễ dàng</li>
                    </ul>
                </div>
                <div class="auth-card-body">
                    <form method="POST" action="<?= e(BASE_URL . '/reset-password?token=' . urlencode($_GET['token'] ?? '')) ?>">
                        <?= csrfInput() ?>

                        <input type="hidden" name="token" value="<?= e($_GET['token'] ?? '') ?>">

                        <div class="mb-3 auth-form-group">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" name="password" class="form-control auth-form-control" placeholder="Tối thiểu 8 ký tự" required>
                        </div>

                        <div class="mb-4 auth-form-group">
                            <label class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" name="password_confirmation" class="form-control auth-form-control" placeholder="Nhập lại mật khẩu" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">Cập nhật mật khẩu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>