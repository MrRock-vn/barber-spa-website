<?php

declare(strict_types=1);

function ownerStaffStatusBadgeClass($isActive): string
{
    return ((int) $isActive === 1) ? 'bg-success' : 'bg-secondary';
}
?>

<div class="container">
    <div class="mb-4">
        <h2 class="page-section-title">Quản lý staff</h2>
        <div class="page-section-subtitle"><?= e($salon['name']) ?></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <?= $editingStaff ? 'Sửa nhân viên' : 'Thêm nhân viên mới' ?>
                    </h5>

                    <form method="POST" action="<?= e(BASE_URL . '/owner/staff') ?>">
                        <?= csrfInput() ?>
                        <input type="hidden" name="action" value="<?= $editingStaff ? 'update' : 'create' ?>">

                        <?php if ($editingStaff): ?>
                            <input type="hidden" name="staff_id" value="<?= e((string) $editingStaff['id']) ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Tên nhân viên</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="<?= e((string) ($editingStaff['name'] ?? '')) ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                value="<?= e((string) ($editingStaff['phone'] ?? '')) ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Đường dẫn avatar</label>
                            <input
                                type="text"
                                name="avatar"
                                class="form-control"
                                value="<?= e((string) ($editingStaff['avatar'] ?? '')) ?>"
                                placeholder="public/uploads/avatars/staff.jpg"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Chuyên môn</label>
                            <textarea
                                name="specialties"
                                class="form-control"
                                rows="4"
                                placeholder='Ví dụ: ["fade","classic"] hoặc mô tả text'
                            ><?= e((string) ($editingStaff['specialties'] ?? '')) ?></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_active"
                                id="is_active"
                                <?= ((int) ($editingStaff['is_active'] ?? 1) === 1) ? 'checked' : '' ?>
                            >
                            <label class="form-check-label" for="is_active">
                                Đang hoạt động
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-dark">
                                <?= $editingStaff ? 'Cập nhật' : 'Thêm mới' ?>
                            </button>

                            <?php if ($editingStaff): ?>
                                <a href="<?= e(BASE_URL . '/owner/staff') ?>" class="btn btn-outline-secondary">
                                    Hủy sửa
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($staffList)): ?>
                        <div class="alert alert-info mb-0">Chưa có nhân viên nào.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tên nhân viên</th>
                                        <th>Điện thoại</th>
                                        <th>Chuyên môn</th>
                                        <th>Trạng thái</th>
                                        <th style="min-width: 280px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staffList as $staff): ?>
                                        <tr>
                                            <td><?= e((string) $staff['id']) ?></td>
                                            <td class="fw-semibold"><?= e($staff['name']) ?></td>
                                            <td><?= e((string) ($staff['phone'] ?? '')) ?: '-' ?></td>
                                            <td>
                                                <div class="small text-muted">
                                                    <?= e((string) ($staff['specialties'] ?? '')) ?: '-' ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= ownerStaffStatusBadgeClass($staff['is_active']) ?>">
                                                    <?= ((int) $staff['is_active'] === 1) ? 'active' : 'inactive' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2 flex-wrap">
                                                    <a
                                                        href="<?= e(BASE_URL . '/owner/staff?edit_id=' . $staff['id']) ?>"
                                                        class="btn btn-sm btn-primary"
                                                    >
                                                        Sửa
                                                    </a>

                                                    <a
                                                        href="<?= e(BASE_URL . '/owner/staff/schedule?staff_id=' . $staff['id']) ?>"
                                                        class="btn btn-sm btn-dark"
                                                    >
                                                        Lịch làm việc
                                                    </a>

                                                    <form method="POST" action="<?= e(BASE_URL . '/owner/staff') ?>">
                                                        <?= csrfInput() ?>
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="staff_id" value="<?= e((string) $staff['id']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-secondary">
                                                            <?= ((int) $staff['is_active'] === 1) ? 'Tắt' : 'Bật' ?>
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="<?= e(BASE_URL . '/owner/staff') ?>" onsubmit="return confirm('Bạn chắc chắn muốn xóa nhân viên này?');">
                                                        <?= csrfInput() ?>
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="staff_id" value="<?= e((string) $staff['id']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                                    </form>
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
    </div>
</div>