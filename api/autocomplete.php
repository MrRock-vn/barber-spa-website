<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 2) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

$db = Database::getInstance();
$keyword = '%' . $q . '%';
$results = [];

$salonSql = "SELECT id, name
             FROM salons
             WHERE status = 'active'
               AND (name LIKE :salon_keyword1 OR district LIKE :salon_keyword2 OR city LIKE :salon_keyword3)
             ORDER BY total_bookings DESC, avg_rating DESC, id DESC
             LIMIT 4";
$salonStmt = $db->prepare($salonSql);
$salonStmt->execute([
    'salon_keyword1' => $keyword,
    'salon_keyword2' => $keyword,
    'salon_keyword3' => $keyword,
]);

foreach ($salonStmt->fetchAll() as $salon) {
    $results[] = [
        'type' => 'salon',
        'id' => (int) $salon['id'],
        'label' => $salon['name'],
        'url' => BASE_URL . '/salon/' . $salon['id'],
    ];
}

$serviceSql = "SELECT sv.id, sv.name, sv.salon_id, s.name AS salon_name
               FROM services sv
               INNER JOIN salons s ON s.id = sv.salon_id
               WHERE sv.is_active = 1
                 AND s.status = 'active'
                 AND sv.name LIKE :keyword
               ORDER BY sv.id DESC
               LIMIT 4";
$serviceStmt = $db->prepare($serviceSql);
$serviceStmt->execute(['keyword' => $keyword]);

foreach ($serviceStmt->fetchAll() as $service) {
    $results[] = [
        'type' => 'service',
        'id' => (int) $service['id'],
        'label' => $service['name'] . ' - ' . $service['salon_name'],
        'url' => BASE_URL . '/search?keyword=' . rawurlencode($service['name']),
    ];
}

echo json_encode(array_slice($results, 0, 8), JSON_UNESCAPED_UNICODE);
