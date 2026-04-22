<?php
// ============================================================================
// config/auth-config.php - Cấu hình xác thực (Standalone version)
// Người làm: Nguyễn Văn Quang
// ============================================================================

// Cấu hình session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Lax');

// Cấu hình bảo mật
define('AUTH_CONFIG', [
    // Session settings
    'session_name' => 'BARBER_SPA_SESSION',
    'session_lifetime' => 7200, // 2 hours
    
    // Password settings
    'password_min_length' => 8,
    'password_require_uppercase' => true,
    'password_require_lowercase' => true,
    'password_require_numbers' => true,
    'password_require_special' => false,
    
    // Login attempt settings
    'max_login_attempts' => 5,
    'lockout_duration' => 1800, // 30 minutes
    
    // Remember me settings
    'remember_me_duration' => 604800, // 7 days
    'remember_token_length' => 64,
    
    // Email verification
    'email_verification_required' => true,
    'email_token_expiry' => 86400, // 24 hours
    
    // Password reset
    'reset_token_expiry' => 900, // 15 minutes
    'reset_token_length' => 64,
    
    // Rate limiting
    'rate_limit_requests' => 10,
    'rate_limit_window' => 300, // 5 minutes
]);

/**
 * Kiểm tra mật khẩu mạnh
 */
function isStrongPassword($password) {
    $config = AUTH_CONFIG;
    
    // Kiểm tra độ dài tối thiểu
    if (strlen($password) < $config['password_min_length']) {
        return false;
    }
    
    // Kiểm tra chữ hoa
    if ($config['password_require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    // Kiểm tra chữ thường
    if ($config['password_require_lowercase'] && !preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    // Kiểm tra số
    if ($config['password_require_numbers'] && !preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    // Kiểm tra ký tự đặc biệt
    if ($config['password_require_special'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
        return false;
    }
    
    return true;
}

/**
 * Kiểm tra email hợp lệ
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Tạo token ngẫu nhiên
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Hash mật khẩu
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify mật khẩu
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Kiểm tra rate limiting
 */
function checkRateLimit($identifier, $db) {
    $config = AUTH_CONFIG;
    $window_start = date('Y-m-d H:i:s', time() - $config['rate_limit_window']);
    
    $stmt = $db->prepare('
        SELECT COUNT(*) as attempts 
        FROM login_attempts 
        WHERE identifier = ? AND attempted_at > ?
    ');
    $stmt->execute([$identifier, $window_start]);
    $result = $stmt->fetch();
    
    return $result['attempts'] < $config['rate_limit_requests'];
}

/**
 * Ghi log login attempt
 */
function logLoginAttempt($identifier, $success, $db) {
    $stmt = $db->prepare('
        INSERT INTO login_attempts (identifier, success, attempted_at, ip_address) 
        VALUES (?, ?, NOW(), ?)
    ');
    $stmt->execute([
        $identifier, 
        $success ? 1 : 0, 
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
}

/**
 * Làm sạch old login attempts
 */
function cleanupLoginAttempts($db) {
    $config = AUTH_CONFIG;
    $cleanup_time = date('Y-m-d H:i:s', time() - ($config['rate_limit_window'] * 2));
    
    $stmt = $db->prepare('DELETE FROM login_attempts WHERE attempted_at < ?');
    $stmt->execute([$cleanup_time]);
}

/**
 * Kiểm tra user có bị khóa không
 */
function isUserLocked($user) {
    if (empty($user['locked_until'])) {
        return false;
    }
    
    return strtotime($user['locked_until']) > time();
}

/**
 * Khóa user
 */
function lockUser($userId, $db) {
    $config = AUTH_CONFIG;
    $locked_until = date('Y-m-d H:i:s', time() + $config['lockout_duration']);
    
    $stmt = $db->prepare('
        UPDATE users 
        SET locked_until = ?, login_attempts = 0 
        WHERE id = ?
    ');
    $stmt->execute([$locked_until, $userId]);
}

/**
 * Reset login attempts
 */
function resetLoginAttempts($userId, $db) {
    $stmt = $db->prepare('
        UPDATE users 
        SET login_attempts = 0, locked_until = NULL 
        WHERE id = ?
    ');
    $stmt->execute([$userId]);
}

/**
 * Tăng login attempts
 */
function incrementLoginAttempts($userId, $db) {
    $config = AUTH_CONFIG;
    
    $stmt = $db->prepare('
        UPDATE users 
        SET login_attempts = login_attempts + 1 
        WHERE id = ?
    ');
    $stmt->execute([$userId]);
    
    // Kiểm tra có cần khóa user không
    $stmt = $db->prepare('SELECT login_attempts FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user && $user['login_attempts'] >= $config['max_login_attempts']) {
        lockUser($userId, $db);
    }
}

/**
 * Tạo remember token
 */
function createRememberToken($userId, $db) {
    $config = AUTH_CONFIG;
    $token = generateSecureToken($config['remember_token_length'] / 2);
    $expires_at = date('Y-m-d H:i:s', time() + $config['remember_me_duration']);
    
    // Xóa token cũ
    $stmt = $db->prepare('DELETE FROM remember_tokens WHERE user_id = ?');
    $stmt->execute([$userId]);
    
    // Tạo token mới
    $stmt = $db->prepare('
        INSERT INTO remember_tokens (user_id, token, expires_at) 
        VALUES (?, ?, ?)
    ');
    $stmt->execute([$userId, $token, $expires_at]);
    
    return $token;
}

/**
 * Xóa remember token
 */
function deleteRememberToken($userId, $db) {
    $stmt = $db->prepare('DELETE FROM remember_tokens WHERE user_id = ?');
    $stmt->execute([$userId]);
}

/**
 * Validate remember token
 */
function validateRememberToken($token, $db) {
    $stmt = $db->prepare('
        SELECT u.* 
        FROM users u 
        JOIN remember_tokens rt ON rt.user_id = u.id 
        WHERE rt.token = ? AND rt.expires_at > NOW() AND u.is_active = 1
    ');
    $stmt->execute([$token]);
    return $stmt->fetch();
}

/**
 * Làm sạch expired tokens
 */
function cleanupExpiredTokens($db) {
    // Cleanup remember tokens
    $stmt = $db->prepare('DELETE FROM remember_tokens WHERE expires_at < NOW()');
    $stmt->execute();
    
    // Cleanup reset tokens
    $stmt = $db->prepare('
        UPDATE users 
        SET reset_token = NULL, reset_token_expires = NULL 
        WHERE reset_token_expires < NOW()
    ');
    $stmt->execute();
    
    // Cleanup email tokens (older than 24 hours)
    $stmt = $db->prepare('
        UPDATE users 
        SET email_token = NULL 
        WHERE email_token IS NOT NULL 
        AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        AND email_verified_at IS NULL
    ');
    $stmt->execute();
}

/**
 * Escape HTML output
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Flash message helper
 */
function flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get and clear flash message
 */
function getFlash($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * CSRF Token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateSecureToken(16);
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF Input HTML
 */
function csrfInput() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

// Auto cleanup on include
if (function_exists('getDB')) {
    try {
        $db = getDB();
        cleanupExpiredTokens($db);
        cleanupLoginAttempts($db);
    } catch (Exception $e) {
        // Silent fail for cleanup
    }
}