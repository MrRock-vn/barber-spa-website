<?php declare(strict_types=1); ?>

<div class="container py-4">
    <div class="mb-4">
        <a href="<?= e(BASE_URL . '/booking/' . $booking['id']) ?>" class="btn btn-outline-dark btn-sm">Quay lại booking</a>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <h2 class="page-section-title mb-2">Viết đánh giá</h2>
            <div class="page-section-subtitle mb-4">
                <?= e($booking['salon_name']) ?> - <?= e(formatDate($booking['booking_date'])) ?>
            </div>

            <div class="alert alert-success">
                Đã xác minh sử dụng dịch vụ: booking #<?= e((string) $booking['id']) ?> đã completed.
            </div>

            <form method="POST" action="<?= e(BASE_URL . '/write-review?booking_id=' . $booking['id']) ?>">
                <?= csrfInput() ?>
                <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">

                <div class="mb-3">
                    <label class="form-label">Số sao</label>
                    <select name="rating" class="form-select" required>
                        <option value="">-- Chọn --</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>"><?= $i ?> sao</option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nội dung nhận xét</label>
                    <textarea name="content" class="form-control" rows="6" minlength="10" maxlength="1000" required><?= e($_POST['content'] ?? '') ?></textarea>
                    <div class="form-text">Từ 10 đến 1000 ký tự.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">Gửi đánh giá</button>
                    <a href="<?= e(BASE_URL . '/booking/' . $booking['id']) ?>" class="btn btn-outline-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
