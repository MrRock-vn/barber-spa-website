<?php

declare(strict_types=1);

class Review
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM reviews WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $review = $stmt->fetch();
        return $review ?: null;
    }

    public function findByBookingId(int $bookingId): ?array
    {
        $sql = "SELECT * FROM reviews WHERE booking_id = :booking_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['booking_id' => $bookingId]);

        $review = $stmt->fetch();
        return $review ?: null;
    }

    public function findDetailedById(int $id): ?array
    {
        $sql = "SELECT r.*,
                       u.name AS customer_name,
                       s.name AS salon_name,
                       st.name AS staff_name,
                       b.booking_date
                FROM reviews r
                LEFT JOIN users u ON u.id = r.user_id
                LEFT JOIN salons s ON s.id = r.salon_id
                LEFT JOIN staff st ON st.id = r.staff_id
                LEFT JOIN bookings b ON b.id = r.booking_id
                WHERE r.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $review = $stmt->fetch();
        return $review ?: null;
    }

    public function getPublishedBySalonId(int $salonId, array $filters = []): array
    {
        $sql = "SELECT r.*,
                       u.name AS customer_name,
                       st.name AS staff_name
                FROM reviews r
                LEFT JOIN users u ON u.id = r.user_id
                LEFT JOIN staff st ON st.id = r.staff_id
                WHERE r.salon_id = :salon_id
                  AND r.status = 'published'";
        $params = ['salon_id' => $salonId];

        if (!empty($filters['rating'])) {
            $sql .= " AND r.rating = :rating";
            $params['rating'] = (int) $filters['rating'];
        }

        $sql .= " ORDER BY r.created_at DESC, r.id DESC";

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':salon_id', $salonId, PDO::PARAM_INT);

        if (!empty($filters['rating'])) {
            $stmt->bindValue(':rating', (int) $filters['rating'], PDO::PARAM_INT);
        }

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $stmt->bindValue(':limit', (int) $filters['limit'], PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $filters['offset'], PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRatingDistributionBySalonId(int $salonId): array
    {
        $sql = "SELECT rating, COUNT(*) AS total
                FROM reviews
                WHERE salon_id = :salon_id
                  AND status = 'published'
                GROUP BY rating";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['salon_id' => $salonId]);

        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($stmt->fetchAll() as $row) {
            $distribution[(int) $row['rating']] = (int) $row['total'];
        }

        return $distribution;
    }

    public function getBySalonId(int $salonId, array $filters = []): array
    {
        $sql = "SELECT r.*,
                       u.name AS customer_name,
                       st.name AS staff_name
                FROM reviews r
                LEFT JOIN users u ON u.id = r.user_id
                LEFT JOIN staff st ON st.id = r.staff_id
                WHERE r.salon_id = :salon_id";
        $params = ['salon_id' => $salonId];

        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :status";
            $params['status'] = $filters['status'];
        }

        $sql .= " ORDER BY r.created_at DESC, r.id DESC";

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            if ($key === 'salon_id') {
                $stmt->bindValue(':salon_id', (int) $value, PDO::PARAM_INT);
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

    public function getByUserId(int $userId): array
    {
        $sql = "SELECT r.*,
                       s.name AS salon_name,
                       st.name AS staff_name
                FROM reviews r
                LEFT JOIN salons s ON s.id = r.salon_id
                LEFT JOIN staff st ON st.id = r.staff_id
                WHERE r.user_id = :user_id
                ORDER BY r.created_at DESC, r.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function getReviewableBookingsByUserId(int $userId): array
    {
        $sql = "SELECT b.*,
                       s.name AS salon_name,
                       st.name AS staff_name,
                       r.id AS review_id
                FROM bookings b
                LEFT JOIN salons s ON s.id = b.salon_id
                LEFT JOIN staff st ON st.id = b.staff_id
                LEFT JOIN reviews r ON r.booking_id = b.id
                WHERE b.user_id = :user_id
                  AND b.status = 'completed'
                ORDER BY b.booking_date DESC, b.start_time DESC, b.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function getFlagged(array $filters = []): array
    {
        $sql = "SELECT r.*,
                       u.name AS customer_name,
                       s.name AS salon_name
                FROM reviews r
                LEFT JOIN users u ON u.id = r.user_id
                LEFT JOIN salons s ON s.id = r.salon_id
                WHERE r.status = 'flagged'
                ORDER BY r.report_count DESC, r.created_at DESC";

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $stmt->bindValue(':limit', (int) $filters['limit'], PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $filters['offset'], PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countFlagged(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM reviews WHERE status = 'flagged'";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function countAllReviews(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM reviews");
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function countBySalonId(int $salonId): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM reviews
                WHERE salon_id = :salon_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['salon_id' => $salonId]);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function getRecentForAdmin(int $limit = 5): array
    {
        $sql = "SELECT r.*,
                       u.name AS customer_name,
                       s.name AS salon_name
                FROM reviews r
                LEFT JOIN users u ON u.id = r.user_id
                LEFT JOIN salons s ON s.id = r.salon_id
                ORDER BY r.id DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getRecentBySalonId(int $salonId, int $limit = 5): array
    {
        $sql = "SELECT r.*,
                       u.name AS customer_name,
                       st.name AS staff_name
                FROM reviews r
                LEFT JOIN users u ON u.id = r.user_id
                LEFT JOIN staff st ON st.id = r.staff_id
                WHERE r.salon_id = :salon_id
                ORDER BY r.id DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':salon_id', $salonId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO reviews (
                    booking_id,
                    user_id,
                    salon_id,
                    staff_id,
                    rating,
                    content,
                    images,
                    status,
                    owner_reply,
                    owner_replied_at,
                    report_count,
                    created_at,
                    updated_at
                ) VALUES (
                    :booking_id,
                    :user_id,
                    :salon_id,
                    :staff_id,
                    :rating,
                    :content,
                    :images,
                    :status,
                    :owner_reply,
                    :owner_replied_at,
                    :report_count,
                    NOW(),
                    NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'booking_id' => $data['booking_id'],
            'user_id' => $data['user_id'],
            'salon_id' => $data['salon_id'],
            'staff_id' => $data['staff_id'],
            'rating' => $data['rating'],
            'content' => $data['content'],
            'images' => $data['images'] ?? null,
            'status' => $data['status'] ?? 'published',
            'owner_reply' => $data['owner_reply'] ?? null,
            'owner_replied_at' => $data['owner_replied_at'] ?? null,
            'report_count' => $data['report_count'] ?? 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE reviews
                SET rating = :rating,
                    content = :content,
                    images = :images,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'rating' => $data['rating'],
            'content' => $data['content'],
            'images' => $data['images'] ?? null,
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $sql = "UPDATE reviews
                SET status = :status,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM reviews WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public function reply(int $id, string $reply): bool
    {
        $sql = "UPDATE reviews
                SET owner_reply = :owner_reply,
                    owner_replied_at = NOW(),
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'owner_reply' => $reply,
        ]);
    }

    public function clearReply(int $id): bool
    {
        $sql = "UPDATE reviews
                SET owner_reply = NULL,
                    owner_replied_at = NULL,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function canEdit(array $review): bool
    {
        $createdAt = strtotime($review['created_at'] ?? '');
        if ($createdAt === false) {
            return false;
        }

        return (time() - $createdAt) <= (24 * 60 * 60);
    }

    public function canReviewBooking(array $booking): bool
    {
        if (($booking['status'] ?? '') !== 'completed') {
            return false;
        }

        $bookingDate = strtotime($booking['booking_date'] ?? '');
        if ($bookingDate === false) {
            return false;
        }

        return (time() - $bookingDate) <= (30 * 24 * 60 * 60);
    }

    public function hasUserReported(int $reviewId, int $reporterId): bool
    {
        $sql = "SELECT COUNT(*) AS total
                FROM review_reports
                WHERE review_id = :review_id
                  AND reporter_id = :reporter_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'review_id' => $reviewId,
            'reporter_id' => $reporterId,
        ]);

        $row = $stmt->fetch();
        return ((int) ($row['total'] ?? 0)) > 0;
    }

    public function addReport(int $reviewId, int $reporterId, string $reason): bool
    {
        $sql = "INSERT INTO review_reports (
                    review_id,
                    reporter_id,
                    reason,
                    created_at
                ) VALUES (
                    :review_id,
                    :reporter_id,
                    :reason,
                    NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            'review_id' => $reviewId,
            'reporter_id' => $reporterId,
            'reason' => $reason,
        ]);

        if ($success) {
            $this->increaseReportCount($reviewId);
        }

        return $success;
    }

    public function increaseReportCount(int $reviewId): bool
    {
        $sql = "UPDATE reviews
                SET report_count = report_count + 1,
                    status = CASE
                        WHEN report_count + 1 >= 3 THEN 'flagged'
                        ELSE status
                    END,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $reviewId]);
    }
}
