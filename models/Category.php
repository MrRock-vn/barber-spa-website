<?php

declare(strict_types=1);

class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM categories WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $category = $stmt->fetch();
        return $category ?: null;
    }

    public function findByName(string $name): ?array
    {
        $sql = "SELECT * FROM categories WHERE name = :name LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['name' => $name]);

        $category = $stmt->fetch();
        return $category ?: null;
    }

    public function getActive(int $limit = 5): array
    {
        $sql = "SELECT * FROM categories
                WHERE is_active = 1
                ORDER BY sort_order ASC, id ASC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getAll(array $filters = []): array
    {
        $sql = "SELECT * FROM categories WHERE 1=1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (name LIKE :keyword OR description LIKE :keyword)";
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND is_active = :is_active";
            $params['is_active'] = (int) $filters['is_active'];
        }

        $sql .= " ORDER BY sort_order ASC, id DESC";

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

    public function countAll(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) AS total FROM categories WHERE 1=1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (name LIKE :keyword OR description LIKE :keyword)";
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND is_active = :is_active";
            $params['is_active'] = (int) $filters['is_active'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO categories (
                    name,
                    icon,
                    description,
                    sort_order,
                    is_active
                ) VALUES (
                    :name,
                    :icon,
                    :description,
                    :sort_order,
                    :is_active
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'icon' => $data['icon'] ?? null,
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE categories
                SET name = :name,
                    icon = :icon,
                    description = :description,
                    sort_order = :sort_order,
                    is_active = :is_active
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'icon' => $data['icon'] ?? null,
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? 1,
        ]);
    }

    public function hasServices(int $id): bool
    {
        $sql = "SELECT COUNT(*) AS total
                FROM services
                WHERE category_id = :category_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['category_id' => $id]);

        $row = $stmt->fetch();
        return ((int) ($row['total'] ?? 0)) > 0;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }
}