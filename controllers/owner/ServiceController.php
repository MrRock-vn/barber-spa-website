<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Salon.php';
require_once __DIR__ . '/../../models/Service.php';
require_once __DIR__ . '/../../models/Category.php';

class ServiceController
{
    private Salon $salonModel;
    private Service $serviceModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->salonModel = new Salon();
        $this->serviceModel = new Service();
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        Auth::requireRole(['owner']);

        $ownerId = (int) Auth::id();
        $salon = $this->salonModel->getFirstByOwnerId($ownerId);

        if (!$salon) {
            echo '<h1>Owner Services</h1>';
            echo '<p>Bạn chưa có salon nào.</p>';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostAction((int) $salon['id']);
            return;
        }

        $services = $this->serviceModel->getBySalonId((int) $salon['id']);
        $categories = $this->categoryModel->getActive(100);

        $editId = (int) ($_GET['edit_id'] ?? 0);
        $editingService = null;

        if ($editId > 0) {
            $candidate = $this->serviceModel->findById($editId);
            if ($candidate && (int) $candidate['salon_id'] === (int) $salon['id']) {
                $editingService = $candidate;
            }
        }

        render('owner/services/index', [
    'pageTitle' => 'Owner Services - ' . APP_NAME,
    'navSection' => 'owner',
    'salon' => $salon,
    'services' => $services,
    'categories' => $categories,
    'editingService' => $editingService,
]);
    }

    private function handlePostAction(int $salonId): void
    {
        if (!verifyCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ.');
            redirect(BASE_URL . '/owner/services');
        }

        $action = trim($_POST['action'] ?? '');

        switch ($action) {
            case 'create':
                $this->createService($salonId);
                return;

            case 'update':
                $this->updateService($salonId);
                return;

            case 'toggle':
                $this->toggleService($salonId);
                return;

            case 'delete':
                $this->deleteService($salonId);
                return;

            default:
                flash('error', 'Hành động không hợp lệ.');
                redirect(BASE_URL . '/owner/services');
        }
    }

    private function createService(int $salonId): void
    {
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $duration = (int) ($_POST['duration'] ?? 0);
        $image = trim($_POST['image'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($categoryId <= 0 || !$this->categoryModel->findById($categoryId)) {
            flash('error', 'Danh mục không hợp lệ.');
            redirect(BASE_URL . '/owner/services');
        }

        if ($name === '') {
            flash('error', 'Tên dịch vụ không được để trống.');
            redirect(BASE_URL . '/owner/services');
        }

        if ($price < 0) {
            flash('error', 'Giá dịch vụ không hợp lệ.');
            redirect(BASE_URL . '/owner/services');
        }

        if ($duration <= 0) {
            flash('error', 'Thời lượng phải lớn hơn 0.');
            redirect(BASE_URL . '/owner/services');
        }

        $this->serviceModel->create([
            'salon_id' => $salonId,
            'category_id' => $categoryId,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'price' => $price,
            'duration' => $duration,
            'image' => $image !== '' ? $image : null,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
        ]);

        flash('success', 'Đã thêm dịch vụ mới.');
        redirect(BASE_URL . '/owner/services');
    }

    private function updateService(int $salonId): void
    {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $service = $this->serviceModel->findById($serviceId);

        if (!$service || (int) $service['salon_id'] !== $salonId) {
            flash('error', 'Không tìm thấy dịch vụ thuộc salon của bạn.');
            redirect(BASE_URL . '/owner/services');
        }

        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $duration = (int) ($_POST['duration'] ?? 0);
        $image = trim($_POST['image'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($categoryId <= 0 || !$this->categoryModel->findById($categoryId)) {
            flash('error', 'Danh mục không hợp lệ.');
            redirect(BASE_URL . '/owner/services?edit_id=' . $serviceId);
        }

        if ($name === '') {
            flash('error', 'Tên dịch vụ không được để trống.');
            redirect(BASE_URL . '/owner/services?edit_id=' . $serviceId);
        }

        if ($price < 0) {
            flash('error', 'Giá dịch vụ không hợp lệ.');
            redirect(BASE_URL . '/owner/services?edit_id=' . $serviceId);
        }

        if ($duration <= 0) {
            flash('error', 'Thời lượng phải lớn hơn 0.');
            redirect(BASE_URL . '/owner/services?edit_id=' . $serviceId);
        }

        $this->serviceModel->update($serviceId, [
            'category_id' => $categoryId,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'price' => $price,
            'duration' => $duration,
            'image' => $image !== '' ? $image : null,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
        ]);

        flash('success', 'Đã cập nhật dịch vụ.');
        redirect(BASE_URL . '/owner/services');
    }

    private function toggleService(int $salonId): void
    {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $service = $this->serviceModel->findById($serviceId);

        if (!$service || (int) $service['salon_id'] !== $salonId) {
            flash('error', 'Không tìm thấy dịch vụ.');
            redirect(BASE_URL . '/owner/services');
        }

        $newStatus = ((int) $service['is_active'] === 1) ? 0 : 1;

        $this->serviceModel->update($serviceId, [
            'category_id' => $service['category_id'],
            'name' => $service['name'],
            'description' => $service['description'],
            'price' => $service['price'],
            'duration' => $service['duration'],
            'image' => $service['image'],
            'is_active' => $newStatus,
            'sort_order' => $service['sort_order'],
        ]);

        flash('success', 'Đã cập nhật trạng thái dịch vụ.');
        redirect(BASE_URL . '/owner/services');
    }

    private function deleteService(int $salonId): void
    {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $service = $this->serviceModel->findById($serviceId);

        if (!$service || (int) $service['salon_id'] !== $salonId) {
            flash('error', 'Không tìm thấy dịch vụ.');
            redirect(BASE_URL . '/owner/services');
        }

        $this->serviceModel->delete($serviceId);

        flash('success', 'Đã xóa dịch vụ.');
        redirect(BASE_URL . '/owner/services');
    }
}