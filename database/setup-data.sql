-- ============================================================
-- BARBER SPA - SETUP DATA
-- Thêm dữ liệu mở rộng: 4 salon mới + 3 owner mới + 9 dịch vụ
-- ============================================================
-- Password cho tất cả owner: Owner@123
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lv6nQ7F9Z7Q0fZ8J9nJ8u

-- ============================================================
-- 1. Tạo tài khoản owner mới (nếu chưa có)
-- ============================================================
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

-- ============================================================
-- 2. Thêm 4 salon mới
-- ============================================================
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

-- ============================================================
-- 3. Thêm 9 dịch vụ cho các salon mới
-- ============================================================

-- Services salon 4: Premium Hair Studio Tan Binh
INSERT INTO services (salon_id, category_id, name, description, price, duration, image, is_active, sort_order) VALUES
(4, 1, 'Cat toc Undercut Taper', 'Undercut taper hien dai voi chi tiet chi ti.', 200000, 60, 'public/uploads/services/s4-undercut.jpg', 1, 1),
(4, 3, 'Nhuom toc Ash', 'Nhuom tong ash ban chay, sang bong va tre trung.', 650000, 180, 'public/uploads/services/s4-ash.jpg', 1, 2),
(4, 5, 'Goi dau SPA cao cap', 'Goi dau ket hop tinh dau thom va massage thu gian.', 150000, 45, 'public/uploads/services/s4-goispa.jpg', 1, 3);

-- Services salon 5: Beauty Palace Go Vap
INSERT INTO services (salon_id, category_id, name, description, price, duration, image, is_active, sort_order) VALUES
(5, 5, 'Massage lung toan bo cao cap', 'Massage toan bo lung voi các dầu thơm chuyên dụng.', 350000, 90, 'public/uploads/services/s5-massage.jpg', 1, 1),
(5, 4, 'Facial toan dien premium', 'Cham soc da toan dien voi my pham co bao hanh.', 450000, 120, 'public/uploads/services/s5-facial.jpg', 1, 2),
(5, 1, 'Nhuom toc fashion & Uon', 'Nhuom toc mau thoi trang ket hop uon layer.', 700000, 180, 'public/uploads/services/s5-nhuom-uon.jpg', 1, 3);

-- Services salon 6: Modern Barbershop District 5
INSERT INTO services (salon_id, category_id, name, description, price, duration, image, is_active, sort_order) VALUES
(6, 1, 'Cat toc Undercut nam', 'Undercut co dien voi chi tiet sach sao.', 180000, 50, 'public/uploads/services/s6-undercut.jpg', 1, 1),
(6, 2, 'Uon toc nam hien dai', 'Uon toc nam voi hieu ung tu nhien.', 350000, 90, 'public/uploads/services/s6-uon.jpg', 1, 2),
(6, 5, 'Goi dau Barber Grooming', 'Goi dau chuyen sau cho nam voi massage da dau.', 120000, 40, 'public/uploads/services/s6-goi.jpg', 1, 3);

-- ============================================================
-- END OF SETUP DATA
-- ============================================================
-- Ghi chú:
-- - Các owner mới có mật khẩu: Owner@123
-- - Các salon có trạng thái 'active' - sẵn sàng đặt lịch
-- - Các dịch vụ được sắp xếp theo thứ tự (sort_order)
-- - Để thay đổi dữ liệu, sửa file này rồi import lại
-- ============================================================
