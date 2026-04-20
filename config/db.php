<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/helpers.php';

loadEnv(__DIR__ . '/../.env');

date_default_timezone_set('Asia/Ho_Chi_Minh');

define('APP_NAME', 'Barber Spa');
define('BASE_URL', env('APP_URL', 'http://localhost/barber-spa'));

define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'barber_spa'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');