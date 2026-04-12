<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Salon.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Staff.php';
require_once __DIR__ . '/../models/Booking.php';

class BookingController
{
    private Salon $salonModel;
    private Service $serviceModel;
    private Staff $staffModel;
    private Booking $bookingModel;

    public function __construct()
    {
        $this->salonModel = new Salon();
        $this->serviceModel = new Service();
        $this->staffModel = new Staff();
        $this->bookingModel = new Booking();
    }

    public function create(): void
    {
        Auth::requireLogin();

        $step = max(1, (int) ($_GET['step'] ?? 1));
        $wizard = $_SESSION['booking_wizard'] ?? [];

        if ($step === 1) {
            $salonId = (int) ($_GET['salon_id'] ?? ($wizard['salon_id'] ?? 0));
            $salon = $this->salonModel->findActiveById($salonId);

            if (!$salon) {
                flash('error', 'Salon không tồn tại hoặc không hoạt động.');
                redirect(BASE_URL . '/home');
            }

            $services = $this->serviceModel->getActiveBySalonId($salonId);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!verifyCsrf()) {
                    flash('error', 'Phiên làm việc không hợp lệ.');
                    redirect(BASE_URL . '/booking/create?salon_id=' . $salonId . '&step=1');
                }

                $serviceIds = $_POST['service_ids'] ?? [];

                if (!is_array($serviceIds) || empty($serviceIds)) {
                    flash('error', 'Vui lòng chọn ít nhất 1 dịch vụ.');
                    redirect(BASE_URL . '/booking/create?salon_id=' . $salonId . '&step=1');
                }

                $serviceIds = array_map('intval', $serviceIds);

                if (!$this->serviceModel->belongsToSalon($serviceIds, $salonId)) {
                    flash('error', 'Dịch vụ không hợp lệ.');
                    redirect(BASE_URL . '/booking/create?salon_id=' . $salonId . '&step=1');
                }

                $summary = $this->serviceModel->calculateSummaryByIds($serviceIds);

                $_SESSION['booking_wizard'] = [
                    'salon_id' => $salonId,
                    'service_ids' => $serviceIds,
                    'summary' => $summary,
                ];

                redirect(BASE_URL . '/booking/create?step=2');
            }

            require_once __DIR__ . '/../views/booking/step1-services.php';
            return;
        }

        if (empty($wizard['salon_id'])) {
            flash('error', 'Vui lòng bắt đầu lại từ bước chọn dịch vụ.');
            redirect(BASE_URL . '/home');
        }

        $salonId = (int) $wizard['salon_id'];
        $salon = $this->salonModel->findActiveById($salonId);

        if (!$salon) {
            unset($_SESSION['booking_wizard']);
            flash('error', 'Salon không tồn tại.');
            redirect(BASE_URL . '/home');
        }

        if ($step === 2) {
            $staffList = $this->staffModel->getActiveBySalonId($salonId);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!verifyCsrf()) {
                    flash('error', 'Phiên làm việc không hợp lệ.');
                    redirect(BASE_URL . '/booking/create?step=2');
                }

                $staffId = (int) ($_POST['staff_id'] ?? 0);

                if ($staffId <= 0 || !$this->staffModel->belongsToSalon($staffId, $salonId)) {
                    flash('error', 'Nhân viên không hợp lệ.');
                    redirect(BASE_URL . '/booking/create?step=2');
                }

                $_SESSION['booking_wizard']['staff_id'] = $staffId;

                redirect(BASE_URL . '/booking/create?step=3');
            }

            require_once __DIR__ . '/../views/booking/step2-staff.php';
            return;
        }

        if ($step === 3) {
            if (empty($wizard['staff_id'])) {
                flash('error', 'Vui lòng chọn nhân viên trước.');
                redirect(BASE_URL . '/booking/create?step=2');
            }

            $staff = $this->staffModel->findActiveById((int) $wizard['staff_id']);
            $summary = $wizard['summary'] ?? ['total_duration' => 0, 'total_price' => 0];

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!verifyCsrf()) {
                    flash('error', 'Phiên làm việc không hợp lệ.');
                    redirect(BASE_URL . '/booking/create?step=3');
                }

                $bookingDate = trim($_POST['booking_date'] ?? '');
                $startTime = trim($_POST['start_time'] ?? '');

                if ($bookingDate === '' || $startTime === '') {
                    flash('error', 'Vui lòng chọn ngày và giờ.');
                    redirect(BASE_URL . '/booking/create?step=3');
                }

                $startTimestamp = strtotime($bookingDate . ' ' . $startTime);
                if ($startTimestamp === false) {
                    flash('error', 'Ngày giờ không hợp lệ.');
                    redirect(BASE_URL . '/booking/create?step=3');
                }

                if (($startTimestamp - time()) < (2 * 60 * 60)) {
                    flash('error', 'Không thể đặt lịch trong vòng 2 giờ tới.');
                    redirect(BASE_URL . '/booking/create?step=3');
                }

                $duration = (int) ($summary['total_duration'] ?? 0);
                $endTime = date('H:i:s', strtotime($startTime . ' +' . $duration . ' minutes'));

                if (!$this->staffModel->isWorkingOn((int) $wizard['staff_id'], $bookingDate)) {
                    flash('error', 'Nhân viên không làm việc trong ngày này.');
                    redirect(BASE_URL . '/booking/create?step=3');
                }

                if ($this->staffModel->isDayOff((int) $wizard['staff_id'], $bookingDate)) {
                    flash('error', 'Nhân viên nghỉ trong ngày này.');
                    redirect(BASE_URL . '/booking/create?step=3');
                }

                if ($this->bookingModel->hasStaffConflict((int) $wizard['staff_id'], $bookingDate, $startTime, $endTime)) {
                    flash('error', 'Khung giờ đã có người đặt. Vui lòng chọn giờ khác.');
                    redirect(BASE_URL . '/booking/create?step=3');
                }

                if ($this->bookingModel->hasHeldConflict((int) $wizard['staff_id'], $bookingDate, $startTime, $endTime)) {
                    flash('error', 'Khung giờ đang được giữ tạm. Vui lòng chọn giờ khác.');
                    redirect(BASE_URL . '/booking/create?step=3');
                }

                $_SESSION['booking_wizard']['booking_date'] = $bookingDate;
                $_SESSION['booking_wizard']['start_time'] = $startTime;
                $_SESSION['booking_wizard']['end_time'] = $endTime;

                redirect(BASE_URL . '/booking/create?step=4');
            }

            require_once __DIR__ . '/../views/booking/step3-datetime.php';
            return;
        }

        if ($step === 4) {
            if (empty($wizard['staff_id']) || empty($wizard['booking_date']) || empty($wizard['start_time'])) {
                flash('error', 'Vui lòng hoàn thành các bước trước.');
                redirect(BASE_URL . '/booking/create?step=1&salon_id=' . $salonId);
            }

            $staff = $this->staffModel->findActiveById((int) $wizard['staff_id']);
            $summary = $wizard['summary'] ?? ['services' => [], 'total_duration' => 0, 'total_price' => 0];

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!verifyCsrf()) {
                    flash('error', 'Phiên làm việc không hợp lệ.');
                    redirect(BASE_URL . '/booking/create?step=4');
                }

                $paymentMethod = trim($_POST['payment_method'] ?? 'at_counter');
                $notes = trim($_POST['notes'] ?? '');

                if (!in_array($paymentMethod, ['online', 'at_counter'], true)) {
                    flash('error', 'Phương thức thanh toán không hợp lệ.');
                    redirect(BASE_URL . '/booking/create?step=4');
                }

                if ($this->bookingModel->countActiveByUserId((int) Auth::id()) >= 5) {
                    flash('error', 'Bạn đã đạt giới hạn 5 lịch hẹn đang hoạt động.');
                    redirect(BASE_URL . '/my-bookings');
                }

                $bookingId = $this->bookingModel->create([
                    'user_id' => (int) Auth::id(),
                    'salon_id' => $salonId,
                    'staff_id' => (int) $wizard['staff_id'],
                    'services' => json_encode($summary['services'], JSON_UNESCAPED_UNICODE),
                    'booking_date' => $wizard['booking_date'],
                    'start_time' => $wizard['start_time'],
                    'end_time' => $wizard['end_time'],
                    'total_price' => $summary['total_price'],
                    'status' => 'pending',
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentMethod === 'online' ? 'unpaid' : 'unpaid',
                    'notes' => $notes,
                    'slot_held_until' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
                ]);

                unset($_SESSION['booking_wizard']);

                if ($paymentMethod === 'online') {
                    flash('success', 'Tạo booking thành công. Vui lòng tiếp tục thanh toán.');
                    redirect(BASE_URL . '/payment?booking_id=' . $bookingId);
                }

                flash('success', 'Đặt lịch thành công.');
                redirect(BASE_URL . '/booking/' . $bookingId);
            }

            require_once __DIR__ . '/../views/booking/step4-confirm.php';
            return;
        }

        redirect(BASE_URL . '/booking/create?step=1&salon_id=' . $salonId);
    }

    public function show($id): void
    {
        Auth::requireLogin();

        $id = (int) $id;
        $booking = $this->bookingModel->findDetailedById($id);

        if (!$booking || (int) $booking['user_id'] !== (int) Auth::id()) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        render('booking/show', [
    'pageTitle' => 'Chi tiết lịch hẹn - ' . APP_NAME,
    'navSection' => 'user',
    'booking' => $booking,
]);
    }

    public function myBookings(): void
    {
        Auth::requireLogin();

        $bookings = $this->bookingModel->getByUserId((int) Auth::id());
        render('booking/show', [
    'pageTitle' => 'Chi tiết lịch hẹn - ' . APP_NAME,
    'navSection' => 'user',
    'booking' => $booking,
]);
    }

    public function cancel(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
            flash('error', 'Yêu cầu không hợp lệ.');
            redirect(BASE_URL . '/my-bookings');
        }

        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        $booking = $this->bookingModel->findById($bookingId);

        if (!$booking || (int) $booking['user_id'] !== (int) Auth::id()) {
            flash('error', 'Không tìm thấy lịch hẹn.');
            redirect(BASE_URL . '/my-bookings');
        }

        if (!$this->bookingModel->canCancel($booking)) {
            flash('error', 'Lịch hẹn này không thể hủy.');
            redirect(BASE_URL . '/my-bookings');
        }

        $this->bookingModel->updateStatus($bookingId, 'cancelled', 'Khách hàng hủy lịch');
        flash('success', 'Đã hủy lịch hẹn.');
        redirect(BASE_URL . '/my-bookings');
    }

    public function reschedule(): void
    {
        Auth::requireLogin();
        echo '<h1>Reschedule Booking</h1>';
    }
}