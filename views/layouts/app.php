<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? APP_NAME;
$content = $content ?? '';
$bodyClass = $bodyClass ?? 'bg-light';
$showNavbar = $showNavbar ?? true;
$navSection = $navSection ?? 'public';

function renderNavLinks(string $navSection): void
{
    switch ($navSection) {
        case 'admin':
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/admin/dashboard') . '">Dashboard</a>';
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/admin/users') . '">Users</a>';
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/admin/salons') . '">Salons</a>';
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/admin/categories') . '">Categories</a>';
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/admin/bookings') . '">Bookings</a>';
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/admin/reviews') . '">Reviews</a>';
            break;

        case 'owner':
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/owner/dashboard') . '">Dashboard</a>';
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/owner/bookings') . '">Bookings</a>';
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/owner/revenue') . '">Revenue</a>';
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/owner/services') . '">Services</a>';
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/owner/staff') . '">Staff</a>';
            break;

        case 'user':
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/home') . '">Trang chủ</a>';
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/my-bookings') . '">Lịch hẹn của tôi</a>';
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/my-profile') . '">Tài khoản</a>';
            break;

        default:
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/home') . '">Trang chủ</a>';

            if (Auth::check()) {
                echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/my-bookings') . '">Lịch hẹn</a>';
                echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/my-profile') . '">Tài khoản</a>';
            } else {
                echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/login') . '">Đăng nhập</a>';
                echo '<a class="btn btn-danger btn-sm" href="' . e(BASE_URL . '/register') . '">Đăng ký</a>';
            }
            break;
    }
}

function renderBrandBySection(string $navSection): string
{
    return match ($navSection) {
        'admin' => 'Admin Panel',
        'owner' => 'Owner Panel',
        default => APP_NAME,
    };
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(BASE_URL . '/public/css/style.css') ?>" rel="stylesheet">
</head>
<body class="<?= e($bodyClass) ?> d-flex flex-column min-vh-100">

<?php if ($showNavbar): ?>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= e(BASE_URL . '/home') ?>">
            <?= e(renderBrandBySection($navSection)) ?>
        </a>

        <div class="ms-auto d-flex gap-2 flex-wrap">
            <?php renderNavLinks($navSection); ?>

            <?php if (Auth::check()): ?>
                <a class="btn btn-danger btn-sm" href="<?= e(BASE_URL . '/logout') ?>">Đăng xuất</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <?php require __DIR__ . '/flash.php'; ?>
    </div>

    <?= $content ?>
</main>

<footer class="app-footer mt-auto">
    <div class="container py-3 text-center text-muted small">
        © <?= e((string) date('Y')) ?> <?= e(APP_NAME) ?>. MVC PHP thuần + MySQL + Bootstrap 5
    </div>
</footer>

</body>
</html>