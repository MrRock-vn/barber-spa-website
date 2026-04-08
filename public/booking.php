<?php
session_start();
require_once __DIR__ . '/../config/db.php';
requireLogin();

$user = currentUser();
$step = (int)($_GET['step'] ?? 1);
$salonId = (int)($_GET['salon_id'] ?? 0);

if (!$salonId) {
    header('Location: /barber-spa-website/public/search.php');
    exit;
}

$salon = fetchOne("SELECT * FROM salons WHERE id = $salonId AND status = 'active'");
if (!$salon) {
    header('Location: /barber-spa-website/public/search.php');
    exit;
}

$services = fetchAll("SELECT sv.*, c.name AS category_name FROM services sv LEFT JOIN categories c ON c.id = sv.category_id WHERE sv.salon_id = $salonId AND sv.is_active = 1 ORDER BY c.name, sv.sort_order");

$staffList = fetchAll("SELECT * FROM staff WHERE salon_id = $salonId AND is_active = 1");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 4) {
    $serviceIds = $_POST['service_ids'] ?? [];
    $staffId = (int)($_POST['staff_id'] ?? 0);
    $bookDate = $_POST['booking_date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    $payMethod = $_POST['payment_method'] ?? 'at_counter';
    $errors = [];

    if (empty($serviceIds)) $errors[] = 'Vui lòng chọn ít nhất 1 dịch vụ.';
    if (!$bookDate || strtotime($bookDate) < strtotime('today')) $errors[] = 'Ngày đặt không hợp lệ.';
    if (!$startTime) $errors[] = 'Vui lòng chọn khung giờ.';

    if (empty($errors)) {
        $selectedServices = [];
        $totalPrice = 0;
        $totalDuration = 0;

        foreach ($serviceIds as $svId) {
            foreach ($services as $sv) {
                if ($sv['id'] == $svId) {
                    $selectedServices[] = ['id'=>$sv['id'],'name'=>$sv['name'],'price'=>$sv['price'],'duration'=>$sv['duration']];
                    $totalPrice += $sv['price'];
                    $totalDuration += $sv['duration'];
                    break;
                }
            }
        }

        $endTime = date('H:i:s', strtotime($bookDate . ' ' . $startTime) + ($totalDuration * 60));
        $servicesJson = escape(json_encode($selectedServices, JSON_UNESCAPED_UNICODE));
        $notes_safe = escape($notes);
        $ip = escape($_SERVER['REMOTE_ADDR']);

        execute("INSERT INTO bookings (user_id, salon_id, staff_id, services, booking_date, start_time, end_time, total_price, payment_method, notes, slot_held_until) VALUES ({$user['id']}, $salonId, " . ($staffId ?: 'NULL') . ", '$servicesJson', '$bookDate', '$startTime', '$endTime', $totalPrice, '$payMethod', '$notes_safe', DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
        
        $bookingId = lastInsertId();
        header("Location: /barber-spa-website/public/booking-success.php?id=$bookingId");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch — <?= htmlspecialchars($salon['name']) ?></title>
    <link rel="stylesheet" href="/barber-spa-website/public/css/style.css">
    <style>
        .step-content { display:none; }
        .step-content.active { display:block; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container" style="padding-top:36px;padding-bottom:80px;max-width:780px">
    <h1 style="font-size:1.9rem;margin:10px 0 28px">📅 Đặt lịch hẹn</h1>
    
    <form id="bookingForm" method="POST" action="?salon_id=<?= $salonId ?>&step=4">
        <input type="hidden" name="payment_method" id="payMethod" value="at_counter">
        
        <!-- BƯỚC 1: CHỌN DỊCH VỤ -->
        <div class="step-content active" id="step1">
            <h2 style="font-size:1.2rem;margin-bottom:20px">Chọn dịch vụ bạn muốn</h2>
            <?php foreach ($services as $sv): ?>
            <div style="background:#f8f9fa;border:1px solid #ddd;border-radius:8px;padding:12px;margin-bottom:8px;display:flex;justify-content:space-between;cursor:pointer" onclick="toggleService(this, <?= $sv['id'] ?>, <?= $sv['duration'] ?>, <?= $sv['price'] ?>)">
                <div>
                    <div style="font-weight:600"><?= htmlspecialchars($sv['name']) ?></div>
                    <div style="font-size:.82rem;color:#666">⏱ <?= $sv['duration'] ?> phút</div>
                </div>
                <div style="display:flex;align-items:center;gap:14px">
                    <span style="font-weight:700;color:#e94560"><?= number_format($sv['price']) ?>đ</span>
                    <div class="check"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <button type="button" onclick="goStep(2)" style="background:#e94560;color:#fff;padding:12px 24px;border:none;border-radius:8px;font-weight:600;cursor:pointer;margin-top:20px">Tiếp theo →</button>
        </div>

        <!-- BƯỚC 2: CHỌN NHÂN VIÊN -->
        <div class="step-content" id="step2">
            <h2 style="font-size:1.2rem;margin-bottom:20px">Chọn nhân viên phục vụ</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:14px">
                <div style="background:#f8f9fa;border:1px solid #ddd;border-radius:8px;padding:12px;text-align:center;cursor:pointer" onclick="selectStaff(this, 0)">
                    <div style="font-size:1.6rem;margin-bottom:10px">🎲</div>
                    <div style="font-weight:600">Bất kỳ</div>
                </div>
                <?php foreach ($staffList as $s): ?>
                <div style="background:#f8f9fa;border:1px solid #ddd;border-radius:8px;padding:12px;text-align:center;cursor:pointer" onclick="selectStaff(this, <?= $s['id'] ?>)">
                    <img src="<?= htmlspecialchars($s['avatar'] ?? 'https://placehold.co/80/252a35/c8963e?text=NV') ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin:0 auto 8px">
                    <div style="font-weight:600"><?= htmlspecialchars($s['name']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="staff_id" id="staffIdInput" value="0">
            <div style="display:flex;justify-content:space-between;margin-top:28px">
                <button type="button" onclick="goStep(1)" style="background:transparent;color:#333;border:1px solid #ddd;padding:12px 24px;border-radius:8px;font-weight:600;cursor:pointer">← Quay lại</button>
                <button type="button" onclick="goStep(3)" style="background:#e94560;color:#fff;padding:12px 24px;border:none;border-radius:8px;font-weight:600;cursor:pointer">Tiếp theo →</button>
            </div>
        </div>

        <!-- BƯỚC 3: CHỌN NGÀY & GIỜ -->
        <div class="step-content" id="step3">
            <h2 style="font-size:1.2rem;margin-bottom:20px">Chọn ngày và khung giờ</h2>
            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:600;margin-bottom:8px">📅 Ngày hẹn</label>
                <input type="date" id="bookingDate" name="booking_date" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" max="<?= date('Y-m-d', strtotime('+30 days')) ?>">
            </div>
            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:600;margin-bottom:8px">🕐 Khung giờ</label>
                <div id="slotsGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(70px,1fr));gap:10px"></div>
                <input type="hidden" name="start_time" id="selectedSlot" value="">
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:28px">
                <button type="button" onclick="goStep(2)" style="background:transparent;color:#333;border:1px solid #ddd;padding:12px 24px;border-radius:8px;font-weight:600;cursor:pointer">← Quay lại</button>
                <button type="button" onclick="goStep(4)" style="background:#e94560;color:#fff;padding:12px 24px;border:none;border-radius:8px;font-weight:600;cursor:pointer" id="btnToStep4" disabled>Tiếp theo →</button>
            </div>
        </div>

        <!-- BƯỚC 4: XÁC NHẬN -->
        <div class="step-content" id="step4">
            <h2 style="font-size:1.2rem;margin-bottom:20px">Xác nhận đặt lịch</h2>
            <div style="background:#f8f9fa;border:1px solid #ddd;border-radius:8px;padding:20px;margin-bottom:20px">
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #ddd"><span>Salon</span><span style="font-weight:600"><?= htmlspecialchars($salon['name']) ?></span></div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #ddd"><span>Ngày hẹn</span><span id="summaryDate">—</span></div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #ddd"><span>Giờ hẹn</span><span id="summaryTime">—</span></div>
                <div style="display:flex;justify-content:space-between;padding:10px 0"><span style="font-weight:700">Tổng tiền</span><span id="summaryTotal" style="color:#e94560;font-weight:700">0đ</span></div>
            </div>
            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:600;margin-bottom:8px">💳 Phương thức thanh toán</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div style="background:#f8f9fa;border:1px solid #ddd;border-radius:8px;padding:14px;cursor:pointer" onclick="selectPay('at_counter',this)">
                        <div style="font-weight:600">🏪 Thanh toán tại quầy</div>
                    </div>
                    <div style="background:#f8f9fa;border:1px solid #ddd;border-radius:8px;padding:14px;cursor:pointer" onclick="selectPay('online',this)">
                        <div style="font-weight:600">💳 Thanh toán online</div>
                    </div>
                </div>
            </div>
            <div id="serviceInputs"></div>
            <div style="display:flex;justify-content:space-between;margin-top:24px">
                <button type="button" onclick="goStep(3)" style="background:transparent;color:#333;border:1px solid #ddd;padding:12px 24px;border-radius:8px;font-weight:600;cursor:pointer">← Quay lại</button>
                <button type="submit" style="background:#e94560;color:#fff;padding:12px 32px;border:none;border-radius:8px;font-weight:600;cursor:pointer">✅ Xác nhận đặt lịch</button>
            </div>
        </div>
    </form>
</div>

<script>
var state = { selectedServices: [], staffId: 0, bookingDate: '', startTime: '', totalPrice: 0, totalDuration: 0 };

function toggleService(el, id, duration, price) {
    var idx = state.selectedServices.findIndex(s => s.id === id);
    if (idx >= 0) {
        state.selectedServices.splice(idx, 1);
        el.style.background = '#f8f9fa';
    } else {
        var name = el.querySelector('[style*="font-weight:600"]').textContent.trim();
        state.selectedServices.push({id, name, price, duration});
        el.style.background = 'rgba(233,69,96,.1)';
    }
    state.totalPrice = state.selectedServices.reduce((a,b) => a + b.price, 0);
    state.totalDuration = state.selectedServices.reduce((a,b) => a + b.duration, 0);
}

function selectStaff(el, id) {
    document.querySelectorAll('#step2 > div > div').forEach(c => c.style.borderColor = '#ddd');
    el.style.borderColor = '#e94560';
    state.staffId = id;
    document.getElementById('staffIdInput').value = id;
}

function selectPay(method, el) {
    document.getElementById('payMethod').value = method;
    document.querySelectorAll('[onclick*="selectPay"]').forEach(e => e.style.borderColor = '#ddd');
    el.style.borderColor = '#e94560';
}

function goStep(n) {
    if (n === 4) {
        document.getElementById('summaryDate').textContent = state.bookingDate;
        document.getElementById('summaryTime').textContent = state.startTime;
        document.getElementById('summaryTotal').textContent = state.totalPrice.toLocaleString('vi-VN') + 'đ';
        var container = document.getElementById('serviceInputs');
        container.innerHTML = '';
        state.selectedServices.forEach(s => {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'service_ids[]';
            inp.value = s.id;
            container.appendChild(inp);
        });
    }
    document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
    document.getElementById('step' + n).classList.add('active');
    window.scrollTo({top:0, behavior:'smooth'});
}

document.getElementById('bookingDate').addEventListener('change', function() {
    state.bookingDate = this.value;
    var grid = document.getElementById('slotsGrid');
    grid.innerHTML = '';
    for (var i = 8; i < 18; i++) {
        var btn = document.createElement('div');
        btn.textContent = (i < 10 ? '0' : '') + i + ':00';
        btn.style.cssText = 'background:#f8f9fa;border:1px solid #ddd;border-radius:8px;padding:12px;text-align:center;cursor:pointer;font-weight:600';
        btn.onclick = function() {
            document.querySelectorAll('#slotsGrid > div').forEach(b => b.style.borderColor = '#ddd');
            this.style.borderColor = '#e94560';
            state.startTime = this.textContent;
            document.getElementById('selectedSlot').value = state.startTime;
            document.getElementById('btnToStep4').disabled = false;
        };
        grid.appendChild(btn);
    }
});
</script>
</body>
</html>
