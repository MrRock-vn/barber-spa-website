<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Salon.php';

class BookingController
{
    private Booking $bookingModel;
    private Salon $salonModel;

    public function __construct()
    {
        $this->bookingModel = new Booking();
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
        $bookingDate = trim($_GET['booking_date'] ?? '');
        $salonId = trim($_GET['salon_id'] ?? '');

        $filters = [
            'keyword' => $keyword,
            'status' => $status,
            'booking_date' => $bookingDate,
            'salon_id' => $salonId,
        ];

        $bookings = $this->bookingModel->getAllForAdmin($filters);
        $salons = $this->salonModel->getAllForAdmin([]);

        render('admin/bookings/index', [
            'pageTitle' => 'Admin Bookings - ' . APP_NAME,
            'navSection' => 'admin',
            'bookings' => $bookings,
            'salons' => $salons,
        ]);
    }

    private function handlePostAction(): void
    {
        if (!verifyCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ.');
            redirect(BASE_URL . '/admin/bookings');
        }

        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        $action = trim($_POST['action'] ?? '');

        if ($bookingId <= 0 || $action === '') {
            flash('error', 'Dữ liệu không hợp lệ.');
            redirect(BASE_URL . '/admin/bookings');
        }

        $booking = $this->bookingModel->findById($bookingId);

        if (!$booking) {
            flash('error', 'Không tìm thấy booking.');
            redirect(BASE_URL . '/admin/bookings');
        }

        switch ($action) {
            case 'confirm':
                if ($booking['status'] !== 'pending') {
                    flash('error', 'Chỉ booking pending mới có thể xác nhận.');
                    redirect(BASE_URL . '/admin/bookings');
                }

                $this->bookingModel->updateStatus($bookingId, 'confirmed');
                flash('success', 'Đã xác nhận booking.');
                break;

            case 'complete':
                if (!in_array($booking['status'], ['pending', 'confirmed'], true)) {
                    flash('error', 'Booking này không thể hoàn thành.');
                    redirect(BASE_URL . '/admin/bookings');
                }

                $this->bookingModel->updateStatus($bookingId, 'completed');
                flash('success', 'Đã đánh dấu booking hoàn thành.');
                break;

            case 'cancel':
                if (in_array($booking['status'], ['completed', 'cancelled'], true)) {
                    flash('error', 'Booking này không thể hủy.');
                    redirect(BASE_URL . '/admin/bookings');
                }

                $this->bookingModel->updateStatus($bookingId, 'cancelled', 'Admin hủy lịch');
                flash('success', 'Đã hủy booking.');
                break;

            default:
                flash('error', 'Hành động không hợp lệ.');
                break;
        }

        redirect(BASE_URL . '/admin/bookings');
    }
}