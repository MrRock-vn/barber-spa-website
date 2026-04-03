<?php
// ============================================================
// public/reset-password.php  — AUTH-01
// Người làm: Nguyễn Văn Quang
// Branch: feature/auth
// ============================================================
session_start();
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$db    = getDB();
$token = trim($_GET['token'] ?? '');
$error = '';
$done  = false;

// --- Kiểm tra token hợp lệ ---
if (empty($token)) {
    header('Location: /forgot-password.php');
    exit;
}

$stmt = $db->prepare('SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1');
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    $tokenInvalid = true; // hiện thông báo lỗi trong HTML
}

// --- Xử lý đổi mật khẩu ---
if (!isset($tokenInvalid) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    if (strlen($password) < 8) {
        $error = 'Mật khẩu phải có ít nhất 8 ký tự.';
    } elseif ($password !== $confirm) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        // Cập nhật mật khẩu mới
        $db->prepare('UPDATE users SET password = ?, login_attempts = 0, locked_until = NULL WHERE email = ?')
           ->execute([$hash, $reset['email']]);

        // Xóa token đã dùng
        $db->prepare('DELETE FROM password_resets WHERE token = ?')
           ->execute([$token]);

        // Xóa tất cả remember_token cũ của user này (bảo mật)
        $userStmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $userStmt->execute([$reset['email']]);
        $user = $userStmt->fetch();
        if ($user) {
            $db->prepare('DELETE FROM remember_tokens WHERE user_id = ?')
               ->execute([$user['id']]);
        }

        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu — Barber & Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .auth-card {
            max-width: 440px; margin: 80px auto;
            background: #fff; border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            padding: 40px 36px;
        }
        .auth-logo { font-size: 1.6rem; font-weight: 700; color: #1a1a2e; }
        .auth-logo span { color: #e94560; }
        .btn-primary { background: #e94560; border-color: #e94560; }
        .btn-primary:hover { background: #c73652; border-color: #c73652; }
        .password-strength { height: 4px; border-radius: 2px; transition: all .3s; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-logo text-center mb-4">✂ Barber<span>&Spa</span></div>
    <h5 class="mb-4 text-center">Đặt lại mật khẩu</h5>

    <?php if (isset($tokenInvalid)): ?>
        <!-- Token không hợp lệ hoặc hết hạn -->
        <div class="alert alert-danger">
            <strong>Link không hợp lệ hoặc đã hết hạn.</strong><br>
            <span class="small">Link chỉ có hiệu lực trong 1 giờ sau khi yêu cầu.</span>
        </div>
        <a href="/forgot-password.php" class="btn btn-primary w-100">Yêu cầu link mới</a>

    <?php elseif ($done): ?>
        <!-- Đổi mật khẩu thành công -->
        <div class="text-center mb-4">
            <div style="font-size:3rem">✅</div>
            <h6 class="mt-3">Mật khẩu đã được cập nhật!</h6>
            <p class="text-muted small">Tất cả phiên đăng nhập trước đó đã bị hủy.</p>
        </div>
        <a href="/login.php" class="btn btn-primary w-100">Đăng nhập ngay</a>

    <?php else: ?>
        <!-- Form đổi mật khẩu -->
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <p class="text-muted small mb-4">
            Đặt lại mật khẩu cho: <strong><?= htmlspecialchars($reset['email']) ?></strong>
        </p>

        <form method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="mb-3">
                <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="password" id="passwordInput"
                           class="form-control" placeholder="Tối thiểu 8 ký tự"
                           required minlength="8" autofocus
                           oninput="checkStrength(this.value)">
                    <button class="btn btn-outline-secondary" type="button"
                            onclick="togglePwd('passwordInput')">👁</button>
                </div>
                <div class="mt-2 bg-light rounded" style="height:4px">
                    <div id="strengthBar" class="password-strength" style="width:0%"></div>
                </div>
                <div id="strengthText" class="form-text"></div>
            </div>

            <div class="mb-4">
                <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="confirm" id="confirmInput"
                           class="form-control" placeholder="Nhập lại mật khẩu"
                           required oninput="checkConfirm()">
                    <button class="btn btn-outline-secondary" type="button"
                            onclick="togglePwd('confirmInput')">👁</button>
                </div>
                <div id="confirmFeedback" class="form-text"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Cập nhật mật khẩu</button>
        </form>
    <?php endif; ?>
</div>

<script>
function togglePwd(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}

function checkStrength(val) {
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let score  = 0;
    if (val.length >= 8)           score++;
    if (/[A-Z]/.test(val))         score++;
    if (/[0-9]/.test(val))         score++;
    if (/[^A-Za-z0-9]/.test(val))  score++;

    const levels = [
        { w: '0%',   color: '',        label: '' },
        { w: '25%',  color: '#e74c3c', label: 'Yếu' },
        { w: '50%',  color: '#e67e22', label: 'Trung bình' },
        { w: '75%',  color: '#f1c40f', label: 'Khá' },
        { w: '100%', color: '#27ae60', label: 'Mạnh' },
    ];
    bar.style.width      = levels[score].w;
    bar.style.background = levels[score].color;
    text.textContent     = val.length ? levels[score].label : '';
    text.style.color     = levels[score].color;
}

function checkConfirm() {
    const pw = document.getElementById('passwordInput').value;
    const cf = document.getElementById('confirmInput').value;
    const fb = document.getElementById('confirmFeedback');
    if (!cf) { fb.textContent = ''; return; }
    if (pw === cf) {
        fb.textContent = '✓ Mật khẩu khớp';
        fb.style.color = '#27ae60';
    } else {
        fb.textContent = '✗ Mật khẩu chưa khớp';
        fb.style.color = '#e74c3c';
    }
}
</script>
</body>
</html>
