<?php
// Debug VNPay signature
require_once __DIR__ . '/core/helpers.php';

$config = [
    'version'      => '2.1.0',
    'tmn_code'     => '5652YMTY',
    'hash_secret'  => '2AT2HZOW2D58PYMT5BJ6B24JHK98OGEN',
    'pay_url'      => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
];

$bookingId = 1;
$amount = 250000;

$inputData = [
    'vnp_Version'    => $config['version'],
    'vnp_Command'    => 'pay',
    'vnp_TmnCode'    => trim($config['tmn_code']),
    'vnp_Amount'     => (int)round($amount * 100),
    'vnp_CurrCode'   => 'VND',
    'vnp_TxnRef'     => 'BOOK_' . $bookingId . '_' . time(),
    'vnp_OrderInfo'  => 'Thanh toan booking ' . $bookingId,
    'vnp_OrderType'  => 'other',
    'vnp_Locale'     => 'vn',
    'vnp_ReturnUrl'  => 'http://localhost/barber-spa/payment/vnpay-return',
    'vnp_IpAddr'     => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
    'vnp_CreateDate' => date('YmdHis'),
    'vnp_ExpireDate' => date('YmdHis', strtotime('+15 minutes')),
];

echo "=== VNPay Signature Debug ===\n\n";

echo "TMN Code: " . $config['tmn_code'] . "\n";
echo "Hash Secret: " . $config['hash_secret'] . "\n";
echo "Amount: " . number_format($amount) . " VND\n";
echo "Amount (in xu): " . $inputData['vnp_Amount'] . "\n\n";

// Method 1: Using http_build_query RFC3986
ksort($inputData);
$hashString1 = http_build_query($inputData, '', '&', PHP_QUERY_RFC3986);
$signature1 = hash_hmac('sha512', $hashString1, trim($config['hash_secret']));

echo "=== Method 1: http_build_query (PHP_QUERY_RFC3986) ===\n";
echo "Hash String:\n" . $hashString1 . "\n\n";
echo "Signature:\n" . $signature1 . "\n\n";

// Method 2: Manual string building (like VNPay sample)
$hashString2 = "";
$i = 0;
foreach ($inputData as $key => $value) {
    if ($i == 0) {
        $hashString2 .= urlencode($key) . "=" . urlencode($value);
    } else {
        $hashString2 .= "&" . urlencode($key) . "=" . urlencode($value);
    }
    $i++;
}
$signature2 = hash_hmac('sha512', $hashString2, trim($config['hash_secret']));

echo "=== Method 2: Manual String Building ===\n";
echo "Hash String:\n" . $hashString2 . "\n\n";
echo "Signature:\n" . $signature2 . "\n\n";

echo "=== Comparison ===\n";
echo "Method 1 == Method 2: " . ($hashString1 === $hashString2 ? "YES" : "NO") . "\n";
echo "Signature 1 == Signature 2: " . ($signature1 === $signature2 ? "YES" : "NO") . "\n\n";

// Full payment URL
$paymentUrl = $config['pay_url'] . '?' . $hashString1 . '&vnp_SecureHash=' . $signature1;
echo "=== Payment URL ===\n";
echo $paymentUrl . "\n";
?>
