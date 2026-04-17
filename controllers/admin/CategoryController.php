<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Category.php';

class CategoryController
{
    private Category $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        Auth::requireRole(['admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostAction();
            return;
        }

        $keyword = trim($_GET['keyword'] ?? '');
        $isActive = $_GET['is_active'] ?? '';

        $filters = [
            'keyword' => $keyword,
            'is_active' => $isActive,
        ];

        $categories = $this->categoryModel->getAll($filters);
        $editId = (int) ($_GET['edit_id'] ?? 0);
        $editingCategory = $editId > 0 ? $this->categoryModel->findById($editId) : null;

        render('admin/categories/index', [
            'pageTitle' => 'Admin Categories - ' . APP_NAME,
            'navSection' => 'admin',
            'categories' => $categories,
            'editingCategory' => $editingCategory,
        ]);
    }

    private function handlePostAction(): void
    {
        if (!verifyCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ.');
            redirect(BASE_URL . '/admin/categories');
        }

        $action = trim($_POST['action'] ?? '');

        switch ($action) {
            case 'create':
                $this->createCategory();
                return;

            case 'update':
                $this->updateCategory();
                return;

            case 'delete':
                $this->deleteCategory();
                return;

            default:
                flash('error', 'Hành động không hợp lệ.');
                redirect(BASE_URL . '/admin/categories');
        }
    }

    private function createCategory(): void
    {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            flash('error', 'Tên danh mục không được để trống.');
            redirect(BASE_URL . '/admin/categories');
        }

        if ($this->categoryModel->findByName($name)) {
            flash('error', 'Tên danh mục đã tồn tại.');
            redirect(BASE_URL . '/admin/categories');
        }

        $this->categoryModel->create([
            'name' => $name,
            'icon' => $icon !== '' ? $icon : null,
            'description' => $description !== '' ? $description : null,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
        ]);

        flash('success', 'Đã thêm danh mục mới.');
        redirect(BASE_URL . '/admin/categories');
    }

    private function updateCategory(): void
    {
        $id = (int) ($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($id <= 0) {
            flash('error', 'Danh mục không hợp lệ.');
            redirect(BASE_URL . '/admin/categories');
        }

        $category = $this->categoryModel->findById($id);

        if (!$category) {
            flash('error', 'Không tìm thấy danh mục.');
            redirect(BASE_URL . '/admin/categories');
        }

        if ($name === '') {
            flash('error', 'Tên danh mục không được để trống.');
            redirect(BASE_URL . '/admin/categories?edit_id=' . $id);
        }

        $existing = $this->categoryModel->findByName($name);
        if ($existing && (int) $existing['id'] !== $id) {
            flash('error', 'Tên danh mục đã tồn tại.');
            redirect(BASE_URL . '/admin/categories?edit_id=' . $id);
        }

        $this->categoryModel->update($id, [
            'name' => $name,
            'icon' => $icon !== '' ? $icon : null,
            'description' => $description !== '' ? $description : null,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
        ]);

        flash('success', 'Đã cập nhật danh mục.');
        redirect(BASE_URL . '/admin/categories');
    }

    private function deleteCategory(): void
    {
        $id = (int) ($_POST['category_id'] ?? 0);

        if ($id <= 0) {
            flash('error', 'Danh mục không hợp lệ.');
            redirect(BASE_URL . '/admin/categories');
        }

        $category = $this->categoryModel->findById($id);

        if (!$category) {
            flash('error', 'Không tìm thấy danh mục.');
            redirect(BASE_URL . '/admin/categories');
        }

        if ($this->categoryModel->hasServices($id)) {
            flash('error', 'Danh mục đang được dùng bởi services, không thể xóa.');
            redirect(BASE_URL . '/admin/categories');
        }

        $this->categoryModel->delete($id);

        flash('success', 'Đã xóa danh mục.');
        redirect(BASE_URL . '/admin/categories');
    }
}