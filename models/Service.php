<?php

declare(strict_types=1);

class Service
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM services WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $service = $stmt->fetch();
        return $service ?: null;
    }

    public function getBySalonId(int $salonId, bool $onlyActive = false): array
    {
        $sql = "SELECT s.*, c.name AS category_name, c.icon AS category_icon
                FROM services s
                LEFT JOIN categories c ON c.id = s.category_id
                WHERE s.salon_id = :salon_id";
        $params = ['salon_id' => $salonId];

        if ($onlyActive) {
            $sql .= " AND s.is_active = 1";
        }

        $sql .= " ORDER BY s.sort_order ASC, s.id ASC";

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
                FROM services
                WHERE salon_id = :salon_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['salon_id' => $salonId]);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function getByCategoryId(int $categoryId, bool $onlyActive = false): array
    {
        $sql = "SELECT * FROM services WHERE category_id = :category_id";
        $params = ['category_id' => $categoryId];

        if ($onlyActive) {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY sort_order ASC, id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT s.*, c.name AS category_name, c.icon AS category_icon
                FROM services s
                LEFT JOIN categories c ON c.id = s.category_id
                WHERE s.id IN ($placeholders)
                ORDER BY s.sort_order ASC, s.id ASC";

        $stmt = $this->db->prepare($sql);
        foreach (array_values($ids) as $index => $id) {
            $stmt->bindValue($index + 1, (int) $id, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO services (
                    salon_id,
                    category_id,
                    name,
                    description,
                    price,
                    duration,
                    image,
                    is_active,
                    sort_order
                ) VALUES (
                    :salon_id,
                    :category_id,
                    :name,
                    :description,
                    :price,
                    :duration,
                    :image,
                    :is_active,
                    :sort_order
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'salon_id' => $data['salon_id'],
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'duration' => $data['duration'],
            'image' => $data['image'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE services
                SET category_id = :category_id,
                    name = :name,
                    description = :description,
                    price = :price,
                    duration = :duration,
                    image = :image,
                    is_active = :is_active,
                    sort_order = :sort_order
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'duration' => $data['duration'],
            'image' => $data['image'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    public function updateSortOrder(int $id, int $sortOrder): bool
    {
        $sql = "UPDATE services
                SET sort_order = :sort_order
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'sort_order' => $sortOrder,
        ]);
    }

    public function deactivate(int $id): bool
    {
        $sql = "UPDATE services
                SET is_active = 0
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM services WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public function getMinPriceBySalonId(int $salonId): ?float
    {
        $sql = "SELECT MIN(price) AS min_price
                FROM services
                WHERE salon_id = :salon_id
                  AND is_active = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['salon_id' => $salonId]);

        $row = $stmt->fetch();

        if (!$row || $row['min_price'] === null) {
            return null;
        }

        return (float) $row['min_price'];
    }

    public function calculateSummaryByIds(array $ids): array
    {
        $services = $this->getByIds($ids);

        $totalPrice = 0.0;
        $totalDuration = 0;

        foreach ($services as $service) {
            $totalPrice += (float) $service['price'];
            $totalDuration += (int) $service['duration'];
        }

        return [
            'services' => $services,
            'total_price' => $totalPrice,
            'total_duration' => $totalDuration,
        ];
    }

    public function belongsToSalon(array $serviceIds, int $salonId): bool
    {
        if (empty($serviceIds)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));

        $sql = "SELECT COUNT(*) AS total
                FROM services
                WHERE id IN ($placeholders)
                  AND salon_id = ?";

        $stmt = $this->db->prepare($sql);

        $position = 1;
        foreach ($serviceIds as $serviceId) {
            $stmt->bindValue($position++, (int) $serviceId, PDO::PARAM_INT);
        }
        $stmt->bindValue($position, $salonId, PDO::PARAM_INT);

        $stmt->execute();
        $row = $stmt->fetch();

        return ((int) ($row['total'] ?? 0)) === count($serviceIds);
    }
}
