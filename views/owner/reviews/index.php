<?php

declare(strict_types=1);

function ownerReviewStatusBadgeClass(string $status): string
{
    return match ($status) {
        'published' => 'bg-success',
        'flagged' => 'bg-warning text-dark',
        'removed' => 'bg-secondary',
        default => 'bg-secondary',
    };
}
?>

<div class="container">
    <div class="mb-4">
        <h2 class="page-section-title">Review salon</h2>
        <div class="page-section-subtitle"><?= e($salon['name']) ?></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= e(BASE_URL . '/owner/reviews') ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="published" <?= ($_GET['status'] ?? '') === 'published' ? 'selected' : '' ?>>published</option>
                            <option value="flagged" <?= ($_GET['status'] ?? '') === 'flagged' ? 'selected' : '' ?>>flagged</option>
                            <option value="removed" <?= ($_GET['status'] ?? '') === 'removed' ? 'selected' : '' ?>>removed</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Số sao</label>
                        <select name="rating" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>" <?= ($_GET['rating'] ?? '') === (string) $i ? 'selected' : '' ?>><?= $i ?> sao</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-md-5 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Lọc</button>
                        <a href="<?= e(BASE_URL . '/owner/reviews') ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($reviews)): ?>
        <div class="alert alert-info">Chưa có review phù hợp.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($reviews as $review): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <h5 class="fw-bold mb-1"><?= e($review['customer_name']) ?></h5>
                                    <div class="small text-muted">
                                        <?= e($review['staff_name'] ?? '-') ?> - <?= e(formatDate($review['created_at'])) ?>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-warning text-dark"><?= e((string) $review['rating']) ?>/5</span>
                                    <span class="badge <?= ownerReviewStatusBadgeClass((string) $review['status']) ?>">
                                        <?= e($review['status']) ?>
                                    </span>
                                </div>
                            </div>

                            <p class="mb-3"><?= e((string) $review['content']) ?></p>

                            <form method="POST" action="<?= e(BASE_URL . '/owner/reviews') ?>" class="border rounded-3 p-3 bg-light">
                                <?= csrfInput() ?>
                                <input type="hidden" name="review_id" value="<?= e((string) $review['id']) ?>">
                                <input type="hidden" name="action" value="reply">

                                <label class="form-label">Phản hồi của salon</label>
                                <textarea name="owner_reply" class="form-control mb-2" rows="3" maxlength="1000"><?= e((string) ($review['owner_reply'] ?? '')) ?></textarea>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-sm btn-dark">Lưu phản hồi</button>
                                </div>
                            </form>

                            <?php if (!empty($review['owner_reply'])): ?>
                                <form method="POST" action="<?= e(BASE_URL . '/owner/reviews') ?>" class="mt-2">
                                    <?= csrfInput() ?>
                                    <input type="hidden" name="review_id" value="<?= e((string) $review['id']) ?>">
                                    <input type="hidden" name="action" value="clear_reply">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Xóa phản hồi</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
