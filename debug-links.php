<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/core/helpers.php';

echo "<h2>Email Link Debug</h2>";

echo "<p><strong>Current BASE_URL:</strong> " . BASE_URL . "</p>";

$token = bin2hex(random_bytes(32));
$verifyLink = BASE_URL . '/verify-email?token=' . urlencode($token);

echo "<p><strong>Example Verify Link:</strong></p>";
echo "<p><a href='" . $verifyLink . "' target='_blank'>" . $verifyLink . "</a></p>";

$resetToken = bin2hex(random_bytes(32));
$resetLink = BASE_URL . '/reset-password?token=' . urlencode($resetToken);

echo "<p><strong>Example Reset Link:</strong></p>";
echo "<p><a href='" . $resetLink . "' target='_blank'>" . $resetLink . "</a></p>";

echo "<hr>";
echo "<h3>Kiểm tra:</h3>";
echo "<ul>";
echo "<li>✓ Truy cập vào email của bạn, copy link đầy đủ từ email</li>";
echo "<li>✓ So sánh với các link ở trên</li>";
echo "<li>✓ Nếu khác nhau, cập nhật APP_URL trong .env file</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>APP_URL trong .env:</h3>";
$envContent = file_get_contents(__DIR__ . '/.env');
$lines = explode("\n", $envContent);
foreach ($lines as $line) {
    if (strpos($line, 'APP_URL') === 0) {
        echo "<p>" . htmlspecialchars($line) . "</p>";
    }
}

echo "<hr>";
echo "<p><a href='index.php'>← Back to Home</a></p>";
?>
