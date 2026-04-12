<?php

declare(strict_types=1);

$dayLabels = [
    0 => 'Chủ nhật',
    1 => 'Thứ 2',
    2 => 'Thứ 3',
    3 => 'Thứ 4',
    4 => 'Thứ 5',
    5 => 'Thứ 6',
    6 => 'Thứ 7',
];
?>

<div class="container">
    <div class="mb-4">
        <h2 class="page-section-title">Lịch làm việc nhân viên</h2>
        <div class="page-section-subtitle"><?= e($staff['name']) ?> - <?= e($salon['name']) ?></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Lịch làm việc theo tuần</h5>

            <form method="POST" action="<?= e(BASE_URL . '/owner/staff/schedule?staff_id=' . $staff['id']) ?>">
                <?= csrfInput() ?>
                <input type="hidden" name="action" value="save_schedule">
                <input type="hidden" name="staff_id" value="<?= e((string) $staff['id']) ?>">

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Bắt đầu</th>
                                <th>Kết thúc</th>
                                <th>Nghỉ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($day = 0; $day <= 6; $day++): ?>
                                <?php $schedule = $scheduleMap[$day] ?? null; ?>
                                <tr>
                                    <td><?= e($dayLabels[$day]) ?></td>
                                    <td>
                                        <input
                                            type="time"
                                            name="start_time[<?= e((string) $day) ?>]"
                                            class="form-control"
                                            value="<?= e(substr((string) ($schedule['start_time'] ?? '08:00:00'), 0, 5)) ?>"
                                        >
                                    </td>
                                    <td>
                                        <input
                                            type="time"
                                            name="end_time[<?= e((string) $day) ?>]"
                                            class="form-control"
                                            value="<?= e(substr((string) ($schedule['end_time'] ?? '20:00:00'), 0, 5)) ?>"
                                        >
                                    </td>
                                    <td>
                                        <input
                                            type="checkbox"
                                            name="is_off[<?= e((string) $day) ?>]"
                                            class="form-check-input"
                                            <?= ((int) ($schedule['is_off'] ?? 0) === 1) ? 'checked' : '' ?>
                                        >
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-dark">Lưu lịch làm việc</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Thêm ngày nghỉ riêng</h5>

                    <form method="POST" action="<?= e(BASE_URL . '/owner/staff/schedule?staff_id=' . $staff['id']) ?>">
                        <?= csrfInput() ?>
                        <input type="hidden" name="action" value="add_day_off">
                        <input type="hidden" name="staff_id" value="<?= e((string) $staff['id']) ?>">

                        <div class="mb-3">
                            <label class="form-label">Ngày nghỉ</label>
                            <input type="date" name="off_date" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lý do</label>
                            <textarea name="reason" class="form-control" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-danger">Thêm ngày nghỉ</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Danh sách ngày nghỉ riêng</h5>

                    <?php if (empty($dayOffs)): ?>
                        <div class="alert alert-info mb-0">Chưa có ngày nghỉ riêng nào.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Ngày nghỉ</th>
                                        <th>Lý do</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dayOffs as $dayOff): ?>
                                        <tr>
                                            <td><?= e(formatDate($dayOff['off_date'])) ?></td>
                                            <td><?= e((string) ($dayOff['reason'] ?? '')) ?: '-' ?></td>
                                            <td>
                                                <form method="POST" action="<?= e(BASE_URL . '/owner/staff/schedule?staff_id=' . $staff['id']) ?>">
                                                    <?= csrfInput() ?>
                                                    <input type="hidden" name="action" value="remove_day_off">
                                                    <input type="hidden" name="staff_id" value="<?= e((string) $staff['id']) ?>">
                                                    <input type="hidden" name="off_date" value="<?= e($dayOff['off_date']) ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="mt-3">
                        <a href="<?= e(BASE_URL . '/owner/staff') ?>" class="btn btn-outline-secondary">Quay lại staff</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>