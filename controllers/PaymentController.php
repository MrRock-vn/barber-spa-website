<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Payment.php';

class PaymentController
{
    private Booking $bookingModel;
    private Payment $paymentModel;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->paymentModel = new Payment();
    }

    public function index(): void
    {
        Auth::requireLogin();

        $bookingId = (int) ($_GET['booking_id'] ?? 0);
        if ($bookingId <= 0) {
            flash('error', 'Booking không hợp lệ.');
            redirect(BASE_URL . '/my-bookings');
        }

        $booking = $this->bookingModel->findDetailedById($bookingId);

        if (!$booking || (int) $booking['user_id'] !== (int) Auth::id()) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        $payment = $this->paymentModel->findByBookingId($bookingId);

        render('payment/index', [
    'pageTitle' => 'Thanh toán - ' . APP_NAME,
    'navSection' => 'user',
    'booking' => $booking,
    'payment' => $payment,
]);
    }

    public function confirm(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . '/my-bookings');
        }

        if (!verifyCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ.');
            redirect(BASE_URL . '/my-bookings');
        }

        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        $action = trim($_POST['action'] ?? '');

        if ($bookingId <= 0) {
            flash('error', 'Booking không hợp lệ.');
            redirect(BASE_URL . '/my-bookings');
        }

        $booking = $this->bookingModel->findDetailedById($bookingId);

        if (!$booking || (int) $booking['user_id'] !== (int) Auth::id()) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        if ($action === 'mark_counter') {
            flash('success', 'Booking đã được ghi nhận thanh toán tại quầy.');
            redirect(BASE_URL . '/booking/' . $bookingId);
        }

        if ($action === 'simulate_online_success') {
            $existingPayment = $this->paymentModel->findByBookingId($bookingId);

            $gatewayResponse = json_encode([
                'message' => 'Simulated online payment success',
                'booking_id' => $bookingId,
                'user_id' => (int) Auth::id(),
                'confirmed_at' => date('Y-m-d H:i:s'),
            ], JSON_UNESCAPED_UNICODE);

            if ($existingPayment) {
                $this->paymentModel->markSuccess(
                    (int) $existingPayment['id'],
                    $gatewayResponse,
                    date('Y-m-d H:i:s')
                );
            } else {
                $transactionId = 'SIM_' . $bookingId . '_' . time();

                $paymentId = $this->paymentModel->create([
                    'booking_id' => $bookingId,
                    'user_id' => (int) Auth::id(),
                    'gateway' => 'vnpay',
                    'transaction_id' => $transactionId,
                    'amount' => (float) $booking['total_price'],
                    'currency' => 'VND',
                    'status' => 'success',
                    'gateway_response' => $gatewayResponse,
                    'paid_at' => date('Y-m-d H:i:s'),
                ]);

                $this->paymentModel->markSuccess(
                    $paymentId,
                    $gatewayResponse,
                    date('Y-m-d H:i:s')
                );
            }

            $this->bookingModel->updatePaymentStatus($bookingId, 'paid');

            flash('success', 'Thanh toán online thành công (giả lập).');
            redirect(BASE_URL . '/booking/' . $bookingId);
        }

        flash('error', 'Thao tác thanh toán không hợp lệ.');
        redirect(BASE_URL . '/payment?booking_id=' . $bookingId);
    }
}