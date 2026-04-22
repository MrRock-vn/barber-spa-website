<?php

declare(strict_types=1);

class Staff
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM staff WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $staff = $stmt->fetch();
        return $staff ?: null;
    }

    public function findActiveById(int $id): ?array
    {
        $sql = "SELECT * FROM staff
                WHERE id = :id AND is_active = 1
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $staff = $stmt->fetch();
        return $staff ?: null;
    }

    public function getBySalonId(int $salonId, bool $onlyActive = false): array
    {
        $sql = "SELECT * FROM staff WHERE salon_id = :salon_id";
        $params = ['salon_id' => $salonId];

        if ($onlyActive) {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getActiveBySalonId(int $salonId): array
    {
        return $this->getBySalonId($salonId, true);
    }

    public function countBySalonId(int $salonId): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM staff
                WHERE salon_id = :salon_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['salon_id' => $salonId]);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO staff (
                    salon_id,
                    name,
                    phone,
                    avatar,
                    specialties,
                    is_active
                ) VALUES (
                    :salon_id,
                    :name,
                    :phone,
                    :avatar,
                    :specialties,
                    :is_active
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'salon_id' => $data['salon_id'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'avatar' => $data['avatar'] ?? null,
            'specialties' => $data['specialties'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE staff
                SET name = :name,
                    phone = :phone,
                    avatar = :avatar,
                    specialties = :specialties,
                    is_active = :is_active
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'avatar' => $data['avatar'] ?? null,
            'specialties' => $data['specialties'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
        ]);
    }

    public function deactivate(int $id): bool
    {
        $sql = "UPDATE staff
                SET is_active = 0
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM staff WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public function belongsToSalon(int $staffId, int $salonId): bool
    {
        $sql = "SELECT COUNT(*) AS total
                FROM staff
                WHERE id = :id
                  AND salon_id = :salon_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $staffId,
            'salon_id' => $salonId,
        ]);

        $row = $stmt->fetch();
        return ((int) ($row['total'] ?? 0)) > 0;
    }

    public function getSchedules(int $staffId): array
    {
        $sql = "SELECT * FROM staff_schedules
                WHERE staff_id = :staff_id
                ORDER BY day_of_week ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['staff_id' => $staffId]);

        return $stmt->fetchAll();
    }

    public function getScheduleByDay(int $staffId, int $dayOfWeek): ?array
    {
        $sql = "SELECT * FROM staff_schedules
                WHERE staff_id = :staff_id
                  AND day_of_week = :day_of_week
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'staff_id' => $staffId,
            'day_of_week' => $dayOfWeek,
        ]);

        $schedule = $stmt->fetch();
        return $schedule ?: null;
    }

    public function upsertSchedule(int $staffId, int $dayOfWeek, string $startTime, string $endTime, int $isOff = 0): bool
    {
        $existing = $this->getScheduleByDay($staffId, $dayOfWeek);

        if ($existing) {
            $sql = "UPDATE staff_schedules
                    SET start_time = :start_time,
                        end_time = :end_time,
                        is_off = :is_off
                    WHERE staff_id = :staff_id
                      AND day_of_week = :day_of_week";
        } else {
            $sql = "INSERT INTO staff_schedules (
                        staff_id,
                        day_of_week,
                        start_time,
                        end_time,
                        is_off
                    ) VALUES (
                        :staff_id,
                        :day_of_week,
                        :start_time,
                        :end_time,
                        :is_off
                    )";
        }

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'staff_id' => $staffId,
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_off' => $isOff,
        ]);
    }

    public function getDayOffs(int $staffId): array
    {
        $sql = "SELECT * FROM staff_day_off
                WHERE staff_id = :staff_id
                ORDER BY off_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['staff_id' => $staffId]);

        return $stmt->fetchAll();
    }

    public function addDayOff(int $staffId, string $offDate, ?string $reason = null): bool
    {
        $sql = "INSERT INTO staff_day_off (
                    staff_id,
                    off_date,
                    reason,
                    created_at
                ) VALUES (
                    :staff_id,
                    :off_date,
                    :reason,
                    NOW()
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'staff_id' => $staffId,
            'off_date' => $offDate,
            'reason' => $reason,
        ]);
    }

    public function removeDayOff(int $staffId, string $offDate): bool
    {
        $sql = "DELETE FROM staff_day_off
                WHERE staff_id = :staff_id
                  AND off_date = :off_date";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'staff_id' => $staffId,
            'off_date' => $offDate,
        ]);
    }

    public function isDayOff(int $staffId, string $bookingDate): bool
    {
        $sql = "SELECT COUNT(*) AS total
                FROM staff_day_off
                WHERE staff_id = :staff_id
                  AND off_date = :off_date";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'staff_id' => $staffId,
            'off_date' => $bookingDate,
        ]);

        $row = $stmt->fetch();
        return ((int) ($row['total'] ?? 0)) > 0;
    }

    public function isWorkingOn(int $staffId, string $bookingDate): bool
    {
        $dayOfWeek = (int) date('w', strtotime($bookingDate));

        $sql = "SELECT COUNT(*) AS total
                FROM staff_schedules
                WHERE staff_id = :staff_id
                  AND day_of_week = :day_of_week
                  AND is_off = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'staff_id' => $staffId,
            'day_of_week' => $dayOfWeek,
        ]);

        $row = $stmt->fetch();
        return ((int) ($row['total'] ?? 0)) > 0;
    }
}
