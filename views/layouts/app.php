<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? APP_NAME;
$content = $content ?? '';
$bodyClass = $bodyClass ?? 'bg-light';
$showNavbar = $showNavbar ?? true;
$navSection = $navSection ?? 'public';
$bodyClass = trim($bodyClass . match ($navSection) {
    'admin' => ' admin-layout',
    'owner' => ' owner-layout',
    default => '',
});

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
            echo '<a class="btn btn-outline-light btn-sm" href="' . e(BASE_URL . '/owner/reviews') . '">Reviews</a>';
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

function getCurrentPath(): string
{
    return (string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
}

function isActiveAdminLink(string $linkPath, string $currentPath): bool
{
    return str_contains($currentPath, $linkPath);
}

function isActiveOwnerLink(string $linkPath, string $currentPath): bool
{
    return str_contains($currentPath, $linkPath);
}

function renderAdminSidebar(string $currentPath): void
{
    $items = [
        '/admin/dashboard' => 'Dashboard',
        '/admin/users' => 'Users',
        '/admin/salons' => 'Salons',
        '/admin/categories' => 'Categories',
        '/admin/bookings' => 'Bookings',
        '/admin/reviews' => 'Reviews',
    ];

    echo '<div class="admin-sidebar">';
    echo '<div class="sidebar-title">Admin menu</div>';
    echo '<nav class="nav flex-column gap-2">';

    foreach ($items as $path => $label) {
        $active = isActiveAdminLink($path, $currentPath) ? ' active' : '';
        echo '<a class="nav-link btn btn-outline-light btn-sm text-start w-100' . $active . '" href="' . e(BASE_URL . $path) . '">' . e($label) . '</a>';
    }

    echo '</nav>';
    echo '</div>';
}

function renderOwnerSidebar(string $currentPath): void
{
    $items = [
        '/owner/dashboard' => 'Dashboard',
        '/owner/bookings' => 'Bookings',
        '/owner/revenue' => 'Revenue',
        '/owner/services' => 'Services',
        '/owner/staff' => 'Staff',
        '/owner/reviews' => 'Reviews',
    ];

    echo '<div class="admin-sidebar">';
    echo '<div class="sidebar-title">Owner menu</div>';
    echo '<nav class="nav flex-column gap-2">';

    foreach ($items as $path => $label) {
        $active = isActiveOwnerLink($path, $currentPath) ? ' active' : '';
        echo '<a class="nav-link btn btn-outline-light btn-sm text-start w-100' . $active . '" href="' . e(BASE_URL . $path) . '">' . e($label) . '</a>';
    }

    echo '</nav>';
    echo '</div>';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(BASE_URL . '/public/css/style.css') ?>" rel="stylesheet">
</head>
<body class="<?= e($bodyClass) ?> d-flex flex-column min-vh-100">

<?php if ($showNavbar): ?>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar <?= $navSection === 'admin' ? 'app-navbar-admin' : '' ?>">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= e($navSection === 'admin' ? BASE_URL . '/admin/dashboard' : BASE_URL . '/home') ?>">
            <?= e(renderBrandBySection($navSection)) ?>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <div class="navbar-nav ms-auto align-items-center gap-2">
                <?php if ($navSection !== 'admin'): ?>
                    <?php renderNavLinks($navSection); ?>
                <?php endif; ?>

                <?php if (Auth::check()): ?>
                    <a class="btn btn-danger btn-sm" href="<?= e(BASE_URL . '/logout') ?>">Đăng xuất</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="flex-grow-1 py-4">
    <?php if ($navSection === 'admin' || $navSection === 'owner'): ?>
        <div class="container-fluid admin-container-fluid">
            <div class="row gx-4">
                <aside class="col-12 col-lg-3 col-xl-2 mb-4 mb-lg-0">
                    <?php if ($navSection === 'admin'): ?>
                        <?php renderAdminSidebar(getCurrentPath()); ?>
                    <?php else: ?>
                        <?php renderOwnerSidebar(getCurrentPath()); ?>
                    <?php endif; ?>
                </aside>

                <section class="col-12 col-lg-9 col-xl-10 admin-content">
                    <div class="container-fluid p-0">
                        <?php require __DIR__ . '/flash.php'; ?>
                        <?= $content ?>
                    </div>
                </section>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <?php require __DIR__ . '/flash.php'; ?>
        </div>

        <div class="container">
            <?= $content ?>
        </div>
    <?php endif; ?>
</main>

<footer class="app-footer mt-auto">
    <div class="container py-3 text-center text-muted small">
        © <?= e((string) date('Y')) ?> <?= e(APP_NAME) ?>. MVC PHP thuần + MySQL + Bootstrap 5
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function cleanupStaleModalBackdrop() {
    const activeModals = document.querySelectorAll('.modal.show');
    if (activeModals.length > 0) {
        return;
    }

    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
    document.querySelectorAll('.modal-backdrop').forEach((element) => element.remove());
}

document.addEventListener('DOMContentLoaded', cleanupStaleModalBackdrop);
window.addEventListener('pageshow', cleanupStaleModalBackdrop);

document.addEventListener('submit', function (event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const confirmMessage = form.dataset.confirm;
    if (confirmMessage && !window.confirm(confirmMessage)) {
        event.preventDefault();
        return;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton && !submitButton.disabled) {
        submitButton.dataset.originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Đang xử lý...';
    }
});
</script>
</body>
</html>
