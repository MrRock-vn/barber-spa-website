<?php

declare(strict_types=1);

function adminUserStatusBadgeClass($isActive): string
{
    return ((int) $isActive === 1) ? 'bg-success' : 'bg-danger';
}
?>

<div class="container">
    <div class="mb-4">
        <h2 class="page-section-title">Quản lý users</h2>
        <div class="page-section-subtitle">Xem, lọc và khóa/mở khóa user</div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= e(BASE_URL . '/admin/users') ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Từ khóa</label>
                        <input
                            type="text"
                            name="keyword"
                            class="form-control"
                            value="<?= e($_GET['keyword'] ?? '') ?>"
                            placeholder="Tên hoặc email"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="admin" <?= ($_GET['role'] ?? '') === 'admin' ? 'selected' : '' ?>>admin</option>
                            <option value="owner" <?= ($_GET['role'] ?? '') === 'owner' ? 'selected' : '' ?>>owner</option>
                            <option value="customer" <?= ($_GET['role'] ?? '') === 'customer' ? 'selected' : '' ?>>customer</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="is_active" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="1" <?= (($_GET['is_active'] ?? '') === '1') ? 'selected' : '' ?>>Đang hoạt động</option>
                            <option value="0" <?= (($_GET['is_active'] ?? '') === '0') ? 'selected' : '' ?>>Bị khóa</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Lọc</button>
                        <a href="<?= e(BASE_URL . '/admin/users') ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($users)): ?>
                <div class="alert alert-info mb-0">Không có user nào phù hợp.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Trạng thái</th>
                                <th>Đăng nhập sai</th>
                                <th>Locked until</th>
                                <th>Lý do khóa</th>
                                <th style="min-width: 220px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= e((string) $user['id']) ?></td>
                                    <td><?= e($user['name']) ?></td>
                                    <td><?= e($user['email']) ?></td>
                                    <td>
                                        <span class="badge bg-dark"><?= e($user['role']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= adminUserStatusBadgeClass($user['is_active']) ?>">
                                            <?= ((int) $user['is_active'] === 1) ? 'active' : 'inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= e((string) $user['login_attempts']) ?></td>
                                    <td><?= e((string) ($user['locked_until'] ?? '')) ?: '-' ?></td>
                                    <td><?= e((string) ($user['ban_reason'] ?? '')) ?: '-' ?></td>
                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            <?php if ((int) $user['id'] === (int) Auth::id()): ?>
                                                <span class="text-muted small">Tài khoản hiện tại</span>
                                            <?php elseif ((int) $user['is_active'] === 1): ?>
                                                <form method="POST" action="<?= e(BASE_URL . '/admin/users') ?>" class="d-flex gap-2">
                                                    <?= csrfInput() ?>
                                                    <input type="hidden" name="user_id" value="<?= e((string) $user['id']) ?>">
                                                    <input type="hidden" name="action" value="ban">
                                                    <input
                                                        type="text"
                                                        name="reason"
                                                        class="form-control form-control-sm"
                                                        placeholder="Lý do khóa"
                                                    >
                                                    <button type="submit" class="btn btn-sm btn-danger">Khóa</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" action="<?= e(BASE_URL . '/admin/users') ?>">
                                                    <?= csrfInput() ?>
                                                    <input type="hidden" name="user_id" value="<?= e((string) $user['id']) ?>">
                                                    <input type="hidden" name="action" value="unban">
                                                    <button type="submit" class="btn btn-sm btn-success">Mở khóa</button>
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