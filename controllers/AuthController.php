<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../core/Mailer.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login(): void
    {
        if (Auth::check()) {
            $this->redirectByRole(Auth::role());
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrf()) {
                flash('error', 'Phiên làm việc không hợp lệ. Vui lòng thử lại.');
                redirect(BASE_URL . '/login');
            }

            $email = trim($_POST['email'] ?? '');
            $password = (string) ($_POST['password'] ?? '');
            $remember = isset($_POST['remember']);

            $_SESSION['old']['email'] = $email;

            if (!isValidEmail($email)) {
                flash('error', 'Email không đúng định dạng.');
                redirect(BASE_URL . '/login');
            }

            if ($password === '') {
                flash('error', 'Vui lòng nhập mật khẩu.');
                redirect(BASE_URL . '/login');
            }

            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                flash('error', 'Email hoặc mật khẩu không đúng.');
                redirect(BASE_URL . '/login');
            }

            // SECURITY: Check email verified
            if (empty($user['email_verified_at'])) {
                flash('error', 'Vui lòng xác thực email trước khi đăng nhập. Kiểm tra email của bạn.');
                redirect(BASE_URL . '/login');
            }

            if ((int) $user['is_active'] !== 1) {
                flash('error', 'Tài khoản của bạn đã bị khóa hoặc vô hiệu hóa.');
                redirect(BASE_URL . '/login');
            }

            if (!empty($user['locked_until']) && strtotime((string) $user['locked_until']) > time()) {
                flash('error', 'Tài khoản đang bị khóa tạm thời. Vui lòng thử lại sau.');
                redirect(BASE_URL . '/login');
            }

            if (!password_verify($password, (string) $user['password'])) {
                $this->userModel->increaseLoginAttempts((int) $user['id']);

                $freshUser = $this->userModel->findById((int) $user['id']);
                $attempts = (int) ($freshUser['login_attempts'] ?? 0);

                if ($attempts >= 5) {
                    // SECURITY: Increase lock time from 1 minute to 30 minutes to prevent brute force
                    $lockedUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                    $this->userModel->lockAccount((int) $user['id'], $lockedUntil);
                    flash('error', 'Bạn nhập sai quá nhiều lần. Tài khoản bị khóa tạm 30 phút.');
                } else {
                    flash('error', 'Email hoặc mật khẩu không đúng.');
                }

                redirect(BASE_URL . '/login');
            }

            $this->userModel->resetLoginAttempts((int) $user['id']);
            $this->userModel->updateLoginSuccess((int) $user['id'], $_SERVER['REMOTE_ADDR'] ?? null);

            Auth::login($user);

            if ($remember) {
                $rememberToken = bin2hex(random_bytes(32));
                $this->userModel->updateRememberToken((int) $user['id'], $rememberToken);

                setcookie(
                    'remember_token',
                    $rememberToken,
                    [
                        'expires' => time() + (7 * 24 * 60 * 60),
                        'path' => '/',
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]
                );
            }

            unset($_SESSION['old']);
            flash('success', 'Đăng nhập thành công.');
            $this->redirectByRole((string) $user['role']);
        }

        render('auth/login', [
    'pageTitle' => 'Đăng nhập - ' . APP_NAME,
    'navSection' => 'public',
]);
    }

    public function register(): void
    {
        if (Auth::check()) {
            $this->redirectByRole(Auth::role());
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrf()) {
                flash('error', 'Phiên làm việc không hợp lệ. Vui lòng thử lại.');
                redirect(BASE_URL . '/register');
            }

            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = (string) ($_POST['password'] ?? '');
            $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

            $_SESSION['old'] = [
                'name' => $name,
                'email' => $email,
            ];

            if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 150) {
                flash('error', 'Họ tên phải từ 2 đến 150 ký tự.');
                redirect(BASE_URL . '/register');
            }

            if (!isValidEmail($email)) {
                flash('error', 'Email không đúng định dạng.');
                redirect(BASE_URL . '/register');
            }

            if ($this->userModel->findByEmail($email)) {
                flash('error', 'Email đã được sử dụng.');
                redirect(BASE_URL . '/register');
            }

            if (!isStrongPassword($password)) {
                flash('error', 'Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường và số.');
                redirect(BASE_URL . '/register');
            }

            if ($password !== $passwordConfirmation) {
                flash('error', 'Xác nhận mật khẩu không khớp.');
                redirect(BASE_URL . '/register');
            }

            // Auto-verify email on registration (no email verification required)
            $userId = $this->userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'role' => 'customer',
                'is_active' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'email_token' => null,
            ]);

            unset($_SESSION['old']);

            flash('success', 'Đăng ký thành công. Bạn có thể đăng nhập ngay bây giờ.');
            redirect(BASE_URL . '/login');
        }
    render('auth/register', [
    'pageTitle' => 'Đăng ký - ' . APP_NAME,
    'navSection' => 'public',
]);
    }

    public function logout(): void
    {
        if (Auth::check()) {
            $userId = Auth::id();

            if ($userId !== null) {
                $this->userModel->updateRememberToken($userId, null);
            }
        }

        setcookie('remember_token', '', time() - 3600, '/');
        Auth::logout();

        flash('success', 'Bạn đã đăng xuất.');
        redirect(BASE_URL . '/login');
    }

   public function forgotPassword(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ. Vui lòng thử lại.');
            redirect(BASE_URL . '/forgot-password');
        }

        $email = trim($_POST['email'] ?? '');
        $_SESSION['old']['email'] = $email;

        if (!isValidEmail($email)) {
            flash('error', 'Email không đúng định dạng.');
            redirect(BASE_URL . '/forgot-password');
        }

        $user = $this->userModel->findByEmail($email);

        if ($user) {
            $resetToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $this->userModel->saveResetToken((int) $user['id'], $resetToken, $expiresAt);

            $resetLink = BASE_URL . '/reset-password?token=' . urlencode($resetToken);

            $emailBody = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .header { background-color: #dc3545; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { padding: 20px; }
        .footer { background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; background-color: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .btn:hover { background-color: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Đặt Lại Mật Khẩu - Barber Spa</h2>
        </div>
        <div class="content">
            <p>Xin chào ' . e($user['name'] ?? 'bạn') . ',</p>
            <p>Bạn vừa yêu cầu đặt lại mật khẩu cho tài khoản Barber Spa.</p>
            <p><a href="' . $resetLink . '" class="btn">Đặt Lại Mật Khẩu</a></p>
            <p><strong>Hoặc copy liên kết bên dưới:</strong></p>
            <p><a href="' . $resetLink . '">' . $resetLink . '</a></p>
            <p style="color: #666; font-size: 12px;">Liên kết này sẽ hết hạn sau 15 phút.</p>
            <p style="color: #999; font-size: 12px;">Nếu đây không phải yêu cầu của bạn, vui lòng bỏ qua email này.</p>
        </div>
        <div class="footer">
            <p>&copy; 2026 Barber Spa. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';

            $sent = Mailer::send(
                (string) $user['email'],
                (string) ($user['name'] ?? 'Khách hàng'),
                'Đặt lại mật khẩu - Barber Spa',
                $emailBody
            );

            if ($sent) {
                flash('success', 'Nếu email tồn tại trong hệ thống, liên kết đặt lại mật khẩu đã được gửi.');
            } else {
                flash('error', 'Không gửi được email đặt lại mật khẩu. Vui lòng thử lại sau.');
            }
        } else {
            flash('success', 'Nếu email tồn tại trong hệ thống, liên kết đặt lại mật khẩu đã được gửi.');
        }

        unset($_SESSION['old']);
        redirect(BASE_URL . '/forgot-password');
    }

    render('auth/forgot-password', [
        'pageTitle' => 'Quên mật khẩu - ' . APP_NAME,
        'navSection' => 'public',
    ]);
}

    public function resetPassword(): void
    {
        $token = trim($_GET['token'] ?? $_POST['token'] ?? '');

        if ($token === '') {
            flash('error', 'Liên kết đặt lại mật khẩu không hợp lệ.');
            redirect(BASE_URL . '/forgot-password');
        }

        $user = $this->userModel->findByResetToken($token);

        if (!$user) {
            flash('error', 'Token đặt lại mật khẩu không hợp lệ.');
            redirect(BASE_URL . '/forgot-password');
        }

        $expiresAt = strtotime((string) ($user['reset_token_expires'] ?? ''));
        if ($expiresAt === false || $expiresAt < time()) {
            flash('error', 'Token đặt lại mật khẩu đã hết hạn.');
            redirect(BASE_URL . '/forgot-password');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrf()) {
                flash('error', 'Phiên làm việc không hợp lệ. Vui lòng thử lại.');
                redirect(BASE_URL . '/reset-password?token=' . urlencode($token));
            }

            $password = (string) ($_POST['password'] ?? '');
            $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

            if (!isStrongPassword($password)) {
                flash('error', 'Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường và số.');
                redirect(BASE_URL . '/reset-password?token=' . urlencode($token));
            }

            if ($password !== $passwordConfirmation) {
                flash('error', 'Xác nhận mật khẩu không khớp.');
                redirect(BASE_URL . '/reset-password?token=' . urlencode($token));
            }

            $this->userModel->updatePassword((int) $user['id'], password_hash($password, PASSWORD_BCRYPT));

            flash('success', 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập.');
            redirect(BASE_URL . '/login');
        }

       render('auth/reset-password', [
    'pageTitle' => 'Đặt lại mật khẩu - ' . APP_NAME,
    'navSection' => 'public',
]);
    }

    public function verifyEmail(): void
    {
        $token = trim($_GET['token'] ?? '');

        if ($token === '') {
            flash('error', 'Token xác thực email không hợp lệ.');
            redirect(BASE_URL . '/login');
        }

        $success = $this->userModel->verifyEmailByToken($token);

        if ($success) {
            flash('success', 'Xác thực email thành công.');
        } else {
            flash('error', 'Token xác thực email không hợp lệ hoặc đã được sử dụng.');
        }

        redirect(BASE_URL . '/login');
    }

    private function redirectByRole(?string $role): void
    {
        switch ($role) {
            case 'admin':
                redirect(BASE_URL . '/admin/dashboard');
                break;

            case 'owner':
                redirect(BASE_URL . '/owner/dashboard');
                break;

            default:
                redirect(BASE_URL . '/home');
                break;
        }
    }
}