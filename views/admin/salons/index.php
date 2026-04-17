<?php

declare(strict_types=1);

function adminSalonStatusBadgeClass(string $status): string
{
    return match ($status) {
        'pending' => 'bg-warning text-dark',
        'active' => 'bg-success',
        'hidden' => 'bg-secondary',
        'rejected' => 'bg-danger',
        'deleted' => 'bg-dark',
        default => 'bg-secondary',
    };
}
?>

<div class="admin-page admin-salons-page">
    <div class="admin-page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="page-section-title">Quản lý salons</h2>
            <div class="page-section-subtitle">Duyệt, từ chối, ẩn và xóa mềm salon</div>
        </div>
        <div class="text-muted small text-end">
            Hiện có <strong><?= e((string) count($salons)) ?></strong> salon.
        </div>
    </div>

    <div class="card admin-card-surface mb-4 admin-filter-card">
        <div class="card-body">
            <form method="GET" action="<?= e(BASE_URL . '/admin/salons') ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Từ khóa</label>
                        <input
                            type="text"
                            name="keyword"
                            class="form-control"
                            value="<?= e($_GET['keyword'] ?? '') ?>"
                            placeholder="Tên salon, owner, email..."
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>pending</option>
                            <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>active</option>
                            <option value="hidden" <?= ($_GET['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>hidden</option>
                            <option value="rejected" <?= ($_GET['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>rejected</option>
                            <option value="deleted" <?= ($_GET['status'] ?? '') === 'deleted' ? 'selected' : '' ?>>deleted</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Thành phố</label>
                        <input
                            type="text"
                            name="city"
                            class="form-control"
                            value="<?= e($_GET['city'] ?? '') ?>"
                            placeholder="Nhập thành phố"
                        >
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Lọc</button>
                        <a href="<?= e(BASE_URL . '/admin/salons') ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card admin-card-surface admin-table-card">
        <div class="card-body p-0">
            <?php if (empty($salons)): ?>
                <div class="alert alert-info mb-0">Không có salon nào phù hợp.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tên salon</th>
                                <th>Owner</th>
                                <th>Khu vực</th>
                                <th>Rating</th>
                                <th>Bookings</th>
                                <th>Trạng thái</th>
                                <th>Lý do từ chối</th>
                                <th style="min-width: 320px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salons as $salon): ?>
                                <tr>
                                    <td><?= e((string) $salon['id']) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= e($salon['name']) ?></div>
                                        <div class="small text-muted"><?= e($salon['address']) ?></div>
                                    </td>
                                    <td>
                                        <div><?= e($salon['owner_name']) ?></div>
                                        <div class="small text-muted"><?= e($salon['owner_email']) ?></div>
                                    </td>
                                    <td><?= e($salon['district']) ?>, <?= e($salon['city']) ?></td>
                                    <td><?= e((string) $salon['avg_rating']) ?></td>
                                    <td><?= e((string) $salon['total_bookings']) ?></td>
                                    <td>
                                        <span class="badge <?= adminSalonStatusBadgeClass((string) $salon['status']) ?>">
                                            <?= e($salon['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= e((string) ($salon['reject_reason'] ?? '')) ?: '-' ?></td>
                                    <td>
                                        <div class="admin-table-actions d-flex flex-column gap-2">
                                            <?php if ($salon['status'] === 'pending'): ?>
                                                <div class="admin-table-actions d-flex gap-2 flex-wrap">
                                                    <form method="POST" action="<?= e(BASE_URL . '/admin/salons') ?>">
                                                        <?= csrfInput() ?>
                                                        <input type="hidden" name="salon_id" value="<?= e((string) $salon['id']) ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="btn btn-sm btn-success">Duyệt</button>
                                                    </form>
                                                </div>

                                                <form method="POST" action="<?= e(BASE_URL . '/admin/salons') ?>" class="d-flex gap-2">
                                                    <?= csrfInput() ?>
                                                    <input type="hidden" name="salon_id" value="<?= e((string) $salon['id']) ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input
                                                        type="text"
                                                        name="reason"
                                                        class="form-control form-control-sm"
                                                        placeholder="Lý do từ chối"
                                                        required
                                                    >
                                                    <button type="submit" class="btn btn-sm btn-danger">Từ chối</button>
                                                </form>
                                            <?php elseif ($salon['status'] === 'active'): ?>
                                                <div class="admin-table-actions d-flex gap-2 flex-wrap">
                                                    <form method="POST" action="<?= e(BASE_URL . '/admin/salons') ?>">
                                                        <?= csrfInput() ?>
                                                        <input type="hidden" name="salon_id" value="<?= e((string) $salon['id']) ?>">
                                                        <input type="hidden" name="action" value="hide">
                                                        <button type="submit" class="btn btn-sm btn-secondary">Ẩn</button>
                                                    </form>

                                                    <form method="POST" action="<?= e(BASE_URL . '/admin/salons') ?>">
                                                        <?= csrfInput() ?>
                                                        <input type="hidden" name="salon_id" value="<?= e((string) $salon['id']) ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="btn btn-sm btn-dark">Xóa mềm</button>
                                                    </form>
                                                </div>
                                            <?php elseif (in_array($salon['status'], ['hidden', 'rejected'], true)): ?>
                                                <div class="admin-table-actions d-flex gap-2 flex-wrap">
                                                    <form method="POST" action="<?= e(BASE_URL . '/admin/salons') ?>">
                                                        <?= csrfInput() ?>
                                                        <input type="hidden" name="salon_id" value="<?= e((string) $salon['id']) ?>">
                                                        <input type="hidden" name="action" value="reopen">
                                                        <button type="submit" class="btn btn-sm btn-success">Mở lại</button>
                                                    </form>

                                                    <form method="POST" action="<?= e(BASE_URL . '/admin/salons') ?>">
                                                        <?= csrfInput() ?>
                                                        <input type="hidden" name="salon_id" value="<?= e((string) $salon['id']) ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="btn btn-sm btn-dark">Xóa mềm</button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">Không có thao tác</span>
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