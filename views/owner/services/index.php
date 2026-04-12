<?php

declare(strict_types=1);

function ownerServiceStatusBadgeClass($isActive): string
{
    return ((int) $isActive === 1) ? 'bg-success' : 'bg-secondary';
}
?>

<div class="container">
    <div class="mb-4">
        <h2 class="page-section-title">Quản lý services</h2>
        <div class="page-section-subtitle"><?= e($salon['name']) ?></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <?= $editingService ? 'Sửa dịch vụ' : 'Thêm dịch vụ mới' ?>
                    </h5>

                    <form method="POST" action="<?= e(BASE_URL . '/owner/services') ?>">
                        <?= csrfInput() ?>
                        <input type="hidden" name="action" value="<?= $editingService ? 'update' : 'create' ?>">

                        <?php if ($editingService): ?>
                            <input type="hidden" name="service_id" value="<?= e((string) $editingService['id']) ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Danh mục</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option
                                        value="<?= e((string) $category['id']) ?>"
                                        <?= ((string) ($editingService['category_id'] ?? '') === (string) $category['id']) ? 'selected' : '' ?>
                                    >
                                        <?= e($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tên dịch vụ</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="<?= e((string) ($editingService['name'] ?? '')) ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea
                                name="description"
                                class="form-control"
                                rows="4"
                            ><?= e((string) ($editingService['description'] ?? '')) ?></textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Giá</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="price"
                                    class="form-control"
                                    value="<?= e((string) ($editingService['price'] ?? 0)) ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Thời lượng (phút)</label>
                                <input
                                    type="number"
                                    min="1"
                                    name="duration"
                                    class="form-control"
                                    value="<?= e((string) ($editingService['duration'] ?? 30)) ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Đường dẫn ảnh</label>
                            <input
                                type="text"
                                name="image"
                                class="form-control"
                                value="<?= e((string) ($editingService['image'] ?? '')) ?>"
                                placeholder="public/uploads/services/example.jpg"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thứ tự</label>
                            <input
                                type="number"
                                name="sort_order"
                                class="form-control"
                                value="<?= e((string) ($editingService['sort_order'] ?? 0)) ?>"
                            >
                        </div>

                        <div class="form-check mb-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_active"
                                id="is_active"
                                <?= ((int) ($editingService['is_active'] ?? 1) === 1) ? 'checked' : '' ?>
                            >
                            <label class="form-check-label" for="is_active">
                                Đang hoạt động
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-dark">
                                <?= $editingService ? 'Cập nhật' : 'Thêm mới' ?>
                            </button>

                            <?php if ($editingService): ?>
                                <a href="<?= e(BASE_URL . '/owner/services') ?>" class="btn btn-outline-secondary">
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
                    <?php if (empty($services)): ?>
                        <div class="alert alert-info mb-0">Chưa có dịch vụ nào.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tên dịch vụ</th>
                                        <th>Danh mục</th>
                                        <th>Giá</th>
                                        <th>Thời lượng</th>
                                        <th>Thứ tự</th>
                                        <th>Trạng thái</th>
                                        <th style="min-width: 220px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($services as $service): ?>
                                        <tr>
                                            <td><?= e((string) $service['id']) ?></td>
                                            <td>
                                                <div class="fw-semibold"><?= e($service['name']) ?></div>
                                                <div class="small text-muted"><?= e((string) ($service['description'] ?? '')) ?></div>
                                            </td>
                                            <td><?= e((string) ($service['category_name'] ?? '')) ?></td>
                                            <td class="text-danger fw-semibold"><?= e(formatMoney((float) $service['price'])) ?></td>
                                            <td><?= e((string) $service['duration']) ?> phút</td>
                                            <td><?= e((string) $service['sort_order']) ?></td>
                                            <td>
                                                <span class="badge <?= ownerServiceStatusBadgeClass($service['is_active']) ?>">
                                                    <?= ((int) $service['is_active'] === 1) ? 'active' : 'inactive' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2 flex-wrap">
                                                    <a
                                                        href="<?= e(BASE_URL . '/owner/services?edit_id=' . $service['id']) ?>"
                                                        class="btn btn-sm btn-primary"
                                                    >
                                                        Sửa
                                                    </a>

                                                    <form method="POST" action="<?= e(BASE_URL . '/owner/services') ?>">
                                                        <?= csrfInput() ?>
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="service_id" value="<?= e((string) $service['id']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-secondary">
                                                            <?= ((int) $service['is_active'] === 1) ? 'Tắt' : 'Bật' ?>
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="<?= e(BASE_URL . '/owner/services') ?>" onsubmit="return confirm('Bạn chắc chắn muốn xóa dịch vụ này?');">
                                                        <?= csrfInput() ?>
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="service_id" value="<?= e((string) $service['id']) ?>">
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