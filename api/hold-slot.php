<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Staff.php';

header('Content-Type: application/json; charset=utf-8');

Auth::start();

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Can dang nhap.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method khong hop le.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$staffId = (int) ($_POST['staff_id'] ?? 0);
$bookingDate = trim($_POST['booking_date'] ?? '');
$startTime = trim($_POST['start_time'] ?? '');
$duration = (int) ($_POST['duration'] ?? 0);

if ($staffId <= 0 || $bookingDate === '' || $startTime === '' || $duration <= 0) {
    echo json_encode(['success' => false, 'message' => 'Du lieu khong hop le.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$startTimestamp = strtotime($bookingDate . ' ' . $startTime);
if ($startTimestamp === false || ($startTimestamp - time()) < (2 * 60 * 60)) {
    echo json_encode(['success' => false, 'message' => 'Khung gio khong hop le.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$endTime = date('H:i:s', strtotime($startTime . ' +' . $duration . ' minutes'));
$startTime = date('H:i:s', strtotime($startTime));
$sessionId = session_id();

$staffModel = new Staff();
$bookingModel = new Booking();

if (!$staffModel->findActiveById($staffId)) {
    echo json_encode(['success' => false, 'message' => 'Nhan vien khong hop le.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$staffModel->isWorkingOn($staffId, $bookingDate) || $staffModel->isDayOff($staffId, $bookingDate)) {
    echo json_encode(['success' => false, 'message' => 'Nhan vien khong lam viec khung nay.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($bookingModel->hasStaffConflict($staffId, $bookingDate, $startTime, $endTime)) {
    echo json_encode(['success' => false, 'message' => 'Khung gio da co booking.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($bookingModel->hasHeldConflict($staffId, $bookingDate, $startTime, $endTime, null, $sessionId)) {
    echo json_encode(['success' => false, 'message' => 'Khung gio dang duoc giu tam.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $bookingModel->clearExpiredHolds();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $bookingModel->createHold([
        'user_id' => (int) Auth::id(),
        'session_id' => $sessionId,
        'staff_id' => $staffId,
        'service_date' => $bookingDate,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'expires_at' => $expiresAt,
    ]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Chua co bang booking_holds. Hay import lai database/schema.sql.'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Da giu cho tam trong 10 phut.',
    'expires_at' => $expiresAt,
], JSON_UNESCAPED_UNICODE);
