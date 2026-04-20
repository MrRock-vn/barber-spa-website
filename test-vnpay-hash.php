<?php
// Test VNPay Hash Calculation
$vnp_TmnCode = '5652YMTY';
$vnp_HashSecret = '2AT2HZOW2D58PYMT5BJ6B24JHK98OGEN';

// Simulate a payment request
$inputData = [
    'vnp_Version'    => '2.1.0',
    'vnp_Command'    => 'pay',
    'vnp_TmnCode'    => $vnp_TmnCode,
    'vnp_Amount'     => 25000000,  // 250,000 VND in xu
    'vnp_CurrCode'   => 'VND',
    'vnp_TxnRef'     => 'BOOK_10_1713607014',
    'vnp_OrderInfo'  => 'Thanh toan booking 10',
    'vnp_OrderType'  => 'other',
    'vnp_Locale'     => 'vn',
    'vnp_ReturnUrl'  => 'http://localhost/barber-spa/payment/vnpay-return',
    'vnp_IpAddr'     => '127.0.0.1',
    'vnp_CreateDate' => '20260420093654',
    'vnp_ExpireDate' => '20260420093954',
];

echo "=== VNPay Hash Test ===\n\n";
echo "TMN Code: $vnp_TmnCode\n";
echo "Hash Secret: $vnp_HashSecret\n\n";

ksort($inputData);

// Method 1: RFC3986 (PHP_QUERY_RFC3986)
$hashString1 = http_build_query($inputData, '', '&', PHP_QUERY_RFC3986);
$hash1 = hash_hmac('sha512', $hashString1, $vnp_HashSecret);

echo "=== Method 1: http_build_query (RFC3986) ===\n";
echo "Hash String:\n$hashString1\n\n";
echo "Hash:\n$hash1\n\n";

// Method 2: Manual building (like sample code)
$hashString2 = "";
$queryString = "";
$i = 0;

foreach ($inputData as $key => $value) {
    $value = (string)$value;
    if ($i == 0) {
        $hashString2 .= urlencode($key) . "=" . urlencode($value);
    } else {
        $hashString2 .= "&" . urlencode($key) . "=" . urlencode($value);
    }
    $queryString .= urlencode($key) . "=" . urlencode($value) . "&";
    $i++;
}

$hash2 = hash_hmac('sha512', $hashString2, $vnp_HashSecret);

echo "=== Method 2: Manual Building ===\n";
echo "Hash String:\n$hashString2\n\n";
echo "Hash:\n$hash2\n\n";

// Method 3: Without URL encoding
$hashString3 = "";
$i = 0;

foreach ($inputData as $key => $value) {
    $value = (string)$value;
    if ($i == 0) {
        $hashString3 .= $key . "=" . $value;
    } else {
        $hashString3 .= "&" . $key . "=" . $value;
    }
    $i++;
}

$hash3 = hash_hmac('sha512', $hashString3, $vnp_HashSecret);

echo "=== Method 3: No URL Encoding ===\n";
echo "Hash String:\n$hashString3\n\n";
echo "Hash:\n$hash3\n\n";

echo "=== Comparison ===\n";
echo "Method 1 == Method 2: " . ($hash1 === $hash2 ? "YES" : "NO") . "\n";
echo "Method 1 == Method 3: " . ($hash1 === $hash3 ? "YES" : "NO") . "\n";
echo "Method 2 == Method 3: " . ($hash2 === $hash3 ? "YES" : "NO") . "\n";

echo "\n=== Payment URL ===\n";
$paymentUrl = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?' . $queryString . 'vnp_SecureHash=' . $hash2;
echo substr($paymentUrl, 0, 200) . "...\n";
?>
