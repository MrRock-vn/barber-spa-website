<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Salon.php';
require_once __DIR__ . '/../../models/Staff.php';

class StaffController
{
    private Salon $salonModel;
    private Staff $staffModel;

    public function __construct()
    {
        $this->salonModel = new Salon();
        $this->staffModel = new Staff();
    }

    public function index(): void
    {
        Auth::requireRole(['owner']);

        $ownerId = (int) Auth::id();
        $salon = $this->salonModel->getFirstByOwnerId($ownerId);

        if (!$salon) {
            echo '<h1>Owner Staff</h1>';
            echo '<p>Bạn chưa có salon nào.</p>';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostAction((int) $salon['id']);
            return;
        }

        $staffList = $this->staffModel->getBySalonId((int) $salon['id']);

        $editId = (int) ($_GET['edit_id'] ?? 0);
        $editingStaff = null;

        if ($editId > 0) {
            $candidate = $this->staffModel->findById($editId);
            if ($candidate && (int) $candidate['salon_id'] === (int) $salon['id']) {
                $editingStaff = $candidate;
            }
        }

        render('owner/staff/index', [
    'pageTitle' => 'Owner Staff - ' . APP_NAME,
    'navSection' => 'owner',
    'salon' => $salon,
    'staffList' => $staffList,
    'editingStaff' => $editingStaff,
]);
    }

    public function schedule(): void
    {
        Auth::requireRole(['owner']);

        $ownerId = (int) Auth::id();
        $salon = $this->salonModel->getFirstByOwnerId($ownerId);

        if (!$salon) {
            echo '<h1>Owner Staff Schedule</h1>';
            echo '<p>Bạn chưa có salon nào.</p>';
            return;
        }

        $staffId = (int) ($_GET['staff_id'] ?? $_POST['staff_id'] ?? 0);
        $staff = $this->staffModel->findById($staffId);

        if (!$staff || (int) $staff['salon_id'] !== (int) $salon['id']) {
            flash('error', 'Không tìm thấy nhân viên thuộc salon của bạn.');
            redirect(BASE_URL . '/owner/staff');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSchedulePost((int) $salon['id'], $staffId);
            return;
        }

        $schedules = $this->staffModel->getSchedules($staffId);
        $dayOffs = $this->staffModel->getDayOffs($staffId);

        $scheduleMap = [];
        foreach ($schedules as $schedule) {
            $scheduleMap[(int) $schedule['day_of_week']] = $schedule;
        }

   render('owner/staff/schedule', [
    'pageTitle' => 'Staff Schedule - ' . APP_NAME,
    'navSection' => 'owner',
    'salon' => $salon,
    'staff' => $staff,
    'schedules' => $schedules,
    'dayOffs' => $dayOffs,
    'scheduleMap' => $scheduleMap,
]);
    }

    private function handlePostAction(int $salonId): void
    {
        if (!verifyCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ.');
            redirect(BASE_URL . '/owner/staff');
        }

        $action = trim($_POST['action'] ?? '');

        switch ($action) {
            case 'create':
                $this->createStaff($salonId);
                return;

            case 'update':
                $this->updateStaff($salonId);
                return;

            case 'toggle':
                $this->toggleStaff($salonId);
                return;

            case 'delete':
                $this->deleteStaff($salonId);
                return;

            default:
                flash('error', 'Hành động không hợp lệ.');
                redirect(BASE_URL . '/owner/staff');
        }
    }

    private function createStaff(int $salonId): void
    {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $avatar = trim($_POST['avatar'] ?? '');
        $specialties = trim($_POST['specialties'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            flash('error', 'Tên nhân viên không được để trống.');
            redirect(BASE_URL . '/owner/staff');
        }

        $this->staffModel->create([
            'salon_id' => $salonId,
            'name' => $name,
            'phone' => $phone !== '' ? $phone : null,
            'avatar' => $avatar !== '' ? $avatar : null,
            'specialties' => $specialties !== '' ? $specialties : null,
            'is_active' => $isActive,
        ]);

        flash('success', 'Đã thêm nhân viên mới.');
        redirect(BASE_URL . '/owner/staff');
    }

    private function updateStaff(int $salonId): void
    {
        $staffId = (int) ($_POST['staff_id'] ?? 0);
        $staff = $this->staffModel->findById($staffId);

        if (!$staff || (int) $staff['salon_id'] !== $salonId) {
            flash('error', 'Không tìm thấy nhân viên.');
            redirect(BASE_URL . '/owner/staff');
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $avatar = trim($_POST['avatar'] ?? '');
        $specialties = trim($_POST['specialties'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            flash('error', 'Tên nhân viên không được để trống.');
            redirect(BASE_URL . '/owner/staff?edit_id=' . $staffId);
        }

        $this->staffModel->update($staffId, [
            'name' => $name,
            'phone' => $phone !== '' ? $phone : null,
            'avatar' => $avatar !== '' ? $avatar : null,
            'specialties' => $specialties !== '' ? $specialties : null,
            'is_active' => $isActive,
        ]);

        flash('success', 'Đã cập nhật nhân viên.');
        redirect(BASE_URL . '/owner/staff');
    }

    private function toggleStaff(int $salonId): void
    {
        $staffId = (int) ($_POST['staff_id'] ?? 0);
        $staff = $this->staffModel->findById($staffId);

        if (!$staff || (int) $staff['salon_id'] !== $salonId) {
            flash('error', 'Không tìm thấy nhân viên.');
            redirect(BASE_URL . '/owner/staff');
        }

        $newStatus = ((int) $staff['is_active'] === 1) ? 0 : 1;

        $this->staffModel->update($staffId, [
            'name' => $staff['name'],
            'phone' => $staff['phone'],
            'avatar' => $staff['avatar'],
            'specialties' => $staff['specialties'],
            'is_active' => $newStatus,
        ]);

        flash('success', 'Đã cập nhật trạng thái nhân viên.');
        redirect(BASE_URL . '/owner/staff');
    }

    private function deleteStaff(int $salonId): void
    {
        $staffId = (int) ($_POST['staff_id'] ?? 0);
        $staff = $this->staffModel->findById($staffId);

        if (!$staff || (int) $staff['salon_id'] !== $salonId) {
            flash('error', 'Không tìm thấy nhân viên.');
            redirect(BASE_URL . '/owner/staff');
        }

        $this->staffModel->delete($staffId);

        flash('success', 'Đã xóa nhân viên.');
        redirect(BASE_URL . '/owner/staff');
    }

    private function handleSchedulePost(int $salonId, int $staffId): void
    {
        if (!verifyCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ.');
            redirect(BASE_URL . '/owner/staff/schedule?staff_id=' . $staffId);
        }

        $action = trim($_POST['action'] ?? '');

        if ($action === 'save_schedule') {
            for ($day = 0; $day <= 6; $day++) {
                $startTime = trim($_POST['start_time'][$day] ?? '08:00:00');
                $endTime = trim($_POST['end_time'][$day] ?? '20:00:00');
                $isOff = isset($_POST['is_off'][$day]) ? 1 : 0;

                if ($startTime === '') {
                    $startTime = '08:00:00';
                }

                if ($endTime === '') {
                    $endTime = '20:00:00';
                }

                if (strlen($startTime) === 5) {
                    $startTime .= ':00';
                }

                if (strlen($endTime) === 5) {
                    $endTime .= ':00';
                }

                $this->staffModel->upsertSchedule($staffId, $day, $startTime, $endTime, $isOff);
            }

            flash('success', 'Đã lưu lịch làm việc.');
            redirect(BASE_URL . '/owner/staff/schedule?staff_id=' . $staffId);
        }

        if ($action === 'add_day_off') {
            $offDate = trim($_POST['off_date'] ?? '');
            $reason = trim($_POST['reason'] ?? '');

            if ($offDate === '') {
                flash('error', 'Vui lòng chọn ngày nghỉ.');
                redirect(BASE_URL . '/owner/staff/schedule?staff_id=' . $staffId);
            }

            $this->staffModel->addDayOff($staffId, $offDate, $reason !== '' ? $reason : null);

            flash('success', 'Đã thêm ngày nghỉ.');
            redirect(BASE_URL . '/owner/staff/schedule?staff_id=' . $staffId);
        }

        if ($action === 'remove_day_off') {
            $offDate = trim($_POST['off_date'] ?? '');

            if ($offDate === '') {
                flash('error', 'Ngày nghỉ không hợp lệ.');
                redirect(BASE_URL . '/owner/staff/schedule?staff_id=' . $staffId);
            }

            $this->staffModel->removeDayOff($staffId, $offDate);

            flash('success', 'Đã xóa ngày nghỉ.');
            redirect(BASE_URL . '/owner/staff/schedule?staff_id=' . $staffId);
        }

        flash('error', 'Hành động không hợp lệ.');
        redirect(BASE_URL . '/owner/staff/schedule?staff_id=' . $staffId);
    }
}