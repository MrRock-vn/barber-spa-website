-- Thêm 4 salon mới vào dữ liệu
-- Password cho owner đều là: Owner@123 (hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lv6nQ7F9Z7Q0fZ8J9nJ8u)

-- Tạo tài khoản owner mới (nếu chưa có)
INSERT INTO users (
    name, email, password, role, is_active, email_verified_at, phone, address, city, district, created_at, updated_at
) VALUES
(
    'Owner Two',
    'owner2@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lv6nQ7F9Z7Q0fZ8J9nJ8u',
    'owner',
    1,
    NOW(),
    '0900000004',
    '30 Nguyen Dinh Chieu',
    'Ho Chi Minh',
    'Quan 1',
    NOW(),
    NOW()
),
(
    'Owner Three',
    'owner3@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lv6nQ7F9Z7Q0fZ8J9nJ8u',
    'owner',
    1,
    NOW(),
    '0900000005',
    '42 Mac Dinh Chi',
    'Ho Chi Minh',
    'Quan 1',
    NOW(),
    NOW()
),
(
    'Owner Four',
    'owner4@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lv6nQ7F9Z7Q0fZ8J9nJ8u',
    'owner',
    1,
    NOW(),
    '0900000006',
    '99 Tran Hung Dao',
    'Ho Chi Minh',
    'Quan 5',
    NOW(),
    NOW()
);

-- Thêm 4 salon mới
INSERT INTO salons (
    owner_id, name, address, district, city, phone, description, search_keywords,
    open_time, close_time, working_days, avg_rating, total_reviews, total_bookings,
    status, latitude, longitude, created_at, updated_at
) VALUES
(
    (SELECT id FROM users WHERE email = 'owner2@gmail.com'),
    'Premium Hair Studio Tan Binh',
    '128 Truong Son',
    'Tan Binh',
    'Ho Chi Minh',
    '0904444444',
    'Studio toc hang A voi cac chi duong dau tay, cat toc, duong dom, lot mau cao cap.',
    'hair studio, tan binh, duong toc, lot mau, cat toc nam',
    '09:00:00',
    '21:00:00',
    '1,2,3,4,5,6,7',
    4.75,
    18,
    102,
    'active',
    10.8100000,
    106.6820000,
    NOW(),
    NOW()
),
(
    (SELECT id FROM users WHERE email = 'owner3@gmail.com'),
    'Beauty Palace Go Vap',
    '456 Pham The Hien',
    'Go Vap',
    'Ho Chi Minh',
    '0905555555',
    'Trung tam lam dep toan dien: spa, massage, nail, toc, tham my, skincare.',
    'spa go vap, lam dep, massage, nail, skincare, thanh my',
    '08:00:00',
    '22:00:00',
    '1,2,3,4,5,6,7',
    4.85,
    22,
    156,
    'active',
    10.8500000,
    106.6500000,
    NOW(),
    NOW()
),
(
    (SELECT id FROM users WHERE email = 'owner4@gmail.com'),
    'Modern Barbershop District 5',
    '789 Vo Van Kiet',
    'Quan 5',
    'Ho Chi Minh',
    '0906666666',
    'Barbershop hien dai voi dich vu cat toc fade, uon, nhuom va shaving tay.',
    'barber quan 5, cat toc fade, uon toc, nhuom, shaving',
    '07:30:00',
    '20:30:00',
    '1,2,3,4,5,6',
    4.70,
    14,
    89,
    'active',
    10.7600000,
    106.6900000,
    NOW(),
    NOW()
),
(
    2,
    'Aesthetic Clinic District 1',
    '222 Calmette',
    'Quan 1',
    'Ho Chi Minh',
    '0900001111',
    'Phong kham lam dep voi cac dich vu: botox, filler, laser, co dan trang sang da.',
    'aesthetic clinic, botox, filler, laser, trang da, co dan',
    '09:00:00',
    '19:00:00',
    '2,3,4,5,6',
    4.95,
    28,
    147,
    'active',
    10.7820000,
    106.7050000,
    NOW(),
    NOW()
);
