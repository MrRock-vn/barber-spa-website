<?php

declare(strict_types=1);

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO users (
                    name,
                    email,
                    password,
                    role,
                    is_active,
                    email_token,
                    created_at,
                    updated_at
                ) VALUES (
                    :name,
                    :email,
                    :password,
                    :role,
                    :is_active,
                    :email_token,
                    NOW(),
                    NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? 'customer',
            'is_active' => $data['is_active'] ?? 1,
            'email_token' => $data['email_token'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateLoginSuccess(int $userId, ?string $ip = null): bool
    {
        $sql = "UPDATE users
                SET login_attempts = 0,
                    locked_until = NULL,
                    last_login_at = NOW(),
                    login_ip = :login_ip,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $userId,
            'login_ip' => $ip,
        ]);
    }

    public function increaseLoginAttempts(int $userId): bool
    {
        $sql = "UPDATE users
                SET login_attempts = login_attempts + 1,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $userId]);
    }

    public function lockAccount(int $userId, string $lockedUntil): bool
    {
        $sql = "UPDATE users
                SET locked_until = :locked_until,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $userId,
            'locked_until' => $lockedUntil,
        ]);
    }

    public function resetLoginAttempts(int $userId): bool
    {
        $sql = "UPDATE users
                SET login_attempts = 0,
                    locked_until = NULL,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $userId]);
    }

    public function updateRememberToken(int $userId, ?string $token): bool
    {
        $sql = "UPDATE users
                SET remember_token = :remember_token,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $userId,
            'remember_token' => $token,
        ]);
    }

    public function findByRememberToken(string $token): ?array
    {
        // SECURITY: Validate token format - must be 64 hex characters
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $sql = "SELECT * FROM users
                WHERE remember_token = :token
                AND is_active = 1
                AND email_verified_at IS NOT NULL
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function verifyEmailByToken(string $token): bool
    {
        $sql = "UPDATE users
                SET email_verified_at = NOW(),
                    email_token = NULL,
                    updated_at = NOW()
                WHERE email_token = :token
                  AND email_verified_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);

        return $stmt->rowCount() > 0;
    }

    public function saveResetToken(int $userId, string $token, string $expiresAt): bool
    {
        $sql = "UPDATE users
                SET reset_token = :reset_token,
                    reset_token_expires = :reset_token_expires,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $userId,
            'reset_token' => $token,
            'reset_token_expires' => $expiresAt,
        ]);
    }

    public function findByResetToken(string $token): ?array
    {
        $sql = "SELECT * FROM users
                WHERE reset_token = :token
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        $sql = "UPDATE users
                SET password = :password,
                    reset_token = NULL,
                    reset_token_expires = NULL,
                    remember_token = NULL,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $userId,
            'password' => $hashedPassword,
        ]);
    }

    public function all(array $filters = []): array
    {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (name LIKE :keyword OR email LIKE :keyword)";
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['role'])) {
            $sql .= " AND role = :role";
            $params['role'] = $filters['role'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND is_active = :is_active";
            $params['is_active'] = (int) $filters['is_active'];
        }

        $sql .= " ORDER BY id DESC";

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

    public function countAll(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) AS total FROM users WHERE 1=1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (name LIKE :keyword OR email LIKE :keyword)";
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['role'])) {
            $sql .= " AND role = :role";
            $params['role'] = $filters['role'];
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

    public function updateProfile(int $userId, array $data): bool
    {
        $sql = "UPDATE users
                SET name = :name,
                    phone = :phone,
                    address = :address,
                    city = :city,
                    district = :district,
                    avatar = :avatar,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $userId,
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'district' => $data['district'] ?? null,
            'avatar' => $data['avatar'] ?? null,
        ]);
    }

    public function banUser(int $userId, string $reason): bool
    {
        $sql = "UPDATE users
                SET is_active = 0,
                    ban_reason = :ban_reason,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $userId,
            'ban_reason' => $reason,
        ]);
    }

    public function unbanUser(int $userId): bool
    {
        $sql = "UPDATE users
                SET is_active = 1,
                    ban_reason = NULL,
                    locked_until = NULL,
                    login_attempts = 0,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $userId]);
    }
        public function countAllUsers(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM users";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function countInactiveUsers(): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM users
                WHERE is_active = 0";

        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function getRecentUsers(int $limit = 5): array
    {
        $sql = "SELECT *
                FROM users
                ORDER BY id DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}