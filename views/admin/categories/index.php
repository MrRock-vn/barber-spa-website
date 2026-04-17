<?php

declare(strict_types=1);

function adminCategoryStatusBadgeClass($isActive): string
{
    return ((int) $isActive === 1) ? 'bg-success' : 'bg-secondary';
}
?>

<div class="admin-page admin-categories-page">
    <div class="admin-page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="page-section-title">Quản lý categories</h2>
            <div class="page-section-subtitle">Thêm, sửa, bật/tắt và xóa danh mục</div>
        </div>
        <div class="text-muted small text-end">
            Hiện có <strong><?= e((string) count($categories)) ?></strong> category.
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card admin-card-surface mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <?= $editingCategory ? 'Sửa danh mục' : 'Thêm danh mục mới' ?>
                    </h5>

                    <form method="POST" action="<?= e(BASE_URL . '/admin/categories') ?>">
                        <?= csrfInput() ?>
                        <input type="hidden" name="action" value="<?= $editingCategory ? 'update' : 'create' ?>">

                        <?php if ($editingCategory): ?>
                            <input type="hidden" name="category_id" value="<?= e((string) $editingCategory['id']) ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Tên danh mục</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="<?= e((string) ($editingCategory['name'] ?? '')) ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Icon</label>
                            <input
                                type="text"
                                name="icon"
                                class="form-control"
                                value="<?= e((string) ($editingCategory['icon'] ?? '')) ?>"
                                placeholder="Ví dụ: ✂ hoặc 🌿"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea
                                name="description"
                                class="form-control"
                                rows="4"
                            ><?= e((string) ($editingCategory['description'] ?? '')) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thứ tự</label>
                            <input
                                type="number"
                                name="sort_order"
                                class="form-control"
                                value="<?= e((string) ($editingCategory['sort_order'] ?? 0)) ?>"
                            >
                        </div>

                        <div class="form-check mb-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_active"
                                id="is_active"
                                <?= ((int) ($editingCategory['is_active'] ?? 1) === 1) ? 'checked' : '' ?>
                            >
                            <label class="form-check-label" for="is_active">
                                Đang hoạt động
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-dark">
                                <?= $editingCategory ? 'Cập nhật' : 'Thêm mới' ?>
                            </button>

                            <?php if ($editingCategory): ?>
                                <a href="<?= e(BASE_URL . '/admin/categories') ?>" class="btn btn-outline-secondary">
                                    Hủy sửa
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card admin-card-surface mb-4 admin-filter-card">
                <div class="card-body">
                    <form method="GET" action="<?= e(BASE_URL . '/admin/categories') ?>">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label">Từ khóa</label>
                                <input
                                    type="text"
                                    name="keyword"
                                    class="form-control"
                                    value="<?= e($_GET['keyword'] ?? '') ?>"
                                    placeholder="Tên hoặc mô tả"
                                >
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Trạng thái</label>
                                <select name="is_active" class="form-select">
                                    <option value="">-- Tất cả --</option>
                                    <option value="1" <?= (($_GET['is_active'] ?? '') === '1') ? 'selected' : '' ?>>Đang hoạt động</option>
                                    <option value="0" <?= (($_GET['is_active'] ?? '') === '0') ? 'selected' : '' ?>>Tắt</option>
                                </select>
                            </div>

                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-dark">Lọc</button>
                                <a href="<?= e(BASE_URL . '/admin/categories') ?>" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card admin-card-surface admin-table-card">
                <div class="card-body">
                    <?php if (empty($categories)): ?>
                        <div class="alert alert-info mb-0">Không có category nào phù hợp.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table admin-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tên</th>
                                        <th>Icon</th>
                                        <th>Mô tả</th>
                                        <th>Thứ tự</th>
                                        <th>Trạng thái</th>
                                        <th style="min-width: 180px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?= e((string) $category['id']) ?></td>
                                            <td class="fw-semibold"><?= e($category['name']) ?></td>
                                            <td><?= e((string) ($category['icon'] ?? '')) ?: '-' ?></td>
                                            <td><?= e((string) ($category['description'] ?? '')) ?: '-' ?></td>
                                            <td><?= e((string) $category['sort_order']) ?></td>
                                            <td>
                                                <span class="badge <?= adminCategoryStatusBadgeClass($category['is_active']) ?>">
                                                    <?= ((int) $category['is_active'] === 1) ? 'active' : 'inactive' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="admin-table-actions d-flex gap-2 flex-wrap">
                                                    <a
                                                        href="<?= e(BASE_URL . '/admin/categories?edit_id=' . $category['id']) ?>"
                                                        class="btn btn-sm btn-primary"
                                                    >
                                                        Sửa
                                                    </a>

                                                    <form method="POST" action="<?= e(BASE_URL . '/admin/categories') ?>" onsubmit="return confirm('Bạn chắc chắn muốn xóa category này?');">
                                                        <?= csrfInput() ?>
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="category_id" value="<?= e((string) $category['id']) ?>">
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