<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/Staff.php';
require_once __DIR__ . '/../models/Booking.php';

header('Content-Type: application/json; charset=utf-8');

Auth::start();

$staffId = (int) ($_GET['staff_id'] ?? 0);
$bookingDate = trim($_GET['booking_date'] ?? '');
$duration = (int) ($_GET['duration'] ?? 0);

if ($staffId <= 0 || $bookingDate === '' || $duration <= 0) {
    echo json_encode([
        'success' => false,
        'slots' => [],
        'message' => 'Dữ liệu không hợp lệ.'
    ]);
    exit;
}

$staffModel = new Staff();
$bookingModel = new Booking();

if (!$staffModel->isWorkingOn($staffId, $bookingDate) || $staffModel->isDayOff($staffId, $bookingDate)) {
    echo json_encode([
        'success' => true,
        'slots' => []
    ]);
    exit;
}

$dayOfWeek = (int) date('w', strtotime($bookingDate));
$schedule = $staffModel->getScheduleByDay($staffId, $dayOfWeek);

if (!$schedule || (int) $schedule['is_off'] === 1) {
    echo json_encode([
        'success' => true,
        'slots' => []
    ]);
    exit;
}

$slots = [];
$start = strtotime($schedule['start_time']);
$end = strtotime($schedule['end_time']);

for ($time = $start; $time < $end; $time += 30 * 60) {
    $startTime = date('H:i:s', $time);
    $endTime = date('H:i:s', strtotime($startTime . ' +' . $duration . ' minutes'));

    if (strtotime($endTime) > $end) {
        continue;
    }

    $startTimestamp = strtotime($bookingDate . ' ' . $startTime);
    if (($startTimestamp - time()) < (2 * 60 * 60)) {
        continue;
    }

    if ($bookingModel->hasStaffConflict($staffId, $bookingDate, $startTime, $endTime)) {
        continue;
    }

    if ($bookingModel->hasHeldConflict($staffId, $bookingDate, $startTime, $endTime, null, session_id())) {
        continue;
    }

    $slots[] = date('H:i', $time);
}

echo json_encode([
    'success' => true,
    'slots' => $slots
], JSON_UNESCAPED_UNICODE);
