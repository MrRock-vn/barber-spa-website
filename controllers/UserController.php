<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';

class UserController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function profile(): void
    {
        Auth::requireLogin();

        $userId = (int) ($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);

        if ($userId <= 0) {
            echo 'Không xác định được người dùng đang đăng nhập.';
            return;
        }

        $user = $this->userModel->findById($userId);

        render('user/profile', [
            'pageTitle' => 'My Profile - ' . APP_NAME,
            'navSection' => 'user',
            'user' => $user,
        ]);
    }

    public function editProfile(): void
    {
        Auth::requireLogin();

        $userId = (int) ($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);

        if ($userId <= 0) {
            echo 'Không xác định được người dùng đang đăng nhập.';
            return;
        }

        $user = $this->userModel->findById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name'     => trim($_POST['name'] ?? ''),
                'phone'    => trim($_POST['phone'] ?? ''),
                'address'  => trim($_POST['address'] ?? ''),
                'city'     => trim($_POST['city'] ?? ''),
                'district' => trim($_POST['district'] ?? ''),
                'avatar'   => null,
            ];

            $this->userModel->updateProfile($userId, $data);

            header('Location: /barber-spa/my-profile');
            exit;
        }

        render('user/edit-profile', [
            'pageTitle' => 'Edit Profile - ' . APP_NAME,
            'navSection' => 'user',
            'user' => $user,
        ]);
    }
}