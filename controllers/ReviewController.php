<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Salon.php';

class ReviewController
{
    private Booking $bookingModel;
    private Review $reviewModel;
    private Salon $salonModel;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->reviewModel = new Review();
        $this->salonModel = new Salon();
    }

    public function create(): void
    {
        Auth::requireLogin();

        $bookingId = (int) ($_GET['booking_id'] ?? $_POST['booking_id'] ?? 0);
        $booking = $this->bookingModel->findDetailedById($bookingId);

        if (!$booking || (int) $booking['user_id'] !== (int) Auth::id()) {
            flash('error', 'Booking không hợp lệ.');
            redirect(BASE_URL . '/my-bookings');
        }

        if (!$this->reviewModel->canReviewBooking($booking)) {
            flash('error', 'Chỉ booking completed trong 30 ngày mới được đánh giá.');
            redirect(BASE_URL . '/booking/' . $bookingId);
        }

        if ($this->reviewModel->findByBookingId($bookingId)) {
            flash('error', 'Booking này đã có review.');
            redirect(BASE_URL . '/booking/' . $bookingId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrf()) {
                flash('error', 'Phiên làm việc không hợp lệ.');
                redirect(BASE_URL . '/write-review?booking_id=' . $bookingId);
            }

            $data = $this->validatedReviewInput();
            if ($data === null) {
                redirect(BASE_URL . '/write-review?booking_id=' . $bookingId);
            }

            $this->reviewModel->create([
                'booking_id' => $bookingId,
                'user_id' => (int) Auth::id(),
                'salon_id' => (int) $booking['salon_id'],
                'staff_id' => (int) $booking['staff_id'],
                'rating' => $data['rating'],
                'content' => $data['content'],
                'images' => null,
                'status' => 'published',
            ]);

            $this->salonModel->updateRatingStats((int) $booking['salon_id']);

            flash('success', 'Đã gửi đánh giá.');
            redirect(BASE_URL . '/booking/' . $bookingId);
        }

        render('review/create', [
            'pageTitle' => 'Viết đánh giá - ' . APP_NAME,
            'navSection' => 'user',
            'booking' => $booking,
        ]);
    }

    public function edit(int $id): void
    {
        Auth::requireLogin();

        $review = $this->reviewModel->findDetailedById($id);

        if (!$review || (int) $review['user_id'] !== (int) Auth::id()) {
            flash('error', 'Không tìm thấy review.');
            redirect(BASE_URL . '/my-bookings');
        }

        if (!$this->reviewModel->canEdit($review)) {
            flash('error', 'Review chỉ được sửa trong 24 giờ sau khi tạo.');
            redirect(BASE_URL . '/booking/' . (int) $review['booking_id']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrf()) {
                flash('error', 'Phiên làm việc không hợp lệ.');
                redirect(BASE_URL . '/edit-review/' . $id);
            }

            $data = $this->validatedReviewInput();
            if ($data === null) {
                redirect(BASE_URL . '/edit-review/' . $id);
            }

            $this->reviewModel->update($id, [
                'rating' => $data['rating'],
                'content' => $data['content'],
                'images' => $review['images'] ?? null,
            ]);

            $this->salonModel->updateRatingStats((int) $review['salon_id']);

            flash('success', 'Đã cập nhật review.');
            redirect(BASE_URL . '/booking/' . (int) $review['booking_id']);
        }

        render('review/edit', [
            'pageTitle' => 'Sửa đánh giá - ' . APP_NAME,
            'navSection' => 'user',
            'review' => $review,
        ]);
    }

    public function delete(int $id): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
            flash('error', 'Yêu cầu không hợp lệ.');
            redirect(BASE_URL . '/my-bookings');
        }

        $review = $this->reviewModel->findById($id);

        if (!$review || (int) $review['user_id'] !== (int) Auth::id()) {
            flash('error', 'Không tìm thấy review.');
            redirect(BASE_URL . '/my-bookings');
        }

        $bookingId = (int) $review['booking_id'];
        $salonId = (int) $review['salon_id'];

        $this->reviewModel->delete($id);
        $this->salonModel->updateRatingStats($salonId);

        flash('success', 'Đã xóa review.');
        redirect(BASE_URL . '/booking/' . $bookingId);
    }

    public function report(int $id): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
            flash('error', 'Yêu cầu không hợp lệ.');
            redirect(BASE_URL . '/home');
        }

        $review = $this->reviewModel->findById($id);

        if (!$review || (int) $review['user_id'] === (int) Auth::id()) {
            flash('error', 'Review không hợp lệ để báo cáo.');
            redirect(BASE_URL . '/salon/' . (int) ($review['salon_id'] ?? 0));
        }

        $reason = trim($_POST['reason'] ?? 'spam');
        if (!in_array($reason, ['spam', 'offensive', 'false_info'], true)) {
            $reason = 'spam';
        }

        if ($this->reviewModel->hasUserReported($id, (int) Auth::id())) {
            flash('error', 'Bạn đã báo cáo review này.');
            redirect(BASE_URL . '/salon/' . (int) $review['salon_id']);
        }

        $this->reviewModel->addReport($id, (int) Auth::id(), $reason);

        flash('success', 'Đã gửi báo cáo review.');
        redirect(BASE_URL . '/salon/' . (int) $review['salon_id']);
    }

    private function validatedReviewInput(): ?array
    {
        $rating = (int) ($_POST['rating'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $length = mb_strlen($content);

        if ($rating < 1 || $rating > 5) {
            flash('error', 'Rating phải từ 1 đến 5 sao.');
            return null;
        }

        if ($length < 10 || $length > 1000) {
            flash('error', 'Nội dung review phải từ 10 đến 1000 ký tự.');
            return null;
        }

        return [
            'rating' => $rating,
            'content' => $content,
        ];
    }
}
