<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Review.php';
require_once __DIR__ . '/../../models/Salon.php';

class ReviewController
{
    private Review $reviewModel;
    private Salon $salonModel;

    public function __construct()
    {
        $this->reviewModel = new Review();
        $this->salonModel = new Salon();
    }

    public function index(): void
    {
        Auth::requireRole(['admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostAction();
            return;
        }

        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $salonId = trim($_GET['salon_id'] ?? '');
        $rating = trim($_GET['rating'] ?? '');

        $filters = [
            'keyword' => $keyword,
            'status' => $status,
            'salon_id' => $salonId,
            'rating' => $rating,
        ];

        $reviews = $this->getReviewsForAdmin($filters);
        $salons = $this->salonModel->getAllForAdmin([]);

        render('admin/reviews/index', [
            'pageTitle' => 'Admin Reviews - ' . APP_NAME,
            'navSection' => 'admin',
            'reviews' => $reviews,
            'salons' => $salons,
        ]);
    }

    private function handlePostAction(): void
    {
        if (!verifyCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ.');
            redirect(BASE_URL . '/admin/reviews');
        }

        $reviewId = (int) ($_POST['review_id'] ?? 0);
        $action = trim($_POST['action'] ?? '');

        if ($reviewId <= 0 || $action === '') {
            flash('error', 'Dữ liệu không hợp lệ.');
            redirect(BASE_URL . '/admin/reviews');
        }

        $review = $this->reviewModel->findById($reviewId);

        if (!$review) {
            flash('error', 'Không tìm thấy review.');
            redirect(BASE_URL . '/admin/reviews');
        }

        switch ($action) {
            case 'publish':
                $this->reviewModel->updateStatus($reviewId, 'published');
                flash('success', 'Đã chuyển review sang published.');
                break;

            case 'hidden':
                $this->reviewModel->updateStatus($reviewId, 'hidden');
                flash('success', 'Đã ẩn review.');
                break;

            case 'flag':
                $this->reviewModel->updateStatus($reviewId, 'flagged');
                flash('success', 'Đã đánh dấu review.');
                break;

            default:
                flash('error', 'Hành động không hợp lệ.');
                break;
        }

        redirect(BASE_URL . '/admin/reviews');
    }

    private function getReviewsForAdmin(array $filters = []): array
    {
        $db = Database::getInstance();

        $sql = "SELECT r.*,
                       u.name AS customer_name,
                       u.email AS customer_email,
                       s.name AS salon_name,
                       st.name AS staff_name
                FROM reviews r
                LEFT JOIN users u ON u.id = r.user_id
                LEFT JOIN salons s ON s.id = r.salon_id
                LEFT JOIN staff st ON st.id = r.staff_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (
                        u.name LIKE :keyword1
                        OR u.email LIKE :keyword2
                        OR s.name LIKE :keyword3
                        OR st.name LIKE :keyword4
                        OR r.content LIKE :keyword5
                        OR r.owner_reply LIKE :keyword6
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
            $sql .= " AND r.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['salon_id'])) {
            $sql .= " AND r.salon_id = :salon_id";
            $params['salon_id'] = (int) $filters['salon_id'];
        }

        if (!empty($filters['rating'])) {
            $sql .= " AND r.rating = :rating";
            $params['rating'] = (int) $filters['rating'];
        }

        $sql .= " ORDER BY r.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}