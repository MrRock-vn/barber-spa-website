<?php
// ============================================================
// public/login.php  — AUTH-01
// Người làm: Nguyễn Văn Quang
// Branch: feature/auth
// ============================================================
session_start();
require_once __DIR__ . '/../config/db.php';

// Nếu đã đăng nhập rồi → redirect về trang chủ
if (currentUser()) {
    header('Location: /index.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    // --- Validate cơ bản ---
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không đúng định dạng.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Email hoặc mật khẩu không đúng.';

        } elseif (!$user['is_active']) {
            $error = 'Tài khoản đã bị đình chỉ. Vui lòng liên hệ Admin.';

        } elseif ($user['locked_until'] && new DateTime() < new DateTime($user['locked_until'])) {
            // Brute-force: tài khoản đang bị khóa tạm thời
            $remaining = (new DateTime($user['locked_until']))->diff(new DateTime())->i;
            $error = "Tài khoản bị khóa tạm thời do nhập sai quá nhiều lần. Thử lại sau {$remaining} phút.";

        } elseif (!password_verify($password, $user['password'])) {
            // Tăng số lần nhập sai
            $attempts = $user['login_attempts'] + 1;
            $lockUntil = null;
            if ($attempts >= 5) {
                $lockUntil = date('Y-m-d H:i:s', strtotime('+1 minute'));
                $attempts  = 0; // reset sau khi khóa
            }
            $db->prepare('UPDATE users SET login_attempts=?, locked_until=? WHERE id=?')
               ->execute([$attempts, $lockUntil, $user['id']]);
            $error = 'Email hoặc mật khẩu không đúng.';

        } else {
            // ✅ Đăng nhập thành công
            $db->prepare('UPDATE users SET login_attempts=0, locked_until=NULL, last_login_at=NOW(), login_ip=? WHERE id=?')
               ->execute([$_SERVER['REMOTE_ADDR'], $user['id']]);

            $_SESSION['user'] = [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ];

            // Remember Me: set cookie 7 ngày
            if ($remember) {
                session_set_cookie_params(7 * 24 * 3600);
                session_regenerate_id(true);
            }

            // Redirect theo role
            $redirect = $_GET['redirect'] ?? null;
            if ($redirect) {
                header('Location: ' . $redirect);
            } elseif ($user['role'] === 'admin') {
                header('Location: /admin/dashboard.php');
            } elseif ($user['role'] === 'owner') {
                header('Location: /owner/dashboard.php');
            } else {
                header('Location: /index.php');
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập — Barber & Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .auth-card {
            max-width: 420px; margin: 80px auto;
            background: #fff; border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            padding: 40px 36px;
        }
        .auth-logo { font-size: 1.6rem; font-weight: 700; color: #1a1a2e; margin-bottom: 8px; }
        .auth-logo span { color: #e94560; }
        .btn-primary { background: #e94560; border-color: #e94560; }
        .btn-primary:hover { background: #c73652; border-color: #c73652; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-logo text-center mb-4">✂ Barber<span>&Spa</span></div>
    <h5 class="mb-4 text-center">Đăng nhập tài khoản</h5>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="loginForm">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($email) ?>"
                   placeholder="example@gmail.com" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label d-flex justify-content-between">
                Mật khẩu
                <a href="/forgot-password.php" class="text-decoration-none small">Quên mật khẩu?</a>
            </label>
            <div class="input-group">
                <input type="password" name="password" id="passwordInput"
                       class="form-control" placeholder="••••••••" required minlength="8">
                <button class="btn btn-outline-secondary" type="button"
                        onclick="togglePwd()">👁</button>
            </div>
        </div>
        <div class="mb-4 form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Ghi nhớ đăng nhập (7 ngày)</label>
        </div>
        <button type="submit" class="btn btn-primary w-100" id="submitBtn">
            <span id="btnText">Đăng nhập</span>
            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none"></span>
        </button>
    </form>

    <hr class="my-4">
    <p class="text-center mb-0 small">
        Chưa có tài khoản? <a href="/register.php">Đăng ký ngay</a>
    </p>
</div>

<script>
function togglePwd() {
    const input = document.getElementById('passwordInput');
    input.type = input.type === 'password' ? 'text' : 'password';
}
document.getElementById('loginForm').addEventListener('submit', function() {
    document.getElementById('btnText').textContent = 'Đang xử lý...';
    document.getElementById('btnSpinner').classList.remove('d-none');
    document.getElementById('submitBtn').disabled = true;
});
</script>
</body>
</html>
