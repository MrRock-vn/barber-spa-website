<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

function hasFlash(string $key): bool
{
    return isset($_SESSION['_flash'][$key]);
}

function getFlash(string $key): ?string
{
    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $message = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return $message;
}

function csrfToken(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrfInput(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrfToken()) . '">';
}

function verifyCsrf(): bool
{
    $token = $_POST['_token'] ?? '';
    $sessionToken = $_SESSION['_csrf_token'] ?? '';

    return is_string($token)
        && is_string($sessionToken)
        && hash_equals($sessionToken, $token);
}

function paginate(int $total, int $perPage, int $page): array
{
    $perPage = max(1, $perPage);
    $page = max(1, $page);
    $totalPages = (int) ceil($total / $perPage);
    $totalPages = max(1, $totalPages);

    if ($page > $totalPages) {
        $page = $totalPages;
    }

    return [
        'total' => $total,
        'perPage' => $perPage,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'offset' => ($page - 1) * $perPage,
        'limit' => $perPage,
    ];
}

function formatMoney(float $amount): string
{
    return number_format($amount, 0, ',', '.') . 'đ';
}

function formatDate(?string $date): string
{
    if (empty($date)) {
        return '';
    }

    return date('d/m/Y', strtotime($date));
}

function formatTime(?string $time): string
{
    if (empty($time)) {
        return '';
    }

    return date('H:i', strtotime($time));
}

function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidPhone(string $phone): bool
{
    return preg_match('/^0[0-9]{9}$/', $phone) === 1;
}

function isStrongPassword(string $password): bool
{
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password) === 1;
}

function loadEnv(string $filePath = __DIR__ . '/../.env'): void
{
    if (!file_exists($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Remove quotes if present
        if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
            (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
            $value = substr($value, 1, -1);
        }

        // Set environment variable
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}

function env(string $key, ?string $default = null): ?string
{
    return $_ENV[$key] ?? \getenv($key) ?: $default;
}

function render(string $view, array $data = [], string $layout = 'app'): void
{
    extract($data, EXTR_SKIP);

    ob_start();
    require __DIR__ . '/../views/' . $view . '.php';
    $content = ob_get_clean();

    require __DIR__ . '/../views/layouts/' . $layout . '.php';
}