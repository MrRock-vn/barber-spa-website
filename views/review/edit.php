<?php declare(strict_types=1); ?>

<div class="container py-4">
    <div class="mb-4">
        <a href="<?= e(BASE_URL . '/booking/' . $review['booking_id']) ?>" class="btn btn-outline-dark btn-sm">Quay lại booking</a>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <h2 class="page-section-title mb-2">Sửa đánh giá</h2>
            <div class="page-section-subtitle mb-4">
                <?= e($review['salon_name']) ?> - <?= e(formatDate($review['created_at'])) ?>
            </div>

            <form method="POST" action="<?= e(BASE_URL . '/edit-review/' . $review['id']) ?>">
                <?= csrfInput() ?>

                <div class="mb-3">
                    <label class="form-label">Số sao</label>
                    <select name="rating" class="form-select" required>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= (int) $review['rating'] === $i ? 'selected' : '' ?>><?= $i ?> sao</option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nội dung nhận xét</label>
                    <textarea name="content" class="form-control" rows="6" minlength="10" maxlength="1000" required><?= e((string) $review['content']) ?></textarea>
                    <div class="form-text">Chỉ được sửa trong 24 giờ sau khi tạo.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">Lưu thay đổi</button>
                    <a href="<?= e(BASE_URL . '/booking/' . $review['booking_id']) ?>" class="btn btn-outline-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
