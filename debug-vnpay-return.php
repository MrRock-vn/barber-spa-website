<?php
// Debug VNPay Return from actual GET params
require_once __DIR__ . '/core/helpers.php';

$config = [
    'hash_secret' => '2AT2HZOW2D58PYMT5BJ6B24JHK98OGEN',
];

echo "=== VNPay Return Debug ===\n\n";

$inputData = $_GET;
$receivedHash = (string)($inputData['vnp_SecureHash'] ?? '');

echo "Received Hash:\n" . $receivedHash . "\n\n";

unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);
ksort($inputData);

echo "=== All GET Parameters (sorted) ===\n";
foreach ($inputData as $key => $value) {
    echo "$key: $value\n";
}
echo "\n";

// Method 1: Manual building with urlencode
$hashString1 = "";
$i = 0;

foreach ($inputData as $key => $value) {
    $value = (string)$value;
    $encodedKey = urlencode($key);
    $encodedValue = urlencode($value);
    
    if ($i == 0) {
        $hashString1 .= $encodedKey . "=" . $encodedValue;
    } else {
        $hashString1 .= "&" . $encodedKey . "=" . $encodedValue;
    }
    $i++;
}

echo "=== Method 1: urlencode ===\n";
echo "Hash String:\n" . $hashString1 . "\n\n";

$hash1 = hash_hmac('sha512', $hashString1, trim($config['hash_secret']));
echo "Calculated Hash:\n" . $hash1 . "\n\n";
echo "Match: " . (hash_equals($hash1, $receivedHash) ? "YES ✅" : "NO ❌") . "\n\n";

// Method 2: http_build_query RFC3986
$hashString2 = http_build_query($inputData, '', '&', PHP_QUERY_RFC3986);
$hash2 = hash_hmac('sha512', $hashString2, trim($config['hash_secret']));

echo "=== Method 2: http_build_query (RFC3986) ===\n";
echo "Hash String:\n" . $hashString2 . "\n\n";
echo "Calculated Hash:\n" . $hash2 . "\n\n";
echo "Match: " . (hash_equals($hash2, $receivedHash) ? "YES ✅" : "NO ❌") . "\n\n";

// Method 3: No urlencode
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

$hash3 = hash_hmac('sha512', $hashString3, trim($config['hash_secret']));

echo "=== Method 3: No encoding ===\n";
echo "Hash String:\n" . $hashString3 . "\n\n";
echo "Calculated Hash:\n" . $hash3 . "\n\n";
echo "Match: " . (hash_equals($hash3, $receivedHash) ? "YES ✅" : "NO ❌") . "\n";
?>

$inputData = $_GET;
$receivedHash = (string)($inputData['vnp_SecureHash'] ?? '');

unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);
ksort($inputData);

echo "Received Hash: " . $receivedHash . "\n\n";

// Method 1: Manual string building
$hashString = "";
$i = 0;

foreach ($inputData as $key => $value) {
    $value = (string)$value;
    if ($i == 0) {
        $hashString .= urlencode($key) . "=" . urlencode($value);
    } else {
        $hashString .= "&" . urlencode($key) . "=" . urlencode($value);
    }
    $i++;
}

echo "Hash String:\n" . $hashString . "\n\n";

$calculatedHash = hash_hmac(
    'sha512',
    $hashString,
    trim($config['hash_secret'])
);

echo "Calculated Hash: " . $calculatedHash . "\n\n";

echo "=== Comparison ===\n";
echo "Hashes Match: " . (hash_equals($calculatedHash, $receivedHash) ? "YES ✅" : "NO ❌") . "\n";

?>
