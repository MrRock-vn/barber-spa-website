<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Salon.php';

class SalonController
{
    private Salon $salonModel;

    public function __construct()
    {
        $this->salonModel = new Salon();
    }

    public function index(): void
    {
        Auth::requireRole(['admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostAction();
            return;
        }

        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $city = trim($_GET['city'] ?? '');

        $filters = [
            'keyword' => $keyword,
            'status' => $status,
            'city' => $city,
        ];

        $salons = $this->salonModel->getAllForAdmin($filters);

        render('admin/salons/index', [
            'pageTitle' => 'Admin Salons - ' . APP_NAME,
            'navSection' => 'admin',
            'salons' => $salons,
        ]);
    }

    private function handlePostAction(): void
    {
        if (!verifyCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ.');
            redirect(BASE_URL . '/admin/salons');
        }

        $salonId = (int) ($_POST['salon_id'] ?? 0);
        $action = trim($_POST['action'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        if ($salonId <= 0 || $action === '') {
            flash('error', 'Dữ liệu không hợp lệ.');
            redirect(BASE_URL . '/admin/salons');
        }

        $salon = $this->salonModel->findById($salonId);

        if (!$salon) {
            flash('error', 'Không tìm thấy salon.');
            redirect(BASE_URL . '/admin/salons');
        }

        switch ($action) {
            case 'approve':
                $this->salonModel->approve($salonId);
                flash('success', 'Đã duyệt salon.');
                break;

            case 'reject':
                if ($reason === '') {
                    flash('error', 'Vui lòng nhập lý do từ chối.');
                    redirect(BASE_URL . '/admin/salons');
                }

                $this->salonModel->reject($salonId, $reason);
                flash('success', 'Đã từ chối salon.');
                break;

            case 'hide':
                $this->salonModel->hide($salonId);
                flash('success', 'Đã ẩn salon.');
                break;

            case 'reopen':
                $this->salonModel->reopen($salonId);
                flash('success', 'Đã mở lại salon.');
                break;

            case 'delete':
                $canDelete = $this->salonModel->canDelete($salonId);

                if (!$canDelete) {
                    flash('error', 'Salon còn booking đang hoạt động, không thể xóa mềm.');
                    redirect(BASE_URL . '/admin/salons');
                }

                $this->salonModel->softDelete($salonId);
                flash('success', 'Đã chuyển salon sang deleted.');
                break;

            default:
                flash('error', 'Hành động không hợp lệ.');
                break;
        }

        redirect(BASE_URL . '/admin/salons');
    }
}