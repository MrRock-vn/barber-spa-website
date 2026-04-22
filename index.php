<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/helpers.php';

// SECURITY: Handle remember token auto login BEFORE Auth::start()
$rememberToken = $_COOKIE['remember_token'] ?? '';
if ($rememberToken && !isset($_SESSION['user_id'])) {
    // Validate token format: must be 64 hex characters
    if (preg_match('/^[a-f0-9]{64}$/', $rememberToken)) {
        require_once __DIR__ . '/models/User.php';
        $userModel = new User();
        $user = $userModel->findByRememberToken($rememberToken);
        
        if ($user && (int) $user['is_active'] === 1 && !empty($user['email_verified_at'])) {
            // Set session before Auth::start()
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_role'] = $user['role'];
        } else {
            // Invalid token - remove cookie
            setcookie('remember_token', '', time() - 3600, '/');
        }
    } else {
        // Malformed token - remove cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }
}

// Initialize session
Auth::start();

$path = trim($_GET['path'] ?? '', '/');
if ($path === '') {
    $path = 'home';
}

$routes = [
    'home'                   => 'controllers/SearchController.php@home',
    'search'                 => 'controllers/SearchController.php@search',
    'salon/(\d+)'            => 'controllers/SearchController.php@salonDetail',

    'login'                  => 'controllers/AuthController.php@login',
    'register'               => 'controllers/AuthController.php@register',
    'logout'                 => 'controllers/AuthController.php@logout',
    'forgot-password'        => 'controllers/AuthController.php@forgotPassword',
    'reset-password'         => 'controllers/AuthController.php@resetPassword',
    'verify-email'           => 'controllers/AuthController.php@verifyEmail',

    'booking/create'         => 'controllers/BookingController.php@create',
    'booking/(\d+)'          => 'controllers/BookingController.php@show',
    'booking'                => 'controllers/BookingController.php@create',
    'my-bookings'            => 'controllers/BookingController.php@myBookings',
    'cancel-booking'         => 'controllers/BookingController.php@cancel',

    'payment'                => 'controllers/PaymentController.php@index',
    'payment/confirm'        => 'controllers/PaymentController.php@confirm',
    'payment/vnpay'          => 'controllers/PaymentController.php@vnpay',
    'payment/vnpay-return'   => 'controllers/PaymentController.php@vnpayReturn',
    'payment/vnpay-ipn'      => 'controllers/PaymentController.php@vnpayIpn',

    'write-review'           => 'controllers/ReviewController.php@create',
    'edit-review/(\d+)'      => 'controllers/ReviewController.php@edit',
    'delete-review/(\d+)'    => 'controllers/ReviewController.php@delete',
    'report-review/(\d+)'    => 'controllers/ReviewController.php@report',

    'my-profile'             => 'controllers/UserController.php@profile',
    'edit-profile'           => 'controllers/UserController.php@editProfile',

    'owner/salon/create'     => 'controllers/owner/SalonController.php@create',
    'owner/staff/schedule'   => 'controllers/owner/StaffController.php@schedule',
    'owner/dashboard'        => 'controllers/owner/DashboardController.php@index',
    'owner/salon'            => 'controllers/owner/SalonController.php@edit',
    'owner/services'         => 'controllers/owner/ServiceController.php@index',
    'owner/staff'            => 'controllers/owner/StaffController.php@index',
    'owner/bookings'         => 'controllers/owner/BookingController.php@index',
    'owner/reviews'          => 'controllers/owner/ReviewController.php@index',
    'owner/revenue'          => 'controllers/owner/RevenueController.php@index',

    'admin/dashboard'        => 'controllers/admin/DashboardController.php@index',
    'admin/users'            => 'controllers/admin/UserController.php@index',
    'admin/salons'           => 'controllers/admin/SalonController.php@index',
    'admin/categories'       => 'controllers/admin/CategoryController.php@index',
    'admin/bookings'         => 'controllers/admin/BookingController.php@index',
    'admin/reviews'          => 'controllers/admin/ReviewController.php@index',
];

$matched = false;

foreach ($routes as $pattern => $handler) {
    $regex = '#^' . $pattern . '$#';

    if (preg_match($regex, $path, $matches)) {
        $matched = true;
        $params = array_slice($matches, 1);

        [$file, $method] = explode('@', $handler);
        require_once __DIR__ . '/' . $file;

        $className = basename($file, '.php');
        $controller = new $className();

        call_user_func_array([$controller, $method], $params);
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    require_once __DIR__ . '/views/errors/404.php';
}
