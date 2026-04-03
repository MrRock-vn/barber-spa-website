<?php
// public/booking.php — Đặt lịch hẹn (BOOK-01)
// Nguyễn Văn Danh phụ trách
session_start();
require_once __DIR__ . '/../config/db.php';

// Bắt buộc đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: /barber-spa-website/public/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$db      = getDB();
$user    = $_SESSION['user'];
$step    = (int)($_GET['step'] ?? 1);
$salonId = (int)($_GET['salon_id'] ?? 0);

// ── Xử lý action get_slots (AJAX) ──────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'get_slots') {
    header('Content-Type: application/json');
    $staffId  = (int)($_GET['staff_id']  ?? 0);
    $date     = $_GET['date']     ?? '';
    $duration = (int)($_GET['duration'] ?? 60);

    if (!$staffId || !$date) { echo json_encode([]); exit; }

    // Kiểm tra ngày hợp lệ
    $ts = strtotime($date);
    if (!$ts || $ts < strtotime('today')) { echo json_encode([]); exit; }

    $dow = (int)date('w', $ts); // 0=CN..6=T7

    // Lấy lịch làm việc của nhân viên hôm đó
    $sch = $db->prepare("SELECT * FROM staff_schedules WHERE staff_id = ? AND day_of_week = ? AND is_off = 0");
    $sch->execute([$staffId, $dow]);
    $schedule = $sch->fetch();

    if (!$schedule) { echo json_encode([]); exit; }

    // Lấy các booking đã có trong ngày đó
    $booked = $db->prepare("
        SELECT start_time, end_time FROM bookings
        WHERE staff_id = ? AND booking_date = ? AND status NOT IN ('cancelled')
    ");
    $booked->execute([$staffId, $date]);
    $bookedSlots = $booked->fetchAll();

    // Tạo danh sách slot 30 phút
    $slots     = [];
    $startWork = strtotime($date . ' ' . $schedule['start_time']);
    $endWork   = strtotime($date . ' ' . $schedule['end_time']) - ($duration * 60);
    $now       = time();
    $minBook   = $now + (2 * 3600); // không đặt trong vòng 2h tới

    for ($t = $startWork; $t <= $endWork; $t += 1800) {
        $slotEnd = $t + ($duration * 60);

        // Ẩn slot quá khứ / trong 2h tới
        if ($t < $minBook) continue;

        // Kiểm tra trùng với booking đã có
        $taken = false;
        foreach ($bookedSlots as $b) {
            $bs = strtotime($date . ' ' . $b['start_time']);
            $be = strtotime($date . ' ' . $b['end_time']);
            if ($t < $be && $slotEnd > $bs) { $taken = true; break; }
        }

        $slots[] = [
            'time'  => date('H:i', $t),
            'taken' => $taken,
        ];
    }

    echo json_encode($slots);
    exit;
}

// ── Lấy thông tin salon ────────────────────────────────────
if (!$salonId) { header('Location: /barber-spa-website/public/search.php'); exit; }

$stmt = $db->prepare("SELECT * FROM salons WHERE id = ? AND status = 'active'");
$stmt->execute([$salonId]); $salon = $stmt->fetch();
if (!$salon) { header('Location: /barber-spa-website/public/search.php'); exit; }

// Lấy dịch vụ
$services = $db->prepare("
    SELECT sv.*, c.name AS category_name FROM services sv
    LEFT JOIN categories c ON c.id = sv.category_id
    WHERE sv.salon_id = ? AND sv.is_active = 1 ORDER BY sv.sort_order
");
$services->execute([$salonId]); $services = $services->fetchAll();

// Lấy nhân viên
$staffList = $db->prepare("SELECT * FROM staff WHERE salon_id = ? AND is_active = 1");
$staffList->execute([$salonId]); $staffList = $staffList->fetchAll();

// ── Xử lý POST (lưu booking) ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 4) {
    $serviceIds = $_POST['service_ids'] ?? [];
    $staffId    = (int)($_POST['staff_id']    ?? 0);
    $bookDate   = $_POST['booking_date']  ?? '';
    $startTime  = $_POST['start_time']    ?? '';
    $notes      = trim($_POST['notes']    ?? '');
    $payMethod  = $_POST['payment_method'] ?? 'at_counter';
    $errors     = [];

    // Validate
    if (empty($serviceIds))  $errors[] = 'Vui lòng chọn ít nhất 1 dịch vụ.';
    if (!$bookDate || strtotime($bookDate) < strtotime('today')) $errors[] = 'Ngày đặt không hợp lệ.';
    if (!$startTime) $errors[] = 'Vui lòng chọn khung giờ.';

    // Kiểm tra giới hạn 5 lịch active
    $activeCount = $db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status IN ('pending','confirmed')");
    $activeCount->execute([$user['id']]); 
    if ($activeCount->fetchColumn() >= 5) $errors[] = 'Bạn đã có 5 lịch hẹn đang chờ. Vui lòng hoàn thành hoặc hủy trước.';

    if (empty($errors)) {
        // Tính tổng giá & duration
        $selectedServices = [];
        $totalPrice    = 0;
        $totalDuration = 0;

        foreach ($serviceIds as $svId) {
            foreach ($services as $sv) {
                if ($sv['id'] == $svId) {
                    $selectedServices[] = ['id'=>$sv['id'],'name'=>$sv['name'],'price'=>$sv['price'],'duration'=>$sv['duration']];
                    $totalPrice    += $sv['price'];
                    $totalDuration += $sv['duration'];
                    break;
                }
            }
        }

        $endTime = date('H:i:s', strtotime($bookDate . ' ' . $startTime) + ($totalDuration * 60));

        // Kiểm tra slot còn trống (double-check)
        $conflict = $db->prepare("
            SELECT id FROM bookings
            WHERE staff_id = ? AND booking_date = ? AND status NOT IN ('cancelled')
            AND start_time < ? AND end_time > ?
        ");
        $conflict->execute([$staffId ?: null, $bookDate, $endTime, $startTime]);

        if ($conflict->fetch()) {
            $errors[] = 'Khung giờ vừa được đặt bởi người khác. Vui lòng chọn giờ khác.';
        } else {
            // Lưu booking
            $ins = $db->prepare("
                INSERT INTO bookings (user_id, salon_id, staff_id, services, booking_date, start_time, end_time, total_price, payment_method, notes, slot_held_until)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))
            ");
            $ins->execute([
                $user['id'], $salonId,
                $staffId ?: null,
                json_encode($selectedServices, JSON_UNESCAPED_UNICODE),
                $bookDate, $startTime, $endTime,
                $totalPrice, $payMethod, $notes
            ]);
            $bookingId = $db->lastInsertId();

            // Redirect sang trang thành công
            header("Location: /barber-spa-website/public/booking-success.php?id=$bookingId");
            exit;
        }
    }
}

$pageTitle = 'Đặt lịch — ' . $salon['name'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="/barber-spa-website/public/css/style.css">
  <style>
    .step-content { display:none; }
    .step-content.active { display:block; }
    #countdown { font-size:.85rem; color:var(--primary); font-weight:600; }
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container" style="padding-top:36px;padding-bottom:80px;max-width:780px">

  <!-- Tên salon -->
  <a href="/barber-spa-website/public/salon-detail.php?id=<?= $salonId ?>" style="color:var(--text-muted);font-size:.88rem">← <?= htmlspecialchars($salon['name']) ?></a>
  <h1 style="font-family:'Playfair Display',serif;font-size:1.9rem;margin:10px 0 28px">📅 Đặt lịch hẹn</h1>

  <!-- Step Bar -->
  <div class="step-bar" style="margin-bottom:36px">
    <?php
    $steps = ['Chọn dịch vụ','Chọn nhân viên','Chọn ngày & giờ','Xác nhận'];
    foreach ($steps as $i => $label):
      $n = $i+1;
      $cls = $step > $n ? 'done' : ($step === $n ? 'active' : '');
    ?>
    <div class="step-item <?= $cls ?>">
      <div class="step-circle"><?= $step > $n ? '✓' : $n ?></div>
      <div class="step-label"><?= $label ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="margin-bottom:20px">
      <?php foreach ($errors as $e): ?><div>⚠️ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form id="bookingForm" method="POST" action="?salon_id=<?= $salonId ?>&step=4">
    <input type="hidden" name="payment_method" id="payMethod" value="at_counter">

    <!-- ════ BƯỚC 1: CHỌN DỊCH VỤ ════ -->
    <div class="step-content <?= $step===1?'active':'' ?>" id="step1">
      <h2 style="font-size:1.2rem;margin-bottom:20px">Chọn dịch vụ bạn muốn</h2>

      <?php
      $byCategory = [];
      foreach ($services as $sv) { $byCategory[$sv['category_name']??'Khác'][] = $sv; }
      foreach ($byCategory as $catName => $list):
      ?>
        <p style="color:var(--primary);font-size:.8rem;text-transform:uppercase;letter-spacing:2px;margin-bottom:10px;margin-top:20px"><?= htmlspecialchars($catName) ?></p>
        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:8px">
        <?php foreach ($list as $sv): ?>
          <div class="service-card" onclick="toggleService(this, <?= $sv['id'] ?>, <?= $sv['duration'] ?>, <?= $sv['price'] ?>)"
               data-id="<?= $sv['id'] ?>" data-duration="<?= $sv['duration'] ?>" data-price="<?= $sv['price'] ?>">
            <div>
              <div style="font-weight:600;margin-bottom:3px"><?= htmlspecialchars($sv['name']) ?></div>
              <div style="font-size:.82rem;color:var(--text-muted)">⏱ <?= $sv['duration'] ?> phút</div>
            </div>
            <div style="display:flex;align-items:center;gap:14px">
              <span style="font-weight:700;color:var(--primary)"><?= number_format($sv['price']) ?>đ</span>
              <div class="check"></div>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

      <!-- Tổng kết -->
      <div id="step1Summary" style="display:none;background:var(--dark2);border:1px solid rgba(200,150,62,.2);border-radius:12px;padding:16px;margin-top:20px">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
          <span style="color:var(--text-muted)">Tổng thời gian</span>
          <span id="totalDuration">0 phút</span>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-muted)">Tổng tiền dự kiến</span>
          <span id="totalPrice" style="color:var(--primary);font-weight:700">0đ</span>
        </div>
      </div>

      <div style="margin-top:24px;display:flex;justify-content:flex-end">
        <button type="button" onclick="goStep(2)" class="btn-primary-custom">
          Tiếp theo → Chọn nhân viên
        </button>
      </div>
    </div>

    <!-- ════ BƯỚC 2: CHỌN NHÂN VIÊN ════ -->
    <div class="step-content <?= $step===2?'active':'' ?>" id="step2">
      <h2 style="font-size:1.2rem;margin-bottom:20px">Chọn nhân viên phục vụ</h2>

      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:14px">
        <!-- Bất kỳ nhân viên -->
        <div class="staff-card selected" onclick="selectStaff(this, 0)" data-id="0" id="staffAny">
          <div style="width:64px;height:64px;border-radius:50%;background:var(--dark3);display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin:0 auto 10px">🎲</div>
          <div class="staff-name">Bất kỳ</div>
          <div style="font-size:.78rem;color:var(--text-muted);margin-top:4px">Để hệ thống chọn</div>
        </div>

        <?php foreach ($staffList as $s): ?>
        <div class="staff-card" onclick="selectStaff(this, <?= $s['id'] ?>)" data-id="<?= $s['id'] ?>">
          <img src="<?= htmlspecialchars($s['avatar'] ?? 'https://placehold.co/80/252a35/c8963e?text='.urlencode(mb_substr($s['name'],0,1))) ?>"
               alt="<?= htmlspecialchars($s['name']) ?>"
               onerror="this.src='https://placehold.co/80/252a35/c8963e?text=NV'">
          <div class="staff-name"><?= htmlspecialchars($s['name']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <input type="hidden" name="staff_id" id="staffIdInput" value="0">

      <div style="display:flex;justify-content:space-between;margin-top:28px">
        <button type="button" onclick="goStep(1)" class="btn-outline-custom">← Quay lại</button>
        <button type="button" onclick="goStep(3)" class="btn-primary-custom">Tiếp theo → Chọn ngày & giờ</button>
      </div>
    </div>

    <!-- ════ BƯỚC 3: CHỌN NGÀY & GIỜ ════ -->
    <div class="step-content <?= $step===3?'active':'' ?>" id="step3">
      <h2 style="font-size:1.2rem;margin-bottom:20px">Chọn ngày và khung giờ</h2>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
        <div class="form-group" style="margin:0">
          <label class="form-label">📅 Ngày hẹn</label>
          <input type="date" id="bookingDate" name="booking_date" class="form-control"
                 min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                 max="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                 onchange="loadSlots()">
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">👤 Nhân viên đã chọn</label>
          <div id="selectedStaffName" style="background:var(--dark3);border:1.5px solid rgba(255,255,255,.1);border-radius:12px;padding:12px 16px;color:var(--text);font-size:.95rem">
            Bất kỳ
          </div>
        </div>
      </div>

      <div id="slotsSection" style="display:none">
        <label class="form-label">🕐 Khung giờ trống</label>
        <div id="slotsLoading" style="display:none;color:var(--text-muted);font-size:.9rem;padding:16px 0">
          <span class="spinner"></span> Đang tải khung giờ...
        </div>
        <div id="slotsGrid" class="slot-grid"></div>
        <div id="noSlots" style="display:none;color:var(--text-muted);padding:16px 0">😕 Không có khung giờ trống ngày này.</div>
        <input type="hidden" name="start_time" id="selectedSlot" value="">
      </div>

      <div id="slotHint" style="color:var(--text-muted);font-size:.88rem;padding:16px 0">
        👆 Chọn ngày để xem khung giờ còn trống
      </div>

      <div style="display:flex;justify-content:space-between;margin-top:28px">
        <button type="button" onclick="goStep(2)" class="btn-outline-custom">← Quay lại</button>
        <button type="button" onclick="goStep(4)" class="btn-primary-custom" id="btnToStep4" disabled style="opacity:.5">
          Tiếp theo → Xác nhận
        </button>
      </div>
    </div>

    <!-- ════ BƯỚC 4: XÁC NHẬN ════ -->
    <div class="step-content <?= $step===4?'active':'' ?>" id="step4">
      <h2 style="font-size:1.2rem;margin-bottom:20px">Xác nhận đặt lịch</h2>

      <div class="alert alert-info" style="display:flex;align-items:center;justify-content:space-between">
        <span>⏳ Slot được giữ trong <strong id="countdown">10:00</strong></span>
        <small>Vui lòng hoàn tất trước khi hết giờ</small>
      </div>

      <!-- Tóm tắt -->
      <div class="summary-box" style="margin-bottom:20px">
        <h3 style="font-size:1rem;margin-bottom:14px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px">Chi tiết lịch hẹn</h3>
        <div class="summary-row"><span>Salon</span><span style="font-weight:600"><?= htmlspecialchars($salon['name']) ?></span></div>
        <div class="summary-row"><span>Địa chỉ</span><span style="color:var(--text-muted)"><?= htmlspecialchars($salon['address']) ?></span></div>
        <div class="summary-row" id="summaryServices"><span>Dịch vụ</span><span>—</span></div>
        <div class="summary-row" id="summaryStaff"><span>Nhân viên</span><span>Bất kỳ</span></div>
        <div class="summary-row" id="summaryDate"><span>Ngày hẹn</span><span>—</span></div>
        <div class="summary-row" id="summaryTime"><span>Giờ bắt đầu</span><span>—</span></div>
        <div class="summary-row" id="summaryDuration"><span>Thời gian dự kiến</span><span>—</span></div>
        <div class="summary-row">
          <span style="font-weight:700">Tổng tiền</span>
          <span class="summary-total" id="summaryTotal">0đ</span>
        </div>
      </div>

      <!-- Ghi chú -->
      <div class="form-group">
        <label class="form-label">📝 Ghi chú (không bắt buộc)</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="VD: Tôi muốn cắt kiểu Hàn, giữ phần mái..."></textarea>
      </div>

      <!-- Thanh toán -->
      <div class="form-group">
        <label class="form-label">💳 Phương thức thanh toán</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div class="service-card selected" id="payAtCounter" onclick="selectPay('at_counter',this)" style="padding:14px">
            <div>
              <div style="font-weight:600">🏪 Thanh toán tại quầy</div>
              <div style="font-size:.8rem;color:var(--text-muted);margin-top:3px">Trả tiền khi đến salon</div>
            </div>
            <div class="check selected">✓</div>
          </div>
          <div class="service-card" id="payOnline" onclick="selectPay('online',this)" style="padding:14px">
            <div>
              <div style="font-weight:600">💳 Thanh toán online</div>
              <div style="font-size:.8rem;color:var(--text-muted);margin-top:3px">VNPay / Momo / ZaloPay</div>
            </div>
            <div class="check"></div>
          </div>
        </div>
      </div>

      <!-- Hidden inputs để submit -->
      <div id="serviceInputs"></div>

      <div style="display:flex;justify-content:space-between;margin-top:24px;align-items:center">
        <button type="button" onclick="goStep(3)" class="btn-outline-custom">← Quay lại</button>
        <button type="submit" class="btn-primary-custom" style="padding:14px 32px;font-size:1rem" id="btnSubmit">
          ✅ Xác nhận đặt lịch
        </button>
      </div>
    </div>

  </form><!-- end form -->
</div>

<footer><div class="container"><p>© 2026 Barber &amp; Spa</p></div></footer>

<script>
// ═══════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════
var state = {
  selectedServices: [],  // [{id, name, price, duration}]
  staffId: 0,
  staffName: 'Bất kỳ',
  bookingDate: '',
  startTime: '',
  totalPrice: 0,
  totalDuration: 0,
};

// ═══════════════════════════════════════════════
// BƯỚC 1 — Chọn dịch vụ
// ═══════════════════════════════════════════════
function toggleService(el, id, duration, price) {
  var idx = state.selectedServices.findIndex(s => s.id === id);
  if (idx >= 0) {
    state.selectedServices.splice(idx, 1);
    el.classList.remove('selected');
    el.querySelector('.check').innerHTML = '';
  } else {
    // Lấy tên dịch vụ
    var name = el.querySelector('[style*="font-weight:600"]').textContent.trim();
    state.selectedServices.push({id, name, price, duration});
    el.classList.add('selected');
    el.querySelector('.check').innerHTML = '✓';
  }
  updateStep1Summary();
}

function updateStep1Summary() {
  var total = 0, dur = 0;
  state.selectedServices.forEach(s => { total += Number(s.price); dur += Number(s.duration); });
  state.totalPrice    = total;
  state.totalDuration = dur;

  var box = document.getElementById('step1Summary');
  if (state.selectedServices.length > 0) {
    box.style.display = 'block';
    document.getElementById('totalPrice').textContent    = total.toLocaleString('vi-VN') + 'đ';
    document.getElementById('totalDuration').textContent = dur + ' phút';
  } else {
    box.style.display = 'none';
  }
}

// ═══════════════════════════════════════════════
// BƯỚC 2 — Chọn nhân viên
// ═══════════════════════════════════════════════
function selectStaff(el, id) {
  document.querySelectorAll('.staff-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  state.staffId = id;
  state.staffName = id === 0 ? 'Bất kỳ' : el.querySelector('.staff-name').textContent;
  document.getElementById('staffIdInput').value = id;
}

// ═══════════════════════════════════════════════
// BƯỚC 3 — Chọn ngày & giờ
// ═══════════════════════════════════════════════
function loadSlots() {
  var date = document.getElementById('bookingDate').value;
  if (!date) return;
  state.bookingDate = date;

  // Cập nhật tên nhân viên
  document.getElementById('selectedStaffName').textContent = state.staffName;

  document.getElementById('slotsSection').style.display = 'block';
  document.getElementById('slotHint').style.display      = 'none';
  document.getElementById('slotsLoading').style.display  = 'block';
  document.getElementById('slotsGrid').innerHTML         = '';
  document.getElementById('noSlots').style.display       = 'none';
  document.getElementById('selectedSlot').value          = '';
  document.getElementById('btnToStep4').disabled         = true;
  document.getElementById('btnToStep4').style.opacity    = '.5';

  // Nếu chọn "Bất kỳ" → dùng nhân viên đầu tiên có slot
  var staffId = state.staffId;
  // Nếu staffId = 0 thì lấy từ nhân viên đầu tiên trong danh sách
  if (staffId === 0) {
    var firstStaff = document.querySelector('.staff-card[data-id]:not([data-id="0"])');
    staffId = firstStaff ? parseInt(firstStaff.getAttribute('data-id')) : 0;
  }

  var url = '?salon_id=<?= $salonId ?>&action=get_slots'
          + '&staff_id=' + staffId
          + '&date=' + date
          + '&duration=' + state.totalDuration;

  fetch(url)
    .then(r => r.json())
    .then(function(slots) {
      document.getElementById('slotsLoading').style.display = 'none';
      var grid = document.getElementById('slotsGrid');

      if (!slots.length) {
        document.getElementById('noSlots').style.display = 'block';
        return;
      }

      slots.forEach(function(slot) {
        var btn = document.createElement('div');
        btn.className  = 'slot-btn' + (slot.taken ? ' taken' : '');
        btn.textContent = slot.time;
        if (!slot.taken) {
          btn.onclick = function() { selectSlot(btn, slot.time); };
        }
        grid.appendChild(btn);
      });
    })
    .catch(function() {
      document.getElementById('slotsLoading').style.display = 'none';
      document.getElementById('slotsGrid').innerHTML = '<p style="color:var(--danger)">Lỗi tải khung giờ. Vui lòng thử lại.</p>';
    });
}

function selectSlot(el, time) {
  document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
  el.classList.add('selected');
  state.startTime = time;
  document.getElementById('selectedSlot').value         = time;
  document.getElementById('btnToStep4').disabled        = false;
  document.getElementById('btnToStep4').style.opacity   = '1';
}

// ═══════════════════════════════════════════════
// BƯỚC 4 — Xác nhận
// ═══════════════════════════════════════════════
function selectPay(method, el) {
  document.getElementById('payMethod').value = method;
  ['payAtCounter','payOnline'].forEach(id => {
    var card = document.getElementById(id);
    card.classList.remove('selected');
    card.querySelector('.check').classList.remove('selected');
    card.querySelector('.check').innerHTML = '';
  });
  el.classList.add('selected');
  el.querySelector('.check').classList.add('selected');
  el.querySelector('.check').innerHTML = '✓';
}

function fillStep4Summary() {
  // Dịch vụ
  var svNames = state.selectedServices.map(s => s.name).join(', ');
  document.getElementById('summaryServices').innerHTML =
    '<span>Dịch vụ</span><span style="text-align:right;max-width:60%">' + svNames + '</span>';

  // Nhân viên
  document.getElementById('summaryStaff').innerHTML =
    '<span>Nhân viên</span><span>' + state.staffName + '</span>';

  // Ngày
  if (state.bookingDate) {
    var d = new Date(state.bookingDate);
    var formatted = d.toLocaleDateString('vi-VN', {weekday:'long', year:'numeric', month:'long', day:'numeric'});
    document.getElementById('summaryDate').innerHTML =
      '<span>Ngày hẹn</span><span>' + formatted + '</span>';
  }

  // Giờ
  document.getElementById('summaryTime').innerHTML =
    '<span>Giờ bắt đầu</span><span>' + (state.startTime || '—') + '</span>';

  // Thời gian
  document.getElementById('summaryDuration').innerHTML =
    '<span>Thời gian dự kiến</span><span>~' + state.totalDuration + ' phút</span>';

  // Tổng tiền
  document.getElementById('summaryTotal').textContent =
    state.totalPrice.toLocaleString('vi-VN') + 'đ';

  // Hidden inputs cho form submit
  var container = document.getElementById('serviceInputs');
  container.innerHTML = '';
  state.selectedServices.forEach(function(s) {
    var inp = document.createElement('input');
    inp.type  = 'hidden';
    inp.name  = 'service_ids[]';
    inp.value = s.id;
    container.appendChild(inp);
  });

  // Cũng truyền booking_date và start_time vào form nếu chưa có giá trị
  document.querySelector('[name="booking_date"]').value = state.bookingDate;
  document.getElementById('selectedSlot').value         = state.startTime;
  document.getElementById('staffIdInput').value         = state.staffId;
}

// ═══════════════════════════════════════════════
// ĐIỀU HƯỚNG STEP
// ═══════════════════════════════════════════════
var currentStep = 1;

function goStep(n) {
  // Validate trước khi chuyển
  if (n === 2 && state.selectedServices.length === 0) {
    alert('⚠️ Vui lòng chọn ít nhất 1 dịch vụ!');
    return;
  }
  if (n === 4) {
    if (!state.bookingDate) { alert('⚠️ Vui lòng chọn ngày hẹn!'); return; }
    if (!state.startTime)   { alert('⚠️ Vui lòng chọn khung giờ!'); return; }
    fillStep4Summary();
    startCountdown(600); // 10 phút
  }

  document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
  document.getElementById('step' + n).classList.add('active');
  currentStep = n;

  // Update step bar
  document.querySelectorAll('.step-item').forEach(function(el, i) {
    el.classList.remove('active','done');
    if (i + 1 < n)      el.classList.add('done');
    else if (i + 1 === n) el.classList.add('active');
  });

  window.scrollTo({top:0, behavior:'smooth'});
}

// ═══════════════════════════════════════════════
// COUNTDOWN 10 PHÚT
// ═══════════════════════════════════════════════
var countdownInterval = null;
function startCountdown(seconds) {
  clearInterval(countdownInterval);
  var remaining = seconds;
  function tick() {
    var m = Math.floor(remaining / 60);
    var s = remaining % 60;
    var el = document.getElementById('countdown');
    if (el) el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
    if (remaining <= 0) {
      clearInterval(countdownInterval);
      alert('⏰ Hết thời gian giữ slot! Vui lòng chọn lại khung giờ.');
      goStep(3);
    }
    remaining--;
  }
  tick();
  countdownInterval = setInterval(tick, 1000);
}

// Submit — hiện spinner
document.getElementById('bookingForm').addEventListener('submit', function() {
  var btn = document.getElementById('btnSubmit');
  btn.innerHTML = '<span class="spinner"></span> Đang xử lý...';
  btn.disabled = true;
});
</script>
</body>
</html>
