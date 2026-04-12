<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/User.php';

class UserController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index(): void
    {
        Auth::requireRole(['admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostAction();
            return;
        }

        $keyword = trim($_GET['keyword'] ?? '');
        $role = trim($_GET['role'] ?? '');
        $isActive = $_GET['is_active'] ?? '';

        $filters = [
            'keyword' => $keyword,
            'role' => $role,
            'is_active' => $isActive,
        ];

        $users = $this->userModel->all($filters);
render('admin/users/index', [
    'pageTitle' => 'Admin Users - ' . APP_NAME,
    'navSection' => 'admin',
    'users' => $users,
]);
    }

    private function handlePostAction(): void
    {
        if (!verifyCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ.');
            redirect(BASE_URL . '/admin/users');
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $action = trim($_POST['action'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        if ($userId <= 0 || $action === '') {
            flash('error', 'Dữ liệu không hợp lệ.');
            redirect(BASE_URL . '/admin/users');
        }

        if ((int) Auth::id() === $userId) {
            flash('error', 'Bạn không thể tự khóa chính mình.');
            redirect(BASE_URL . '/admin/users');
        }

        $user = $this->userModel->findById($userId);

        if (!$user) {
            flash('error', 'Không tìm thấy user.');
            redirect(BASE_URL . '/admin/users');
        }

        switch ($action) {
            case 'ban':
                if ($reason === '') {
                    $reason = 'Bị khóa bởi admin';
                }

                $this->userModel->banUser($userId, $reason);
                flash('success', 'Đã khóa user thành công.');
                break;

            case 'unban':
                $this->userModel->unbanUser($userId);
                flash('success', 'Đã mở khóa user thành công.');
                break;

            default:
                flash('error', 'Hành động không hợp lệ.');
                break;
        }

        redirect(BASE_URL . '/admin/users');
    }
}