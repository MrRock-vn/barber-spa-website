<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Salon.php';
require_once __DIR__ . '/../../models/Booking.php';

class BookingController
{
    private Salon $salonModel;
    private Booking $bookingModel;

    public function __construct()
    {
        $this->salonModel = new Salon();
        $this->bookingModel = new Booking();
    }

    public function index(): void
    {
        Auth::requireRole(['owner']);

        $ownerId = (int) Auth::id();
        $salon = $this->salonModel->getFirstByOwnerId($ownerId);

        if (!$salon) {
            echo '<h1>Owner Bookings</h1>';
            echo '<p>Bạn chưa có salon nào.</p>';
            return;
        }

        // SECURITY: Verify salon ownership to prevent accessing other owner's salons
        if ((int) $salon['owner_id'] !== $ownerId) {
            http_response_code(403);
            echo '<h1>Forbidden</h1>';
            echo '<p>Bạn không có quyền truy cập salon này.</p>';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostAction((int) $salon['id']);
            return;
        }

        $status = trim($_GET['status'] ?? '');
        $bookingDate = trim($_GET['booking_date'] ?? '');

        $filters = [];

        if ($status !== '') {
            $filters['status'] = $status;
        }

        if ($bookingDate !== '') {
            $filters['booking_date'] = $bookingDate;
        }

        $bookings = $this->bookingModel->getBySalonId((int) $salon['id'], $filters);

        render('owner/bookings/index', [
            'pageTitle' => 'Owner Bookings - ' . APP_NAME,
            'navSection' => 'owner',
            'salon' => $salon,
            'bookings' => $bookings,
        ]);
    }

    private function handlePostAction(int $salonId): void
    {
        if (!verifyCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ.');
            redirect(BASE_URL . '/owner/bookings');
        }

        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        $action = trim($_POST['action'] ?? '');

        if ($bookingId <= 0 || $action === '') {
            flash('error', 'Dữ liệu không hợp lệ.');
            redirect(BASE_URL . '/owner/bookings');
        }

        $booking = $this->bookingModel->findById($bookingId);

        if (!$booking || (int) $booking['salon_id'] !== $salonId) {
            flash('error', 'Không tìm thấy booking thuộc salon của bạn.');
            redirect(BASE_URL . '/owner/bookings');
        }

        switch ($action) {
            case 'confirm':
                if ($booking['status'] !== 'pending') {
                    flash('error', 'Chỉ booking pending mới có thể xác nhận.');
                    redirect(BASE_URL . '/owner/bookings');
                }

                $this->bookingModel->updateStatus($bookingId, 'confirmed');
                flash('success', 'Đã xác nhận booking.');
                break;

            case 'complete':
                if (!in_array($booking['status'], ['pending', 'confirmed'], true)) {
                    flash('error', 'Booking này không thể hoàn thành.');
                    redirect(BASE_URL . '/owner/bookings');
                }

                $this->bookingModel->updateStatus($bookingId, 'completed');
                flash('success', 'Đã đánh dấu booking hoàn thành.');
                break;

            case 'cancel':
                if (in_array($booking['status'], ['completed', 'cancelled'], true)) {
                    flash('error', 'Booking này không thể hủy.');
                    redirect(BASE_URL . '/owner/bookings');
                }

                $this->bookingModel->updateStatus($bookingId, 'cancelled', 'Salon hủy lịch');
                flash('success', 'Đã hủy booking.');
                break;

            default:
                flash('error', 'Hành động không hợp lệ.');
                break;
        }

        redirect(BASE_URL . '/owner/bookings');
    }
}