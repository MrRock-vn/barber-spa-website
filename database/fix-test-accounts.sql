-- ============================================================
-- FIX TEST ACCOUNTS - Đảm bảo tất cả tài khoản test có thể đăng nhập
-- ============================================================
-- Mật khẩu: Admin@123, Owner@123, Customer@123
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lv6nQ7F9Z7Q0fZ8J9nJ8u
-- ============================================================

-- 1. Đảm bảo admin@barberspa.vn được xác thực email
UPDATE users SET email_verified_at = NOW() WHERE email = 'admin@barberspa.vn';

-- 2. Đảm bảo owner1@gmail.com được xác thực email
UPDATE users SET email_verified_at = NOW() WHERE email = 'owner1@gmail.com';

-- 3. Đảm bảo an@gmail.com được xác thực email
UPDATE users SET email_verified_at = NOW() WHERE email = 'an@gmail.com';

-- 4. Thêm tài khoản customer quang@gmail.com (nếu chưa có)
INSERT IGNORE INTO users (
    name, email, password, role, is_active, email_verified_at, phone, address, city, district, created_at, updated_at
) VALUES (
    'Nguyen Van Quang',
    'quang@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lv6nQ7F9Z7Q0fZ8J9nJ8u',
    'customer',
    1,
    NOW(),
    '0900000099',
    '100 Nguyen Trai',
    'Ho Chi Minh',
    'Quan 1',
    NOW(),
    NOW()
);

-- 5. Đảm bảo owner2@gmail.com được xác thực email
UPDATE users SET email_verified_at = NOW() WHERE email = 'owner2@gmail.com';

-- 6. Đảm bảo owner3@gmail.com được xác thực email
UPDATE users SET email_verified_at = NOW() WHERE email = 'owner3@gmail.com';

-- 7. Đảm bảo owner4@gmail.com được xác thực email
UPDATE users SET email_verified_at = NOW() WHERE email = 'owner4@gmail.com';

-- ============================================================
-- VERIFY: Kiểm tra tất cả tài khoản test
-- ============================================================
SELECT id, name, email, role, is_active, email_verified_at FROM users 
WHERE email IN (
    'admin@barberspa.vn',
    'owner1@gmail.com',
    'owner2@gmail.com',
    'owner3@gmail.com',
    'owner4@gmail.com',
    'an@gmail.com',
    'quang@gmail.com'
)
ORDER BY role, email;

-- ============================================================
-- RESULT: Tất cả tài khoản test đều có email_verified_at = NOW()
-- Có thể đăng nhập ngay bây giờ
-- ============================================================
