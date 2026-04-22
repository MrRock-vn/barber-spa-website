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

        if (($booking['payment_status'] ?? '') === 'paid') {
            flash('success', 'Booking này đã được thanh toán.');
            redirect(BASE_URL . '/booking/' . $bookingId);
        }

        if (($booking['status'] ?? '') === 'cancelled') {
            flash('error', 'Booking đã hủy không thể thanh toán.');
            redirect(BASE_URL . '/booking/' . $bookingId);
        }

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
            if ($booking['payment_method'] !== 'at_counter') {
                flash('error', 'Booking này không phải thanh toán tại quầy.');
                redirect(BASE_URL . '/booking/' . $bookingId);
            }

            if ($booking['payment_status'] !== 'unpaid') {
                flash('error', 'Booking này đã được thanh toán.');
                redirect(BASE_URL . '/booking/' . $bookingId);
            }

            $existingPayment = $this->paymentModel->findByBookingId($bookingId);
            if ($existingPayment && $existingPayment['status'] === 'success') {
                flash('success', 'Booking đã được ghi nhận thanh toán tại quầy.');
                redirect(BASE_URL . '/booking/' . $bookingId);
            }

            $paidAt = date('Y-m-d H:i:s');
            $payload = json_encode(['method' => 'at_counter', 'paid_at' => $paidAt]);

            if ($existingPayment) {
                $this->paymentModel->markSuccess((int) $existingPayment['id'], $payload, $paidAt);
            } else {
                $this->paymentModel->create([
                    'booking_id' => $bookingId,
                    'user_id' => (int) Auth::id(),
                    'gateway' => 'cash',
                    'transaction_id' => 'CASH_' . $bookingId . '_' . time(),
                    'amount' => (float) $booking['total_price'],
                    'currency' => 'VND',
                    'status' => 'success',
                    'gateway_response' => $payload,
                    'paid_at' => $paidAt,
                ]);
            }

            $this->bookingModel->updatePaymentStatus($bookingId, 'paid');

            flash('success', 'Booking đã được ghi nhận thanh toán tại quầy.');
            redirect(BASE_URL . '/booking/' . $bookingId);
        }

        flash('error', 'Thao tác thanh toán không hợp lệ.');
        redirect(BASE_URL . '/payment?booking_id=' . $bookingId);
    }

    public function vnpay(): void
    {
        Auth::requireLogin();

        $config = require __DIR__ . '/../config/vnpay.php';

        $bookingId = (int) ($_GET['booking_id'] ?? 0);
        $booking = $this->bookingModel->findById($bookingId);

        if (!$booking) {
            flash('error', 'Không tìm thấy lịch hẹn.');
            redirect(BASE_URL . '/my-bookings');
        }

        if ((int) $booking['user_id'] !== (int) Auth::id()) {
            http_response_code(403);
            flash('error', 'Bạn không có quyền thanh toán lịch hẹn này.');
            redirect(BASE_URL . '/my-bookings');
        }

        if ($booking['payment_method'] !== 'online') {
            flash('error', 'Booking này không được đặt thanh toán online.');
            redirect(BASE_URL . '/booking/' . $bookingId);
        }

        if ($booking['payment_status'] === 'paid') {
            flash('error', 'Booking này đã được thanh toán.');
            redirect(BASE_URL . '/booking/' . $bookingId);
        }

        if (($booking['status'] ?? '') === 'cancelled') {
            flash('error', 'Booking đã hủy không thể thanh toán.');
            redirect(BASE_URL . '/booking/' . $bookingId);
        }

        $amount = (float) $booking['total_price'];
        $txnRef = 'BOOK_' . $bookingId . '_' . time();

        $this->paymentModel->create([
            'booking_id' => $bookingId,
            'user_id' => (int) $booking['user_id'],
            'gateway' => 'vnpay',
            'transaction_id' => $txnRef,
            'amount' => $amount,
            'currency' => 'VND',
            'status' => 'pending',
        ]);

        $inputData = [
            'vnp_Version' => $config['version'],
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => trim($config['tmn_code']),
            'vnp_Amount' => (int) round($amount * 100),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $txnRef,
            'vnp_OrderInfo' => 'Thanh toán booking ' . $bookingId,
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => trim($config['return_url']),
            'vnp_IpAddr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_ExpireDate' => date('YmdHis', strtotime('+15 minutes')),
        ];

        ksort($inputData);

        $hashString = '';
        $queryString = '';
        $index = 0;

        foreach ($inputData as $key => $value) {
            $encodedKey = urlencode((string) $key);
            $encodedValue = urlencode((string) $value);
            $hashString .= ($index === 0 ? '' : '&') . $encodedKey . '=' . $encodedValue;
            $queryString .= $encodedKey . '=' . $encodedValue . '&';
            $index++;
        }

        $secureHash = hash_hmac('sha512', $hashString, trim($config['hash_secret']), false);
        $paymentUrl = $config['pay_url'] . '?' . $queryString . 'vnp_SecureHash=' . $secureHash;

        header('Location: ' . $paymentUrl);
        exit;
    }

    public function vnpayReturn(): void
    {
        $config = require __DIR__ . '/../config/vnpay.php';

        $inputData = $_GET;
        $receivedHash = (string) ($inputData['vnp_SecureHash'] ?? '');

        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        foreach ($inputData as $key => $value) {
            if (strpos((string) $key, 'vnp_') !== 0) {
                unset($inputData[$key]);
            }
        }

        ksort($inputData);

        $hashString = '';
        $index = 0;

        foreach ($inputData as $key => $value) {
            $encodedKey = urlencode((string) $key);
            $encodedValue = urlencode((string) $value);
            $hashString .= ($index === 0 ? '' : '&') . $encodedKey . '=' . $encodedValue;
            $index++;
        }

        $calculatedHash = hash_hmac('sha512', $hashString, trim($config['hash_secret']));

        if (!hash_equals($calculatedHash, $receivedHash)) {
            flash('error', 'Sai chữ ký bảo mật từ VNPay.');
            redirect(BASE_URL . '/my-bookings');
        }

        $txnRef = (string) ($_GET['vnp_TxnRef'] ?? '');
        $responseCode = (string) ($_GET['vnp_ResponseCode'] ?? '');
        $transactionNo = (string) ($_GET['vnp_TransactionNo'] ?? '');

        $payment = $this->paymentModel->findByTransactionId($txnRef);

        if (!$payment) {
            flash('error', 'Không tìm thấy giao dịch.');
            redirect(BASE_URL . '/my-bookings');
        }

        if ($this->paymentModel->isProcessedTransaction($txnRef)) {
            flash('success', 'Giao dịch này đã được xử lý trước đó.');
            redirect(BASE_URL . '/booking/' . (int) $payment['booking_id']);
        }

        if ($responseCode === '00') {
            $this->paymentModel->markSuccess(
                (int) $payment['id'],
                json_encode($_GET, JSON_UNESCAPED_UNICODE),
                date('Y-m-d H:i:s')
            );
            $this->bookingModel->updatePaymentStatus((int) $payment['booking_id'], 'paid');
            flash('success', 'Thanh toán VNPay thành công. Mã GD: ' . $transactionNo);
        } else {
            $this->paymentModel->markFailed(
                (int) $payment['id'],
                json_encode($_GET, JSON_UNESCAPED_UNICODE)
            );
            $this->bookingModel->updatePaymentStatus((int) $payment['booking_id'], 'unpaid');
            flash('error', 'Thanh toán thất bại.');
        }

        redirect(BASE_URL . '/booking/' . (int) $payment['booking_id']);
    }

    public function vnpayIpn(): void
    {
        $config = require __DIR__ . '/../config/vnpay.php';

        header('Content-Type: application/json');

        $inputData = $_GET;
        $receivedHash = (string) ($inputData['vnp_SecureHash'] ?? '');

        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);
        ksort($inputData);

        $hashString = http_build_query($inputData, '', '&', PHP_QUERY_RFC3986);
        $calculatedHash = hash_hmac('sha512', $hashString, trim($config['hash_secret']));

        if (!hash_equals($calculatedHash, $receivedHash)) {
            echo json_encode(['RspCode' => '97', 'Message' => 'Invalid signature']);
            exit;
        }

        $txnRef = (string) ($_GET['vnp_TxnRef'] ?? '');
        $responseCode = (string) ($_GET['vnp_ResponseCode'] ?? '');

        $payment = $this->paymentModel->findByTransactionId($txnRef);

        if (!$payment) {
            echo json_encode(['RspCode' => '01', 'Message' => 'Order not found']);
            exit;
        }

        if ($this->paymentModel->isProcessedTransaction($txnRef)) {
            echo json_encode(['RspCode' => '00', 'Message' => 'Already processed']);
            exit;
        }

        if ($responseCode === '00') {
            $this->paymentModel->markSuccess(
                (int) $payment['id'],
                json_encode($_GET, JSON_UNESCAPED_UNICODE),
                date('Y-m-d H:i:s')
            );
            $this->bookingModel->updatePaymentStatus((int) $payment['booking_id'], 'paid');
        } else {
            $this->paymentModel->markFailed(
                (int) $payment['id'],
                json_encode($_GET, JSON_UNESCAPED_UNICODE)
            );
            $this->bookingModel->updatePaymentStatus((int) $payment['booking_id'], 'unpaid');
        }

        echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Success']);
        exit;
    }
}
