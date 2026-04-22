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
        Auth::requireRole(['owner']);

        $ownerId = (int) Auth::id();
        $salon = $this->salonModel->getFirstByOwnerId($ownerId);

        if (!$salon) {
            echo '<h1>Owner Reviews</h1>';
            echo '<p>Bạn chưa có salon nào.</p>';
            return;
        }

        if ((int) $salon['owner_id'] !== $ownerId) {
            http_response_code(403);
            exit('403 - Forbidden');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostAction((int) $salon['id']);
            return;
        }

        $status = trim($_GET['status'] ?? '');
        $rating = trim($_GET['rating'] ?? '');
        $filters = [];

        if ($status !== '') {
            $filters['status'] = $status;
        }

        $reviews = $this->reviewModel->getBySalonId((int) $salon['id'], $filters);
        if ($rating !== '') {
            $reviews = array_values(array_filter($reviews, static fn (array $review): bool => (int) $review['rating'] === (int) $rating));
        }

        render('owner/reviews/index', [
            'pageTitle' => 'Owner Reviews - ' . APP_NAME,
            'navSection' => 'owner',
            'salon' => $salon,
            'reviews' => $reviews,
        ]);
    }

    private function handlePostAction(int $salonId): void
    {
        if (!verifyCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ.');
            redirect(BASE_URL . '/owner/reviews');
        }

        $reviewId = (int) ($_POST['review_id'] ?? 0);
        $action = trim($_POST['action'] ?? '');
        $review = $this->reviewModel->findById($reviewId);

        if (!$review || (int) $review['salon_id'] !== $salonId) {
            flash('error', 'Không tìm thấy review thuộc salon của bạn.');
            redirect(BASE_URL . '/owner/reviews');
        }

        if ($action === 'reply') {
            $reply = trim($_POST['owner_reply'] ?? '');
            if (mb_strlen($reply) < 2 || mb_strlen($reply) > 1000) {
                flash('error', 'Phản hồi phải từ 2 đến 1000 ký tự.');
                redirect(BASE_URL . '/owner/reviews');
            }

            $this->reviewModel->reply($reviewId, $reply);
            flash('success', 'Đã lưu phản hồi.');
            redirect(BASE_URL . '/owner/reviews');
        }

        if ($action === 'clear_reply') {
            $this->reviewModel->clearReply($reviewId);
            flash('success', 'Đã xóa phản hồi.');
            redirect(BASE_URL . '/owner/reviews');
        }

        flash('error', 'Hành động không hợp lệ.');
        redirect(BASE_URL . '/owner/reviews');
    }
}
