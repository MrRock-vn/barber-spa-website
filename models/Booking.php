<?php

declare(strict_types=1);

class Booking
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM bookings WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $booking = $stmt->fetch();
        return $booking ?: null;
    }

    public function findDetailedById(int $id): ?array
    {
        $sql = "SELECT b.*,
                       u.name AS customer_name,
                       u.email AS customer_email,
                       s.name AS salon_name,
                       s.address AS salon_address,
                       s.city AS salon_city,
                       s.district AS salon_district,
                       st.name AS staff_name,
                       st.phone AS staff_phone
                FROM bookings b
                LEFT JOIN users u ON u.id = b.user_id
                LEFT JOIN salons s ON s.id = b.salon_id
                LEFT JOIN staff st ON st.id = b.staff_id
                WHERE b.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $booking = $stmt->fetch();
        return $booking ?: null;
    }

    public function getByUserId(int $userId, array $filters = []): array
    {
        $sql = "SELECT b.*,
                       s.name AS salon_name,
                       st.name AS staff_name
                FROM bookings b
                LEFT JOIN salons s ON s.id = b.salon_id
                LEFT JOIN staff st ON st.id = b.staff_id
                WHERE b.user_id = :user_id";
        $params = ['user_id' => $userId];

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = :status";
            $params['status'] = $filters['status'];
        }

        $sql .= " ORDER BY b.booking_date DESC, b.start_time DESC, b.id DESC";

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $stmt->bindValue(':limit', (int) $filters['limit'], PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $filters['offset'], PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countByUserId(int $userId, array $filters = []): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings
                WHERE user_id = :user_id";
        $params = ['user_id' => $userId];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function getBySalonId(int $salonId, array $filters = []): array
    {
        $sql = "SELECT b.*,
                       u.name AS customer_name,
                       u.email AS customer_email,
                       st.name AS staff_name
                FROM bookings b
                LEFT JOIN users u ON u.id = b.user_id
                LEFT JOIN staff st ON st.id = b.staff_id
                WHERE b.salon_id = :salon_id";
        $params = ['salon_id' => $salonId];

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['booking_date'])) {
            $sql .= " AND b.booking_date = :booking_date";
            $params['booking_date'] = $filters['booking_date'];
        }

        $sql .= " ORDER BY b.booking_date DESC, b.start_time DESC, b.id DESC";

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $stmt->bindValue(':limit', (int) $filters['limit'], PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $filters['offset'], PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countActiveByUserId(int $userId): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings
                WHERE user_id = :user_id
                  AND status IN ('pending', 'confirmed')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO bookings (
                    user_id,
                    salon_id,
                    staff_id,
                    services,
                    booking_date,
                    start_time,
                    end_time,
                    total_price,
                    status,
                    payment_method,
                    payment_status,
                    notes,
                    cancel_reason,
                    slot_held_until,
                    created_at,
                    updated_at
                ) VALUES (
                    :user_id,
                    :salon_id,
                    :staff_id,
                    :services,
                    :booking_date,
                    :start_time,
                    :end_time,
                    :total_price,
                    :status,
                    :payment_method,
                    :payment_status,
                    :notes,
                    :cancel_reason,
                    :slot_held_until,
                    NOW(),
                    NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $data['user_id'],
            'salon_id' => $data['salon_id'],
            'staff_id' => $data['staff_id'],
            'services' => $data['services'],
            'booking_date' => $data['booking_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'total_price' => $data['total_price'],
            'status' => $data['status'] ?? 'pending',
            'payment_method' => $data['payment_method'] ?? 'at_counter',
            'payment_status' => $data['payment_status'] ?? 'unpaid',
            'notes' => $data['notes'] ?? null,
            'cancel_reason' => $data['cancel_reason'] ?? null,
            'slot_held_until' => $data['slot_held_until'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * SECURITY: Create booking with transaction to prevent race condition (double booking)
     * Uses database-level locking to ensure slot availability
     */
    public function createWithTransaction(array $data): ?int
    {
        try {
            $this->db->beginTransaction();

            // SECURITY: Lock the staff row to prevent concurrent bookings
            $lockSql = "SELECT id FROM staff WHERE id = :staff_id FOR UPDATE";
            $lockStmt = $this->db->prepare($lockSql);
            $lockStmt->execute(['staff_id' => $data['staff_id']]);

            // Re-check for conflicts within transaction
            if ($this->hasStaffConflict(
                (int) $data['staff_id'],
                $data['booking_date'],
                $data['start_time'],
                $data['end_time']
            )) {
                $this->db->rollBack();
                return null;
            }

            if ($this->hasHeldConflict(
                (int) $data['staff_id'],
                $data['booking_date'],
                $data['start_time'],
                $data['end_time']
            )) {
                $this->db->rollBack();
                return null;
            }

            // Create booking
            $bookingId = $this->create($data);

            $this->db->commit();
            return $bookingId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return null;
        }
    }

    public function updateStatus(int $id, string $status, ?string $cancelReason = null): bool
    {
        $sql = "UPDATE bookings
                SET status = :status,
                    cancel_reason = :cancel_reason,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'status' => $status,
            'cancel_reason' => $cancelReason,
        ]);
    }

    public function updatePaymentStatus(int $id, string $paymentStatus): bool
    {
        $sql = "UPDATE bookings
                SET payment_status = :payment_status,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'payment_status' => $paymentStatus,
        ]);
    }

    public function reschedule(int $id, string $bookingDate, string $startTime, string $endTime): bool
    {
        $sql = "UPDATE bookings
                SET booking_date = :booking_date,
                    start_time = :start_time,
                    end_time = :end_time,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'booking_date' => $bookingDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    }

    public function holdSlot(int $id, string $holdUntil): bool
    {
        $sql = "UPDATE bookings
                SET slot_held_until = :slot_held_until,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'slot_held_until' => $holdUntil,
        ]);
    }

    public function clearHeldSlot(int $id): bool
    {
        $sql = "UPDATE bookings
                SET slot_held_until = NULL,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function hasStaffConflict(
        int $staffId,
        string $bookingDate,
        string $startTime,
        string $endTime,
        ?int $excludeBookingId = null
    ): bool {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings
                WHERE staff_id = :staff_id
                  AND booking_date = :booking_date
                  AND status IN ('pending', 'confirmed', 'completed')
                  AND start_time < :end_time
                  AND end_time > :start_time";
        $params = [
            'staff_id' => $staffId,
            'booking_date' => $bookingDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];

        if ($excludeBookingId !== null) {
            $sql .= " AND id != :exclude_booking_id";
            $params['exclude_booking_id'] = $excludeBookingId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        return ((int) ($row['total'] ?? 0)) > 0;
    }

    public function hasHeldConflict(
        int $staffId,
        string $bookingDate,
        string $startTime,
        string $endTime,
        ?int $excludeBookingId = null
    ): bool {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings
                WHERE staff_id = :staff_id
                  AND booking_date = :booking_date
                  AND slot_held_until IS NOT NULL
                  AND slot_held_until >= NOW()
                  AND status IN ('pending', 'confirmed')
                  AND start_time < :end_time
                  AND end_time > :start_time";
        $params = [
            'staff_id' => $staffId,
            'booking_date' => $bookingDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];

        if ($excludeBookingId !== null) {
            $sql .= " AND id != :exclude_booking_id";
            $params['exclude_booking_id'] = $excludeBookingId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        return ((int) ($row['total'] ?? 0)) > 0;
    }

    public function getUpcomingBySalonId(int $salonId, int $limit = 10): array
    {
        $sql = "SELECT b.*,
                       u.name AS customer_name,
                       st.name AS staff_name
                FROM bookings b
                LEFT JOIN users u ON u.id = b.user_id
                LEFT JOIN staff st ON st.id = b.staff_id
                WHERE b.salon_id = :salon_id
                  AND b.booking_date >= CURDATE()
                ORDER BY b.booking_date ASC, b.start_time ASC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':salon_id', $salonId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTodayBySalonId(int $salonId): array
    {
        $sql = "SELECT b.*,
                       u.name AS customer_name,
                       st.name AS staff_name
                FROM bookings b
                LEFT JOIN users u ON u.id = b.user_id
                LEFT JOIN staff st ON st.id = b.staff_id
                WHERE b.salon_id = :salon_id
                  AND b.booking_date = CURDATE()
                ORDER BY b.start_time ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['salon_id' => $salonId]);

        return $stmt->fetchAll();
    }

    public function countBySalonId(int $salonId): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings
                WHERE salon_id = :salon_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['salon_id' => $salonId]);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function canCancel(array $booking): bool
    {
        if (!in_array($booking['status'], ['pending', 'confirmed'], true)) {
            return false;
        }

        $bookingDateTime = strtotime($booking['booking_date'] . ' ' . $booking['start_time']);
        return $bookingDateTime !== false && ($bookingDateTime - time()) >= 0;
    }

    public function isEligibleForFullRefund(array $booking): bool
    {
        $bookingDateTime = strtotime($booking['booking_date'] . ' ' . $booking['start_time']);
        if ($bookingDateTime === false) {
            return false;
        }

        return ($bookingDateTime - time()) >= (2 * 60 * 60);
    }

    public function canReschedule(array $booking): bool
    {
        if (!in_array($booking['status'], ['pending', 'confirmed'], true)) {
            return false;
        }

        $bookingDateTime = strtotime($booking['booking_date'] . ' ' . $booking['start_time']);
        if ($bookingDateTime === false) {
            return false;
        }

        return ($bookingDateTime - time()) >= (4 * 60 * 60);
    }
        public function countTodayBySalonId(int $salonId): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings
                WHERE salon_id = :salon_id
                  AND booking_date = CURDATE()";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['salon_id' => $salonId]);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function countUpcomingBySalonId(int $salonId): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings
                WHERE salon_id = :salon_id
                  AND booking_date >= CURDATE()
                  AND status IN ('pending', 'confirmed')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['salon_id' => $salonId]);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function countCompletedBySalonId(int $salonId): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings
                WHERE salon_id = :salon_id
                  AND status = 'completed'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['salon_id' => $salonId]);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function sumRevenueBySalonId(int $salonId): float
    {
        $sql = "SELECT SUM(total_price) AS total
                FROM bookings
                WHERE salon_id = :salon_id
                  AND status = 'completed'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['salon_id' => $salonId]);

        $row = $stmt->fetch();
        return (float) ($row['total'] ?? 0);
    }

    public function getRecentBySalonId(int $salonId, int $limit = 5): array
    {
        $sql = "SELECT b.*,
                       u.name AS customer_name,
                       u.email AS customer_email,
                       st.name AS staff_name
                FROM bookings b
                LEFT JOIN users u ON u.id = b.user_id
                LEFT JOIN staff st ON st.id = b.staff_id
                WHERE b.salon_id = :salon_id
                ORDER BY b.id DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':salon_id', $salonId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
        public function getCompletedBySalonIdInRange(
        int $salonId,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $sql = "SELECT b.*,
                       u.name AS customer_name,
                       u.email AS customer_email,
                       st.name AS staff_name
                FROM bookings b
                LEFT JOIN users u ON u.id = b.user_id
                LEFT JOIN staff st ON st.id = b.staff_id
                WHERE b.salon_id = :salon_id
                  AND b.status = 'completed'";
        $params = ['salon_id' => $salonId];

        if (!empty($dateFrom)) {
            $sql .= " AND b.booking_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $sql .= " AND b.booking_date <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $sql .= " ORDER BY b.booking_date DESC, b.start_time DESC, b.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function sumRevenueBySalonIdInRange(
        int $salonId,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): float {
        $sql = "SELECT SUM(total_price) AS total
                FROM bookings
                WHERE salon_id = :salon_id
                  AND status = 'completed'";
        $params = ['salon_id' => $salonId];

        if (!empty($dateFrom)) {
            $sql .= " AND booking_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $sql .= " AND booking_date <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        return (float) ($row['total'] ?? 0);
    }

    public function countCompletedBySalonIdInRange(
        int $salonId,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings
                WHERE salon_id = :salon_id
                  AND status = 'completed'";
        $params = ['salon_id' => $salonId];

        if (!empty($dateFrom)) {
            $sql .= " AND booking_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $sql .= " AND booking_date <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }
        public function countAllBookings(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM bookings";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function countTodayBookings(): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings
                WHERE booking_date = CURDATE()";

        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function countBookingsByStatus(string $status): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings
                WHERE status = :status";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => $status]);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function getRevenueByLastDays(int $days = 7): array
    {
        $fromDate = date('Y-m-d', strtotime(sprintf('-%d days', $days - 1)));

        $sql = "SELECT booking_date,
                       SUM(total_price) AS revenue
                FROM bookings
                WHERE status = 'completed'
                  AND booking_date >= :from_date
                GROUP BY booking_date
                ORDER BY booking_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['from_date' => $fromDate]);

        return $stmt->fetchAll();
    }

    public function sumAllRevenue(): float
    {
        $sql = "SELECT SUM(total_price) AS total
                FROM bookings
                WHERE status = 'completed'";

        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();

        return (float) ($row['total'] ?? 0);
    }

    public function getRecentForAdmin(int $limit = 5): array
    {
        $sql = "SELECT b.*,
                       u.name AS customer_name,
                       s.name AS salon_name,
                       st.name AS staff_name
                FROM bookings b
                LEFT JOIN users u ON u.id = b.user_id
                LEFT JOIN salons s ON s.id = b.salon_id
                LEFT JOIN staff st ON st.id = b.staff_id
                ORDER BY b.id DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
        public function getAllForAdmin(array $filters = []): array
    {
        $sql = "SELECT b.*,
                       u.name AS customer_name,
                       u.email AS customer_email,
                       s.name AS salon_name,
                       st.name AS staff_name
                FROM bookings b
                LEFT JOIN users u ON u.id = b.user_id
                LEFT JOIN salons s ON s.id = b.salon_id
                LEFT JOIN staff st ON st.id = b.staff_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (
                        u.name LIKE :keyword1
                        OR u.email LIKE :keyword2
                        OR s.name LIKE :keyword3
                        OR st.name LIKE :keyword4
                        OR b.notes LIKE :keyword5
                    )";
            $keyword = '%' . $filters['keyword'] . '%';
            $params['keyword1'] = $keyword;
            $params['keyword2'] = $keyword;
            $params['keyword3'] = $keyword;
            $params['keyword4'] = $keyword;
            $params['keyword5'] = $keyword;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['booking_date'])) {
            $sql .= " AND b.booking_date = :booking_date";
            $params['booking_date'] = $filters['booking_date'];
        }

        if (!empty($filters['salon_id'])) {
            $sql .= " AND b.salon_id = :salon_id";
            $params['salon_id'] = (int) $filters['salon_id'];
        }

        $sql .= " ORDER BY b.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function countAllForAdmin(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings b
                LEFT JOIN users u ON u.id = b.user_id
                LEFT JOIN salons s ON s.id = b.salon_id
                LEFT JOIN staff st ON st.id = b.staff_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (
                        u.name LIKE :keyword1
                        OR u.email LIKE :keyword2
                        OR s.name LIKE :keyword3
                        OR st.name LIKE :keyword4
                        OR b.notes LIKE :keyword5
                    )";
            $keyword = '%' . $filters['keyword'] . '%';
            $params['keyword1'] = $keyword;
            $params['keyword2'] = $keyword;
            $params['keyword3'] = $keyword;
            $params['keyword4'] = $keyword;
            $params['keyword5'] = $keyword;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['booking_date'])) {
            $sql .= " AND b.booking_date = :booking_date";
            $params['booking_date'] = $filters['booking_date'];
        }

        if (!empty($filters['salon_id'])) {
            $sql .= " AND b.salon_id = :salon_id";
            $params['salon_id'] = (int) $filters['salon_id'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }
}