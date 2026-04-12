<?php

declare(strict_types=1);

class Salon
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM salons WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $salon = $stmt->fetch();
        return $salon ?: null;
    }

    public function findActiveById(int $id): ?array
    {
        $sql = "SELECT * FROM salons
                WHERE id = :id AND status = 'active'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $salon = $stmt->fetch();
        return $salon ?: null;
    }

    public function getFeatured(int $limit = 6): array
    {
        $sql = "SELECT * FROM salons
                WHERE status = 'active'
                ORDER BY avg_rating DESC, total_bookings DESC, id DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getByOwnerId(int $ownerId): array
    {
        $sql = "SELECT * FROM salons
                WHERE owner_id = :owner_id
                ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['owner_id' => $ownerId]);

        return $stmt->fetchAll();
    }

    public function getFirstByOwnerId(int $ownerId): ?array
    {
        $sql = "SELECT * FROM salons
                WHERE owner_id = :owner_id
                ORDER BY id ASC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['owner_id' => $ownerId]);

        $salon = $stmt->fetch();
        return $salon ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO salons (
                    owner_id,
                    name,
                    address,
                    district,
                    city,
                    phone,
                    description,
                    search_keywords,
                    open_time,
                    close_time,
                    working_days,
                    status,
                    latitude,
                    longitude,
                    created_at,
                    updated_at
                ) VALUES (
                    :owner_id,
                    :name,
                    :address,
                    :district,
                    :city,
                    :phone,
                    :description,
                    :search_keywords,
                    :open_time,
                    :close_time,
                    :working_days,
                    :status,
                    :latitude,
                    :longitude,
                    NOW(),
                    NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'owner_id' => $data['owner_id'],
            'name' => $data['name'],
            'address' => $data['address'],
            'district' => $data['district'],
            'city' => $data['city'],
            'phone' => $data['phone'],
            'description' => $data['description'] ?? null,
            'search_keywords' => $data['search_keywords'] ?? null,
            'open_time' => $data['open_time'] ?? '08:00:00',
            'close_time' => $data['close_time'] ?? '20:00:00',
            'working_days' => $data['working_days'] ?? '1,2,3,4,5,6',
            'status' => $data['status'] ?? 'pending',
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE salons
                SET name = :name,
                    address = :address,
                    district = :district,
                    city = :city,
                    phone = :phone,
                    description = :description,
                    search_keywords = :search_keywords,
                    open_time = :open_time,
                    close_time = :close_time,
                    working_days = :working_days,
                    latitude = :latitude,
                    longitude = :longitude,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'address' => $data['address'],
            'district' => $data['district'],
            'city' => $data['city'],
            'phone' => $data['phone'],
            'description' => $data['description'] ?? null,
            'search_keywords' => $data['search_keywords'] ?? null,
            'open_time' => $data['open_time'] ?? '08:00:00',
            'close_time' => $data['close_time'] ?? '20:00:00',
            'working_days' => $data['working_days'] ?? '1,2,3,4,5,6',
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ]);
    }

    public function updateStatus(int $id, string $status, ?string $rejectReason = null): bool
    {
        $sql = "UPDATE salons
                SET status = :status,
                    reject_reason = :reject_reason,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'status' => $status,
            'reject_reason' => $rejectReason,
        ]);
    }

    public function getImages(int $salonId): array
    {
        $sql = "SELECT * FROM salon_images
                WHERE salon_id = :salon_id
                ORDER BY is_primary DESC, sort_order ASC, id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['salon_id' => $salonId]);

        return $stmt->fetchAll();
    }

    public function search(array $filters = []): array
    {
        $sql = "SELECT DISTINCT s.*
                FROM salons s
                LEFT JOIN services sv ON sv.salon_id = s.id
                WHERE s.status = 'active'";
        $params = [];

      if (!empty($filters['keyword'])) {
    $sql .= " AND (
                s.name LIKE :keyword1
                OR s.description LIKE :keyword2
                OR s.search_keywords LIKE :keyword3
                OR s.city LIKE :keyword4
                OR s.district LIKE :keyword5
                OR sv.name LIKE :keyword6
            )";
    $keyword = '%' . $filters['keyword'] . '%';
    $params['keyword1'] = $keyword;
    $params['keyword2'] = $keyword;
    $params['keyword3'] = $keyword;
    $params['keyword4'] = $keyword;
    $params['keyword5'] = $keyword;
    $params['keyword6'] = $keyword;
}

        if (!empty($filters['city'])) {
            $sql .= " AND s.city = :city";
            $params['city'] = $filters['city'];
        }

        if (!empty($filters['district'])) {
            $sql .= " AND s.district = :district";
            $params['district'] = $filters['district'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND sv.category_id = :category_id";
            $params['category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['rating'])) {
            $sql .= " AND s.avg_rating >= :rating";
            $params['rating'] = (float) $filters['rating'];
        }

        if ($filters['min_price'] !== '' && $filters['min_price'] !== null) {
            $sql .= " AND sv.price >= :min_price";
            $params['min_price'] = (float) $filters['min_price'];
        }

        if ($filters['max_price'] !== '' && $filters['max_price'] !== null) {
            $sql .= " AND sv.price <= :max_price";
            $params['max_price'] = (float) $filters['max_price'];
        }

        $allowedSort = [
            'rating_desc' => 's.avg_rating DESC, s.id DESC',
            'newest' => 's.created_at DESC, s.id DESC',
            'popular' => 's.total_bookings DESC, s.id DESC',
            'price_asc' => 's.id ASC'
        ];

        $sort = $filters['sort'] ?? 'rating_desc';
        $orderBy = $allowedSort[$sort] ?? $allowedSort['rating_desc'];

        $sql .= " ORDER BY {$orderBy}";

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':' . $key, $value);
            }
        }

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $stmt->bindValue(':limit', (int) $filters['limit'], PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $filters['offset'], PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

   public function countSearch(array $filters = []): int
{
    $sql = "SELECT COUNT(DISTINCT s.id) AS total
            FROM salons s
            LEFT JOIN services sv ON sv.salon_id = s.id
            WHERE s.status = 'active'";
    $params = [];

    if (!empty($filters['keyword'])) {
        $sql .= " AND (
                    s.name LIKE :keyword1
                    OR s.description LIKE :keyword2
                    OR s.search_keywords LIKE :keyword3
                    OR s.city LIKE :keyword4
                    OR s.district LIKE :keyword5
                    OR sv.name LIKE :keyword6
                )";
        $keyword = '%' . $filters['keyword'] . '%';
        $params['keyword1'] = $keyword;
        $params['keyword2'] = $keyword;
        $params['keyword3'] = $keyword;
        $params['keyword4'] = $keyword;
        $params['keyword5'] = $keyword;
        $params['keyword6'] = $keyword;
    }

    if (!empty($filters['city'])) {
        $sql .= " AND s.city = :city";
        $params['city'] = $filters['city'];
    }

    if (!empty($filters['district'])) {
        $sql .= " AND s.district = :district";
        $params['district'] = $filters['district'];
    }

    if (!empty($filters['category_id'])) {
        $sql .= " AND sv.category_id = :category_id";
        $params['category_id'] = (int) $filters['category_id'];
    }

    if (!empty($filters['rating'])) {
        $sql .= " AND s.avg_rating >= :rating";
        $params['rating'] = (float) $filters['rating'];
    }

    if (($filters['min_price'] ?? '') !== '') {
        $sql .= " AND sv.price >= :min_price";
        $params['min_price'] = (float) $filters['min_price'];
    }

    if (($filters['max_price'] ?? '') !== '') {
        $sql .= " AND sv.price <= :max_price";
        $params['max_price'] = (float) $filters['max_price'];
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    $row = $stmt->fetch();
    return (int) ($row['total'] ?? 0);
}

    public function updateRatingStats(int $salonId): bool
    {
        $sql = "UPDATE salons s
                LEFT JOIN (
                    SELECT salon_id,
                           COUNT(*) AS total_reviews_calc,
                           ROUND(AVG(rating), 2) AS avg_rating_calc
                    FROM reviews
                    WHERE salon_id = :salon_id
                      AND status = 'published'
                    GROUP BY salon_id
                ) r ON s.id = r.salon_id
                SET s.total_reviews = IFNULL(r.total_reviews_calc, 0),
                    s.avg_rating = IFNULL(r.avg_rating_calc, 0.00),
                    s.updated_at = NOW()
                WHERE s.id = :salon_id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['salon_id' => $salonId]);
    }

    public function getTop10(): array
    {
        $sql = "SELECT * FROM salons
                WHERE status = 'active'
                ORDER BY avg_rating DESC, total_bookings DESC, id DESC
                LIMIT 10";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function canDelete(int $salonId): bool
    {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings
                WHERE salon_id = :salon_id
                  AND status IN ('pending', 'confirmed')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['salon_id' => $salonId]);

        $row = $stmt->fetch();
        return ((int) ($row['total'] ?? 0)) === 0;
    }
        public function getAllForAdmin(array $filters = []): array
    {
        $sql = "SELECT s.*,
                       u.name AS owner_name,
                       u.email AS owner_email
                FROM salons s
                LEFT JOIN users u ON u.id = s.owner_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (
                        s.name LIKE :keyword1
                        OR s.address LIKE :keyword2
                        OR s.city LIKE :keyword3
                        OR s.district LIKE :keyword4
                        OR u.name LIKE :keyword5
                        OR u.email LIKE :keyword6
                    )";
            $keyword = '%' . $filters['keyword'] . '%';
            $params['keyword1'] = $keyword;
            $params['keyword2'] = $keyword;
            $params['keyword3'] = $keyword;
            $params['keyword4'] = $keyword;
            $params['keyword5'] = $keyword;
            $params['keyword6'] = $keyword;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND s.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['city'])) {
            $sql .= " AND s.city LIKE :city";
            $params['city'] = '%' . $filters['city'] . '%';
        }

        $sql .= " ORDER BY s.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function countAllForAdmin(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM salons s
                LEFT JOIN users u ON u.id = s.owner_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (
                        s.name LIKE :keyword1
                        OR s.address LIKE :keyword2
                        OR s.city LIKE :keyword3
                        OR s.district LIKE :keyword4
                        OR u.name LIKE :keyword5
                        OR u.email LIKE :keyword6
                    )";
            $keyword = '%' . $filters['keyword'] . '%';
            $params['keyword1'] = $keyword;
            $params['keyword2'] = $keyword;
            $params['keyword3'] = $keyword;
            $params['keyword4'] = $keyword;
            $params['keyword5'] = $keyword;
            $params['keyword6'] = $keyword;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND s.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['city'])) {
            $sql .= " AND s.city LIKE :city";
            $params['city'] = '%' . $filters['city'] . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function approve(int $id): bool
    {
        return $this->updateStatus($id, 'active', null);
    }

    public function reject(int $id, string $reason): bool
    {
        return $this->updateStatus($id, 'rejected', $reason);
    }

    public function hide(int $id): bool
    {
        return $this->updateStatus($id, 'hidden', null);
    }

    public function reopen(int $id): bool
    {
        return $this->updateStatus($id, 'active', null);
    }

    public function softDelete(int $id): bool
    {
        return $this->updateStatus($id, 'deleted', null);
    }
        public function countAllSalons(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM salons";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function countPendingSalons(): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM salons
                WHERE status = 'pending'";

        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function getRecentForAdmin(int $limit = 5): array
    {
        $sql = "SELECT s.*,
                       u.name AS owner_name,
                       u.email AS owner_email
                FROM salons s
                LEFT JOIN users u ON u.id = s.owner_id
                ORDER BY s.id DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}