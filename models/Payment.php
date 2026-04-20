<?php

declare(strict_types=1);

class Payment
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM payments WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $payment = $stmt->fetch();
        return $payment ?: null;
    }

    public function findByTransactionId(string $transactionId): ?array
    {
        $sql = "SELECT * FROM payments WHERE transaction_id = :transaction_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['transaction_id' => $transactionId]);

        $payment = $stmt->fetch();
        return $payment ?: null;
    }

    public function findByBookingId(int $bookingId): ?array
    {
        $sql = "SELECT * FROM payments WHERE booking_id = :booking_id ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['booking_id' => $bookingId]);

        $payment = $stmt->fetch();
        return $payment ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO payments (
                    booking_id,
                    user_id,
                    gateway,
                    transaction_id,
                    amount,
                    currency,
                    status,
                    gateway_response,
                    paid_at,
                    created_at
                ) VALUES (
                    :booking_id,
                    :user_id,
                    :gateway,
                    :transaction_id,
                    :amount,
                    :currency,
                    :status,
                    :gateway_response,
                    :paid_at,
                    NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'booking_id' => $data['booking_id'],
            'user_id' => $data['user_id'],
            'gateway' => $data['gateway'],
            'transaction_id' => $data['transaction_id'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'VND',
            'status' => $data['status'] ?? 'pending',
            'gateway_response' => $data['gateway_response'] ?? null,
            'paid_at' => $data['paid_at'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateStatus(
        int $id,
        string $status,
        ?string $gatewayResponse = null,
        ?string $paidAt = null
    ): bool {
        // SECURITY: Verify payment exists and is in pending state before updating
        $payment = $this->findById($id);
        if (!$payment || $payment['status'] !== 'pending') {
            return false;
        }

        $sql = "UPDATE payments
                SET status = :status,
                    gateway_response = :gateway_response,
                    paid_at = :paid_at
                WHERE id = :id AND status = 'pending'";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'status' => $status,
            'gateway_response' => $gatewayResponse,
            'paid_at' => $paidAt,
        ]);
    }

    public function updateGatewayResponse(int $id, ?string $gatewayResponse): bool
    {
        $sql = "UPDATE payments
                SET gateway_response = :gateway_response
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'gateway_response' => $gatewayResponse,
        ]);
    }

    public function markSuccess(int $id, ?string $gatewayResponse = null, ?string $paidAt = null): bool
    {
        return $this->updateStatus(
            $id,
            'success',
            $gatewayResponse,
            $paidAt ?? date('Y-m-d H:i:s')
        );
    }

    public function markFailed(int $id, ?string $gatewayResponse = null): bool
    {
        return $this->updateStatus($id, 'failed', $gatewayResponse, null);
    }

    public function markRefunded(int $id, ?string $gatewayResponse = null): bool
    {
        return $this->updateStatus($id, 'refunded', $gatewayResponse, null);
    }

    public function getByUserId(int $userId, array $filters = []): array
    {
        $sql = "SELECT p.*,
                       b.booking_date,
                       b.start_time,
                       b.status AS booking_status,
                       s.name AS salon_name
                FROM payments p
                LEFT JOIN bookings b ON b.id = p.booking_id
                LEFT JOIN salons s ON s.id = b.salon_id
                WHERE p.user_id = :user_id";
        $params = ['user_id' => $userId];

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['gateway'])) {
            $sql .= " AND p.gateway = :gateway";
            $params['gateway'] = $filters['gateway'];
        }

        $sql .= " ORDER BY p.id DESC";

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

    public function getBySalonId(int $salonId, array $filters = []): array
    {
        $sql = "SELECT p.*,
                       b.booking_date,
                       b.start_time,
                       b.status AS booking_status,
                       u.name AS customer_name
                FROM payments p
                INNER JOIN bookings b ON b.id = p.booking_id
                LEFT JOIN users u ON u.id = p.user_id
                WHERE b.salon_id = :salon_id";
        $params = ['salon_id' => $salonId];

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }

        $sql .= " ORDER BY p.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function countByStatus(string $status): int
    {
        $sql = "SELECT COUNT(*) AS total FROM payments WHERE status = :status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => $status]);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

   public function isProcessedTransaction(string $transactionId): bool
{
    $payment = $this->findByTransactionId($transactionId);

    if (!$payment) {
        return false;
    }

    return in_array($payment['status'], ['success', 'failed', 'refunded'], true);
}
}