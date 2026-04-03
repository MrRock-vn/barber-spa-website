<?php
// ============================================================
// public/register.php  — AUTH-01
// Người làm: Nguyễn Văn Quang
// Branch: feature/auth
// ============================================================
session_start();
require_once __DIR__ . '/../config/db.php';

// Nếu đã đăng nhập rồi → redirect
if (isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$error   = '';
$success = '';
$data    = ['name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    $data = ['name' => $name, 'email' => $email, 'phone' => $phone];

    // --- Validate ---
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Vui lòng điền đầy đủ tất cả các trường bắt buộc.';
    } elseif (mb_strlen($name) < 2) {
        $error = 'Họ tên phải có ít nhất 2 ký tự.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không đúng định dạng.';
    } elseif (strlen($password) < 8) {
        $error = 'Mật khẩu phải có ít nhất 8 ký tự.';
    } elseif ($password !== $confirm) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } elseif (!empty($phone) && !preg_match('/^(0|\+84)[0-9]{9}$/', $phone)) {
        $error = 'Số điện thoại không đúng định dạng (VD: 0912345678).';
    } else {
        $db = getDB();

        // Kiểm tra email đã tồn tại chưa
        $check = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Email này đã được đăng ký. Vui lòng dùng email khác hoặc <a href="/login.php">đăng nhập</a>.';
        } else {
            // Hash mật khẩu và lưu vào DB
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            $stmt = $db->prepare("
                INSERT INTO users (name, email, phone, password, role, is_active, created_at)
                VALUES (?, ?, ?, ?, 'customer', 1, NOW())
            ");
            $stmt->execute([$name, $email, $phone ?: null, $hash]);

            $userId = $db->lastInsertId();

            // Tự động đăng nhập sau khi đăng ký
            $_SESSION['user'] = [
                'id'    => $userId,
                'name'  => $name,
                'email' => $email,
                'role'  => 'customer',
            ];

            header('Location: /index.php?registered=1');
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
    <title>Đăng ký — Barber & Spa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .auth-card {
            max-width: 460px; margin: 60px auto 40px;
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
    <h5 class="mb-4 text-center">Tạo tài khoản mới</h5>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" id="registerForm" novalidate>
        <!-- Họ tên -->
        <div class="mb-3">
            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control"
                   value="<?= htmlspecialchars($data['name']) ?>"
                   placeholder="Nguyễn Văn A" required autofocus>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" id="emailInput"
                   value="<?= htmlspecialchars($data['email']) ?>"
                   placeholder="example@gmail.com" required>
            <div id="emailFeedback" class="form-text"></div>
        </div>

        <!-- Số điện thoại (không bắt buộc) -->
        <div class="mb-3">
            <label class="form-label">Số điện thoại <span class="text-muted small">(không bắt buộc)</span></label>
            <input type="tel" name="phone" class="form-control"
                   value="<?= htmlspecialchars($data['phone']) ?>"
                   placeholder="0912 345 678">
        </div>

        <!-- Mật khẩu -->
        <div class="mb-3">
            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="password" name="password" id="passwordInput"
                       class="form-control" placeholder="Tối thiểu 8 ký tự"
                       required minlength="8" oninput="checkStrength(this.value)">
                <button class="btn btn-outline-secondary" type="button"
                        onclick="togglePwd('passwordInput')">👁</button>
            </div>
            <!-- Thanh độ mạnh mật khẩu -->
            <div class="mt-2 bg-light rounded" style="height:4px">
                <div id="strengthBar" class="password-strength" style="width:0%"></div>
            </div>
            <div id="strengthText" class="form-text"></div>
        </div>

        <!-- Xác nhận mật khẩu -->
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

        <button type="submit" class="btn btn-primary w-100" id="submitBtn">
            <span id="btnText">Tạo tài khoản</span>
            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none"></span>
        </button>
    </form>

    <hr class="my-4">
    <p class="text-center mb-0 small">
        Đã có tài khoản? <a href="/login.php">Đăng nhập</a>
    </p>
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
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

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
    const pw  = document.getElementById('passwordInput').value;
    const cf  = document.getElementById('confirmInput').value;
    const fb  = document.getElementById('confirmFeedback');
    if (!cf) { fb.textContent = ''; return; }
    if (pw === cf) {
        fb.textContent = '✓ Mật khẩu khớp';
        fb.style.color = '#27ae60';
    } else {
        fb.textContent = '✗ Mật khẩu chưa khớp';
        fb.style.color = '#e74c3c';
    }
}

// Check email realtime (debounce 600ms)
let emailTimer;
document.getElementById('emailInput').addEventListener('input', function() {
    clearTimeout(emailTimer);
    const fb = document.getElementById('emailFeedback');
    fb.textContent = '';
    if (!this.value) return;
    emailTimer = setTimeout(() => {
        fetch('/api/check-email.php?email=' + encodeURIComponent(this.value))
            .then(r => r.json())
            .then(d => {
                if (d.exists) {
                    fb.textContent = '✗ Email này đã được đăng ký';
                    fb.style.color = '#e74c3c';
                } else {
                    fb.textContent = '✓ Email hợp lệ';
                    fb.style.color = '#27ae60';
                }
            })
            .catch(() => {});
    }, 600);
});

document.getElementById('registerForm').addEventListener('submit', function() {
    document.getElementById('btnText').textContent    = 'Đang xử lý...';
    document.getElementById('btnSpinner').classList.remove('d-none');
    document.getElementById('submitBtn').disabled = true;
});
</script>
</body>
</html>
