<?php
declare(strict_types=1);

return [
    'host' => env('MAIL_HOST', 'smtp.gmail.com'),
    'port' => (int) env('MAIL_PORT', '587'),
    'username' => env('MAIL_USERNAME', ''),
    'password' => env('MAIL_PASSWORD', ''),
    'from_email' => env('MAIL_FROM_EMAIL', ''),
    'from_name' => env('MAIL_FROM_NAME', 'Barber Spa'),
];