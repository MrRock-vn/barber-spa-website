<?php
// includes/navbar.php — Navbar component (tái sử dụng)
// Được include vào tất cả các trang
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;
?>
<nav style="background: var(--dark); border-bottom: 1px solid var(--border); padding: 12px 0;">
  <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
    <!-- Logo -->
    <a href="/barber-spa-website/public/index.php" style="text-decoration: none; color: var(--text); font-weight: 700; font-size: 1.3rem;">
      ✂ Barber<span style="color: var(--brand);">&Spa</span>
    </a>

            <div style="display: flex; gap: 12px; align-items: center;">
      <?php if ($user): ?>
        <span style="color: var(--text-muted); font-size: 0.9rem;">
          👤 <?= htmlspecialchars($user['name']) ?>
        </span>
        <a href="/barber-spa-website/public/my-bookings.php" style="color: var(--text); text-decoration: none; padding: 8px 16px; background: var(--brand); border-radius: 6px; font-size: 0.9rem; font-weight: 600;">
          📅 Lịch hẹn
        </a>
        <a href="/barber-spa-website/public/logout.php" style="color: var(--text); text-decoration: none; padding: 8px 16px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.9rem;">
          Đăng xuất
        </a>
      <?php else: ?>
        <a href="/barber-spa-website/public/login.php" style="color: var(--text); text-decoration: none; padding: 8px 16px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.9rem;">
          Đăng nhập
        </a>
        <a href="/barber-spa-website/public/register.php" style="color: #fff; text-decoration: none; padding: 8px 16px; background: var(--brand); border-radius: 6px; font-size: 0.9rem; font-weight: 600;">
          Đăng ký
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>
