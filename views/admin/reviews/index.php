<?php

declare(strict_types=1);

function adminReviewStatusBadgeClass(string $status): string
{
    return match ($status) {
        'published' => 'bg-success',
        'removed' => 'bg-secondary',
        'flagged' => 'bg-warning text-dark',
        default => 'bg-secondary',
    };
}
?>

<div class="admin-page admin-reviews-page">
    <div class="admin-page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="page-section-title">Quản lý reviews</h2>
            <div class="page-section-subtitle">Xem, lọc và kiểm duyệt review</div>
        </div>
        <div class="text-muted small text-end">
            Hiện có <strong><?= e((string) count($reviews)) ?></strong> review.
        </div>
    </div>

    <div class="card admin-card-surface mb-4 admin-filter-card">
        <div class="card-body">
            <form method="GET" action="<?= e(BASE_URL . '/admin/reviews') ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Từ khóa</label>
                        <input
                            type="text"
                            name="keyword"
                            class="form-control"
                            value="<?= e($_GET['keyword'] ?? '') ?>"
                            placeholder="Khách, salon, staff, nội dung..."
                        >
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="published" <?= ($_GET['status'] ?? '') === 'published' ? 'selected' : '' ?>>published</option>
                            <option value="removed" <?= ($_GET['status'] ?? '') === 'removed' ? 'selected' : '' ?>>removed</option>
                            <option value="flagged" <?= ($_GET['status'] ?? '') === 'flagged' ? 'selected' : '' ?>>flagged</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Salon</label>
                        <select name="salon_id" class="form-select">
                            <option value="">-- Tất cả salons --</option>
                            <?php foreach ($salons as $salon): ?>
                                <option
                                    value="<?= e((string) $salon['id']) ?>"
                                    <?= (($_GET['salon_id'] ?? '') === (string) $salon['id']) ? 'selected' : '' ?>
                                >
                                    <?= e($salon['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">Rating</label>
                        <select name="rating" class="form-select">
                            <option value="">--</option>
                            <option value="1" <?= ($_GET['rating'] ?? '') === '1' ? 'selected' : '' ?>>1</option>
                            <option value="2" <?= ($_GET['rating'] ?? '') === '2' ? 'selected' : '' ?>>2</option>
                            <option value="3" <?= ($_GET['rating'] ?? '') === '3' ? 'selected' : '' ?>>3</option>
                            <option value="4" <?= ($_GET['rating'] ?? '') === '4' ? 'selected' : '' ?>>4</option>
                            <option value="5" <?= ($_GET['rating'] ?? '') === '5' ? 'selected' : '' ?>>5</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Lọc</button>
                        <a href="<?= e(BASE_URL . '/admin/reviews') ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card admin-card-surface admin-table-card">
        <div class="card-body p-0">
            <?php if (empty($reviews)): ?>
                <div class="alert alert-info mb-0">Không có review nào phù hợp.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Khách hàng</th>
                                <th>Salon</th>
                                <th>Nhân viên</th>
                                <th>Rating</th>
                                <th>Trạng thái</th>
                                <th>Reports</th>
                                <th>Nội dung</th>
                                <th>Owner reply</th>
                                <th style="min-width: 220px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td><?= e((string) $review['id']) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= e($review['customer_name']) ?></div>
                                        <div class="small text-muted"><?= e($review['customer_email']) ?></div>
                                    </td>
                                    <td><?= e($review['salon_name']) ?></td>
                                    <td><?= e((string) ($review['staff_name'] ?? '')) ?: '-' ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark">⭐ <?= e((string) $review['rating']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= adminReviewStatusBadgeClass((string) $review['status']) ?>">
                                            <?= e($review['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= e((string) ($review['report_count'] ?? 0)) ?></td>
                                    <td>
                                        <div style="max-width: 260px;" class="small">
                                            <?= e(mb_strimwidth((string) ($review['content'] ?? ''), 0, 120, '...')) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="max-width: 220px;" class="small text-muted">
                                            <?= e(mb_strimwidth((string) ($review['owner_reply'] ?? ''), 0, 100, '...')) ?: '-' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-table-actions d-flex gap-2 flex-wrap">
                                            <?php if ($review['status'] !== 'published'): ?>
                                                <form method="POST" action="<?= e(BASE_URL . '/admin/reviews') ?>">
                                                    <?= csrfInput() ?>
                                                    <input type="hidden" name="review_id" value="<?= e((string) $review['id']) ?>">
                                                    <input type="hidden" name="action" value="publish">
                                                    <button type="submit" class="btn btn-sm btn-success">Publish</button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($review['status'] !== 'removed'): ?>
                                                <form method="POST" action="<?= e(BASE_URL . '/admin/reviews') ?>" data-confirm="An review nay khoi trang cong khai?">
                                                    <?= csrfInput() ?>
                                                    <input type="hidden" name="review_id" value="<?= e((string) $review['id']) ?>">
                                                    <input type="hidden" name="action" value="removed">
                                                    <button type="submit" class="btn btn-sm btn-secondary">Ẩn</button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($review['status'] !== 'flagged'): ?>
                                                <form method="POST" action="<?= e(BASE_URL . '/admin/reviews') ?>">
                                                    <?= csrfInput() ?>
                                                    <input type="hidden" name="review_id" value="<?= e((string) $review['id']) ?>">
                                                    <input type="hidden" name="action" value="flag">
                                                    <button type="submit" class="btn btn-sm btn-warning">Flag</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
