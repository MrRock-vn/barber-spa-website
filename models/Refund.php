<?php

declare(strict_types=1);

class Refund
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM refunds WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $refund = $stmt->fetch();
        return $refund ?: null;
    }

    public function findByPaymentId(int $paymentId): array
    {
        $sql = "SELECT * FROM refunds
                WHERE payment_id = :payment_id
                ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['payment_id' => $paymentId]);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO refunds (
                    payment_id,
                    amount,
                    reason,
                    status,
                    refunded_at,
                    created_at
                ) VALUES (
                    :payment_id,
                    :amount,
                    :reason,
                    :status,
                    :refunded_at,
                    NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'payment_id' => $data['payment_id'],
            'amount' => $data['amount'],
            'reason' => $data['reason'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'refunded_at' => $data['refunded_at'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status, ?string $refundedAt = null): bool
    {
        $sql = "UPDATE refunds
                SET status = :status,
                    refunded_at = :refunded_at
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'status' => $status,
            'refunded_at' => $refundedAt,
        ]);
    }

    public function markSuccess(int $id, ?string $refundedAt = null): bool
    {
        return $this->updateStatus(
            $id,
            'success',
            $refundedAt ?? date('Y-m-d H:i:s')
        );
    }

    public function markFailed(int $id): bool
    {
        return $this->updateStatus($id, 'failed', null);
    }

    public function getPending(array $filters = []): array
    {
        $sql = "SELECT r.*,
                       p.transaction_id,
                       p.amount AS payment_amount,
                       p.status AS payment_status,
                       b.id AS booking_id,
                       b.booking_date,
                       b.start_time,
                       s.name AS salon_name,
                       u.name AS customer_name
                FROM refunds r
                INNER JOIN payments p ON p.id = r.payment_id
                INNER JOIN bookings b ON b.id = p.booking_id
                LEFT JOIN salons s ON s.id = b.salon_id
                LEFT JOIN users u ON u.id = p.user_id
                WHERE r.status = 'pending'
                ORDER BY r.id DESC";

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

    public function countPending(): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM refunds
                WHERE status = 'pending'";

        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function getByUserId(int $userId): array
    {
        $sql = "SELECT r.*,
                       p.transaction_id,
                       b.id AS booking_id,
                       b.booking_date,
                       b.start_time,
                       s.name AS salon_name
                FROM refunds r
                INNER JOIN payments p ON p.id = r.payment_id
                INNER JOIN bookings b ON b.id = p.booking_id
                LEFT JOIN salons s ON s.id = b.salon_id
                WHERE p.user_id = :user_id
                ORDER BY r.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function getTotalRefundedAmountByPaymentId(int $paymentId): float
    {
        $sql = "SELECT SUM(amount) AS total
                FROM refunds
                WHERE payment_id = :payment_id
                  AND status = 'success'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['payment_id' => $paymentId]);

        $row = $stmt->fetch();
        return (float) ($row['total'] ?? 0);
    }

    public function canRefundAmount(int $paymentId, float $requestedAmount): bool
    {
        $sql = "SELECT amount
                FROM payments
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $paymentId]);
        $payment = $stmt->fetch();

        if (!$payment) {
            return false;
        }

        $paidAmount = (float) $payment['amount'];
        $refundedAmount = $this->getTotalRefundedAmountByPaymentId($paymentId);

        return ($refundedAmount + $requestedAmount) <= $paidAmount;
    }
}