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
            // SECURITY: Verify payment_method is at_counter
            if ($booking['payment_method'] !== 'at_counter') {
                flash('error', 'Booking này không phải thanh toán tại quầy.');
                redirect(BASE_URL . '/booking/' . $bookingId);
            }
            
            // SECURITY: Verify payment_status is unpaid
            if ($booking['payment_status'] !== 'unpaid') {
                flash('error', 'Booking này đã được thanh toán.');
                redirect(BASE_URL . '/booking/' . $bookingId);
            }
            
            // SECURITY: Check if payment already exists for this booking
            $existingPayment = $this->paymentModel->findByBookingId($bookingId);
            if ($existingPayment && $existingPayment['status'] === 'success') {
                flash('success', 'Booking đã được ghi nhận thanh toán tại quầy.');
                redirect(BASE_URL . '/booking/' . $bookingId);
            }
            
            if ($existingPayment) {
                // Update existing payment (idempotent)
                $this->paymentModel->markSuccess(
                    (int) $existingPayment['id'],
                    json_encode(['method' => 'at_counter', 'paid_at' => date('Y-m-d H:i:s')]),
                    date('Y-m-d H:i:s')
                );
            } else {
                // Create new payment record
                $this->paymentModel->create([
                    'booking_id' => $bookingId,
                    'user_id' => (int) Auth::id(),
                    'gateway' => 'cash',
                    'transaction_id' => 'CASH_' . $bookingId . '_' . time(),
                    'amount' => (float) $booking['total_price'],
                    'currency' => 'VND',
                    'status' => 'success',
                    'gateway_response' => json_encode(['method' => 'at_counter', 'paid_at' => date('Y-m-d H:i:s')]),
                    'paid_at' => date('Y-m-d H:i:s'),
                ]);
            }
            
            // Update booking payment_status
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

        $bookingId = (int)($_GET['booking_id'] ?? 0);
        $booking = $this->bookingModel->findById($bookingId);

        if (!$booking) {
            flash('error', 'Không tìm thấy lịch hẹn.');
            redirect(BASE_URL . '/my-bookings');
        }

        // SECURITY: Ownership check - booking must belong to current user
        if ((int) $booking['user_id'] !== (int) Auth::id()) {
            http_response_code(403);
            flash('error', 'Bạn không có quyền thanh toán lịch hẹn này.');
            redirect(BASE_URL . '/my-bookings');
        }

        // SECURITY: Verify payment_method is online
        if ($booking['payment_method'] !== 'online') {
            flash('error', 'Booking này không được đặt thanh toán online.');
            redirect(BASE_URL . '/booking/' . $bookingId);
        }

        // SECURITY: Verify booking not already paid
        if ($booking['payment_status'] === 'paid') {
            flash('error', 'Booking này đã được thanh toán.');
            redirect(BASE_URL . '/booking/' . $bookingId);
        }

        $amount = (float)$booking['total_price'];
        $vnp_TxnRef = 'BOOK_' . $bookingId . '_' . time();

        // Lưu payment pending
        $this->paymentModel->create([
            'booking_id'     => $bookingId,
            'user_id'        => (int)$booking['user_id'],
            'gateway'        => 'vnpay',
            'transaction_id' => $vnp_TxnRef,
            'amount'         => $amount,
            'currency'       => 'VND',
            'status'         => 'pending',
        ]);

        $inputData = [
            'vnp_Version'    => $config['version'],
            'vnp_Command'    => 'pay',
            'vnp_TmnCode'    => trim($config['tmn_code']),
            'vnp_Amount'     => (int)round($amount * 100),
            'vnp_CurrCode'   => 'VND',
            'vnp_TxnRef'     => $vnp_TxnRef,
            'vnp_OrderInfo'  => 'Thanh toan booking ' . $bookingId,
            'vnp_OrderType'  => 'other',
            'vnp_Locale'     => 'vn',
            'vnp_ReturnUrl'  => trim($config['return_url']),
            'vnp_IpAddr'     => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_ExpireDate' => date('YmdHis', strtotime('+15 minutes')),
        ];

        // Sắp xếp tham số
        ksort($inputData);

        // Tạo chuỗi hash (CHÍNH XÁC theo VNPay sample code)
        $hashString = "";
        $queryString = "";
        $i = 0;
        
        foreach ($inputData as $key => $value) {
            $value = (string)$value;
            $encodedKey = urlencode($key);
            $encodedValue = urlencode($value);
            
            if ($i == 0) {
                $hashString .= $encodedKey . "=" . $encodedValue;
            } else {
                $hashString .= "&" . $encodedKey . "=" . $encodedValue;
            }
            $queryString .= $encodedKey . "=" . $encodedValue . "&";
            $i++;
        }

        // Tính chữ ký SHA512
        $vnpSecureHash = hash_hmac(
            'sha512',
            $hashString,
            trim($config['hash_secret']),
            false
        );

        // Tạo URL thanh toán
        $paymentUrl = $config['pay_url'] . "?" . $queryString . "vnp_SecureHash=" . $vnpSecureHash;

        header('Location: ' . $paymentUrl);
        exit;
    }

public function vnpayReturn(): void
{
    $config = require __DIR__ . '/../config/vnpay.php';

    $inputData = $_GET;
    $receivedHash = (string)($inputData['vnp_SecureHash'] ?? '');

    // DEBUG: Log all received parameters
    error_log("=== VNPay Return - All Parameters ===");
    error_log(json_encode($_GET, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    // Remove VNPay signature and non-VNPay parameters
    unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);
    
    // IMPORTANT: Remove any non-VNPay parameters (like 'path' from routing)
    foreach ($inputData as $key => $value) {
        if (strpos($key, 'vnp_') !== 0) {
            unset($inputData[$key]);
        }
    }
    
    ksort($inputData);

    // Tạo chuỗi hash giống lúc gửi
    $hashString = "";
    $i = 0;
    
    foreach ($inputData as $key => $value) {
        $value = (string)$value;
        $encodedKey = urlencode($key);
        $encodedValue = urlencode($value);
        
        if ($i == 0) {
            $hashString .= $encodedKey . "=" . $encodedValue;
        } else {
            $hashString .= "&" . $encodedKey . "=" . $encodedValue;
        }
        $i++;
    }

    $calculatedHash = hash_hmac(
        'sha512',
        $hashString,
        trim($config['hash_secret'])
    );

    // DEBUG: Log signature verification
    error_log("=== VNPay Return Debug ===");
    error_log("Received Hash: " . $receivedHash);
    error_log("Calculated Hash: " . $calculatedHash);
    error_log("Hash String: " . $hashString);
    error_log("Hash Secret: " . trim($config['hash_secret']));
    error_log("Match: " . (hash_equals($calculatedHash, $receivedHash) ? "YES" : "NO"));

    // SECURITY: Always verify signature using hash_equals() to prevent timing attacks
    if (!hash_equals($calculatedHash, $receivedHash)) {
        error_log("SIGNATURE VERIFICATION FAILED");
        flash('error', 'Sai chữ ký bảo mật từ VNPay. Kiểm tra lại TMN Code và Hash Secret.');
        redirect(BASE_URL . '/my-bookings');
    }

    $txnRef = $_GET['vnp_TxnRef'] ?? '';
    $responseCode = $_GET['vnp_ResponseCode'] ?? '';
    $transactionNo = $_GET['vnp_TransactionNo'] ?? '';
    $amount = (int)($_GET['vnp_Amount'] ?? 0);

    $payment = $this->paymentModel->findByTransactionId($txnRef);

    if (!$payment) {
        flash('error', 'Không tìm thấy giao dịch.');
        redirect(BASE_URL . '/my-bookings');
    }

    // SECURITY: Prevent double processing - check if already processed
    if ($this->paymentModel->isProcessedTransaction($txnRef)) {
        flash('success', 'Giao dịch này đã được xử lý trước đó.');
        redirect(BASE_URL . '/booking/' . (int)$payment['booking_id']);
    }

    if ($responseCode === '00') {
        $this->paymentModel->markSuccess(
            (int)$payment['id'],
            json_encode($_GET, JSON_UNESCAPED_UNICODE),
            date('Y-m-d H:i:s')
        );
        $this->bookingModel->updatePaymentStatus((int)$payment['booking_id'], 'paid');
        flash('success', 'Thanh toán VNPay thành công. Mã GD: ' . $transactionNo);
    } else {
        $this->paymentModel->markFailed(
            (int)$payment['id'],
            json_encode($_GET, JSON_UNESCAPED_UNICODE)
        );
        $this->bookingModel->updatePaymentStatus((int)$payment['booking_id'], 'unpaid');
        flash('error', 'Thanh toán thất bại.');
    }

    redirect(BASE_URL . '/booking/' . (int)$payment['booking_id']);
}

public function vnpayIpn(): void
{
    $config = require __DIR__ . '/../config/vnpay.php';

    header('Content-Type: application/json');

    $inputData = $_GET;
    $receivedHash = (string)($inputData['vnp_SecureHash'] ?? '');

    unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);
    ksort($inputData);

    $hashString = http_build_query($inputData, '', '&', PHP_QUERY_RFC3986);
    $calculatedHash = hash_hmac('sha512', $hashString, trim($config['hash_secret']));

    // SECURITY: Always verify signature
    if (!hash_equals($calculatedHash, $receivedHash)) {
        echo json_encode(['RspCode' => '97', 'Message' => 'Invalid signature']);
        exit;
    }

    $txnRef = $_GET['vnp_TxnRef'] ?? '';
    $responseCode = $_GET['vnp_ResponseCode'] ?? '';

    $payment = $this->paymentModel->findByTransactionId($txnRef);

    if (!$payment) {
        echo json_encode(['RspCode' => '01', 'Message' => 'Order not found']);
        exit;
    }

    // SECURITY: Prevent double processing
    if ($this->paymentModel->isProcessedTransaction($txnRef)) {
        echo json_encode(['RspCode' => '00', 'Message' => 'Already processed']);
        exit;
    }

    if ($responseCode === '00') {
        $this->paymentModel->markSuccess(
            (int)$payment['id'],
            json_encode($_GET, JSON_UNESCAPED_UNICODE),
            date('Y-m-d H:i:s')
        );
        $this->bookingModel->updatePaymentStatus((int)$payment['booking_id'], 'paid');
    } else {
        $this->paymentModel->markFailed(
            (int)$payment['id'],
            json_encode($_GET, JSON_UNESCAPED_UNICODE)
        );
        $this->bookingModel->updatePaymentStatus((int)$payment['booking_id'], 'unpaid');
    }

    echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Success']);
    exit;
}

    private function formatVnpayDate(string $payDate): ?string
    {
        if ($payDate === '' || strlen($payDate) !== 14) {
            return date('Y-m-d H:i:s');
        }

        $dt = DateTime::createFromFormat('YmdHis', $payDate);
        return $dt ? $dt->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
    }

    public function momo(): void
{
    Auth::requireLogin();

    $config = require __DIR__ . '/../config/momo.php';

    $bookingId = (int)($_GET['booking_id'] ?? 0);
    if ($bookingId <= 0) {
        flash('error', 'Thiếu mã lịch hẹn.');
        redirect(BASE_URL . '/my-bookings');
    }

    $booking = $this->bookingModel->findById($bookingId);
    if (!$booking) {
        flash('error', 'Không tìm thấy lịch hẹn.');
        redirect(BASE_URL . '/my-bookings');
    }

    if ((int)$booking['user_id'] !== (int)Auth::id()) {
        flash('error', 'Bạn không có quyền thanh toán lịch hẹn này.');
        redirect(BASE_URL . '/my-bookings');
    }

    $existingPayment = $this->paymentModel->findByBookingId($bookingId);
    if ($existingPayment && ($existingPayment['status'] ?? '') === 'success') {
        flash('success', 'Lịch hẹn này đã được thanh toán.');
        redirect(BASE_URL . '/booking/' . $bookingId);
    }

    $amount = (int)round((float)($booking['total_price'] ?? 0));
    if ($amount < 1000) {
        flash('error', 'Số tiền thanh toán MoMo phải từ 1.000 VND.');
        redirect(BASE_URL . '/booking/' . $bookingId);
    }

    if ($existingPayment && ($existingPayment['status'] ?? '') === 'pending' && ($existingPayment['gateway'] ?? '') === 'momo') {
        $orderId = (string)$existingPayment['transaction_id'];
    } else {
        $orderId = 'MOMO_BOOK_' . $bookingId . '_' . time();

        $this->paymentModel->create([
            'booking_id'     => $bookingId,
            'user_id'        => (int)$booking['user_id'],
            'gateway'        => 'momo',
            'transaction_id' => $orderId,
            'amount'         => $amount,
            'currency'       => 'VND',
            'status'         => 'pending',
        ]);
    }

    $requestId = (string)time();
    $orderInfo = 'Thanh toan booking ' . $bookingId;
    $extraData = '';

    $rawHash = "accessKey=" . $config['access_key']
        . "&amount=" . $amount
        . "&extraData=" . $extraData
        . "&ipnUrl=" . $config['ipn_url']
        . "&orderId=" . $orderId
        . "&orderInfo=" . $orderInfo
        . "&partnerCode=" . $config['partner_code']
        . "&redirectUrl=" . $config['redirect_url']
        . "&requestId=" . $requestId
        . "&requestType=" . $config['request_type'];

    $signature = hash_hmac('sha256', $rawHash, $config['secret_key']);

    $data = [
        'partnerCode' => $config['partner_code'],
        'partnerName' => 'Test',
        'storeId'     => 'MomoTestStore',
        'requestId'   => $requestId,
        'amount'      => $amount,
        'orderId'     => $orderId,
        'orderInfo'   => $orderInfo,
        'redirectUrl' => $config['redirect_url'],
        'ipnUrl'      => $config['ipn_url'],
        'lang'        => $config['lang'],
        'extraData'   => $extraData,
        'requestType' => $config['request_type'],
        'signature'   => $signature,
    ];

    $ch = curl_init($config['endpoint']);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    $result = curl_exec($ch);
    curl_close($ch);

    $jsonResult = json_decode($result, true);

    if (!is_array($jsonResult) || empty($jsonResult['payUrl'])) {
        flash('error', 'Không tạo được liên kết thanh toán MoMo.');
        redirect(BASE_URL . '/booking/' . $bookingId);
    }

    header('Location: ' . $jsonResult['payUrl']);
    exit;
}

public function momoReturn(): void
{
    $orderId = trim((string)($_GET['orderId'] ?? ''));
    $resultCode = trim((string)($_GET['resultCode'] ?? ''));
    $message = trim((string)($_GET['message'] ?? ''));
    $transId = trim((string)($_GET['transId'] ?? ''));

    $payment = $this->paymentModel->findByTransactionId($orderId);

    if (!$payment) {
        flash('error', 'Không tìm thấy giao dịch MoMo.');
        redirect(BASE_URL . '/my-bookings');
    }

    // SECURITY: Prevent double processing
    if ($this->paymentModel->isProcessedTransaction($orderId)) {
        flash('success', 'Giao dịch này đã được xử lý trước đó.');
        redirect(BASE_URL . '/booking/' . (int)$payment['booking_id']);
    }

    if ((string)$payment['status'] === 'pending') {
        if ($resultCode === '0') {
            $this->paymentModel->markSuccess(
                (int)$payment['id'],
                json_encode($_GET, JSON_UNESCAPED_UNICODE),
                date('Y-m-d H:i:s')
            );
            $this->bookingModel->updatePaymentStatus((int)$payment['booking_id'], 'paid');
        } else {
            $this->paymentModel->markFailed(
                (int)$payment['id'],
                json_encode($_GET, JSON_UNESCAPED_UNICODE)
            );
            $this->bookingModel->updatePaymentStatus((int)$payment['booking_id'], 'failed');
        }
    }

    flash(
        $resultCode === '0' ? 'success' : 'error',
        $resultCode === '0'
            ? 'Thanh toán MoMo thành công. Mã giao dịch: ' . $transId
            : 'Thanh toán MoMo thất bại: ' . $message
    );

    redirect(BASE_URL . '/booking/' . (int)$payment['booking_id']);
}

public function momoIpn(): void
{
    header('Content-Type: application/json; charset=utf-8');

    $config = require __DIR__ . '/../config/momo.php';

    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);

    if (!is_array($data)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
        exit;
    }

    $signature = (string)($data['signature'] ?? '');

    $rawHash = "accessKey=" . $config['access_key']
        . "&amount=" . ($data['amount'] ?? '')
        . "&extraData=" . ($data['extraData'] ?? '')
        . "&message=" . ($data['message'] ?? '')
        . "&orderId=" . ($data['orderId'] ?? '')
        . "&orderInfo=" . ($data['orderInfo'] ?? '')
        . "&orderType=" . ($data['orderType'] ?? '')
        . "&partnerCode=" . ($data['partnerCode'] ?? '')
        . "&payType=" . ($data['payType'] ?? '')
        . "&requestId=" . ($data['requestId'] ?? '')
        . "&responseTime=" . ($data['responseTime'] ?? '')
        . "&resultCode=" . ($data['resultCode'] ?? '')
        . "&transId=" . ($data['transId'] ?? '');

    $checkSignature = hash_hmac('sha256', $rawHash, $config['secret_key']);

    // SECURITY: Always verify signature
    if (!hash_equals($checkSignature, $signature)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
        exit;
    }

    $orderId = trim((string)($data['orderId'] ?? ''));
    $payment = $this->paymentModel->findByTransactionId($orderId);

    if (!$payment) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found']);
        exit;
    }

    // SECURITY: Prevent double processing
    if ($this->paymentModel->isProcessedTransaction($orderId)) {
        echo json_encode(['status' => 'ok', 'message' => 'Already processed']);
        exit;
    }

    if ((string)$payment['status'] !== 'pending') {
        echo json_encode(['status' => 'ok', 'message' => 'Already processed']);
        exit;
    }

    if ((string)($data['resultCode'] ?? '') === '0') {
        $this->paymentModel->markSuccess(
            (int)$payment['id'],
            json_encode($data, JSON_UNESCAPED_UNICODE),
            date('Y-m-d H:i:s')
        );
        $this->bookingModel->updatePaymentStatus((int)$payment['booking_id'], 'paid');
    } else {
        $this->paymentModel->markFailed(
            (int)$payment['id'],
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );
        $this->bookingModel->updatePaymentStatus((int)$payment['booking_id'], 'failed');
    }

    echo json_encode(['status' => 'ok']);
    exit;
}
}