-- =============================================
-- Barber Spa - database/schema.sql
-- PHP thuần + MySQL/MariaDB + XAMPP
-- =============================================

CREATE DATABASE IF NOT EXISTS barber_spa
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE barber_spa;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS review_reports;
DROP TABLE IF EXISTS refunds;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS booking_holds;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS staff_day_off;
DROP TABLE IF EXISTS staff_schedules;
DROP TABLE IF EXISTS staff;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS salon_images;
DROP TABLE IF EXISTS salons;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- TABLE: users
-- =============================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'owner', 'customer') NOT NULL DEFAULT 'customer',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    ban_reason TEXT NULL,
    login_attempts INT NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    last_login_at DATETIME NULL,
    login_ip VARCHAR(45) NULL,
    email_verified_at DATETIME NULL,
    email_token VARCHAR(255) NULL,
    reset_token VARCHAR(255) NULL,
    reset_token_expires DATETIME NULL,
    phone VARCHAR(20) NULL,
    address VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    district VARCHAR(100) NULL,
    avatar VARCHAR(255) NULL,
    remember_token VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active),
    INDEX idx_users_locked_until (locked_until),
    INDEX idx_users_email_token (email_token),
    INDEX idx_users_reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: password_resets
-- =============================================
CREATE TABLE password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_password_resets_email (email),
    INDEX idx_password_resets_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: categories
-- =============================================
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    icon VARCHAR(50) NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,

    UNIQUE KEY uq_categories_name (name),
    INDEX idx_categories_active (is_active),
    INDEX idx_categories_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: salons
-- =============================================
CREATE TABLE salons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id INT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    address VARCHAR(255) NOT NULL,
    district VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    description TEXT NULL,
    search_keywords TEXT NULL,
    open_time TIME NOT NULL DEFAULT '08:00:00',
    close_time TIME NOT NULL DEFAULT '20:00:00',
    working_days VARCHAR(100) NOT NULL DEFAULT '1,2,3,4,5,6',
    avg_rating DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    total_reviews INT NOT NULL DEFAULT 0,
    total_bookings INT NOT NULL DEFAULT 0,
    status ENUM('pending', 'active', 'hidden', 'rejected', 'deleted') NOT NULL DEFAULT 'pending',
    reject_reason TEXT NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_salons_owner
        FOREIGN KEY (owner_id) REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_salons_owner (owner_id),
    INDEX idx_salons_status (status),
    INDEX idx_salons_city (city),
    INDEX idx_salons_district (district),
    INDEX idx_salons_rating (avg_rating),
    INDEX idx_salons_bookings (total_bookings),
    FULLTEXT KEY ft_salons_search (name, description, search_keywords)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: salon_images
-- =============================================
CREATE TABLE salon_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    salon_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,

    CONSTRAINT fk_salon_images_salon
        FOREIGN KEY (salon_id) REFERENCES salons(id)
        ON DELETE CASCADE,

    INDEX idx_salon_images_salon (salon_id),
    INDEX idx_salon_images_primary (is_primary),
    INDEX idx_salon_images_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: services
-- specialties, services list dùng LONGTEXT lưu JSON string
-- để tương thích tốt hơn với MariaDB/XAMPP
-- =============================================
CREATE TABLE services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    salon_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    duration INT NOT NULL COMMENT 'Duration in minutes',
    image VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,

    CONSTRAINT fk_services_salon
        FOREIGN KEY (salon_id) REFERENCES salons(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_services_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT,

    INDEX idx_services_salon (salon_id),
    INDEX idx_services_category (category_id),
    INDEX idx_services_active (is_active),
    INDEX idx_services_price (price),
    INDEX idx_services_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: staff
-- specialties lưu JSON string
-- =============================================
CREATE TABLE staff (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    salon_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NULL,
    avatar VARCHAR(255) NULL,
    specialties LONGTEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,

    CONSTRAINT fk_staff_salon
        FOREIGN KEY (salon_id) REFERENCES salons(id)
        ON DELETE CASCADE,

    INDEX idx_staff_salon (salon_id),
    INDEX idx_staff_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: staff_schedules
-- day_of_week: 0=Sunday ... 6=Saturday
-- =============================================
CREATE TABLE staff_schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id INT UNSIGNED NOT NULL,
    day_of_week TINYINT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_off TINYINT(1) NOT NULL DEFAULT 0,

    CONSTRAINT fk_staff_schedules_staff
        FOREIGN KEY (staff_id) REFERENCES staff(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_staff_day (staff_id, day_of_week),
    INDEX idx_staff_schedules_staff (staff_id),
    INDEX idx_staff_schedules_day (day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: staff_day_off
-- =============================================
CREATE TABLE staff_day_off (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id INT UNSIGNED NOT NULL,
    off_date DATE NOT NULL,
    reason VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_staff_day_off_staff
        FOREIGN KEY (staff_id) REFERENCES staff(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_staff_off_date (staff_id, off_date),
    INDEX idx_staff_day_off_staff (staff_id),
    INDEX idx_staff_day_off_date (off_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: bookings
-- services lưu JSON string
-- =============================================
CREATE TABLE bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    salon_id INT UNSIGNED NOT NULL,
    staff_id INT UNSIGNED NOT NULL,
    services LONGTEXT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    total_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_method ENUM('online', 'at_counter') NOT NULL DEFAULT 'at_counter',
    payment_status ENUM('paid', 'unpaid') NOT NULL DEFAULT 'unpaid',
    notes TEXT NULL,
    cancel_reason TEXT NULL,
    slot_held_until DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_bookings_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_bookings_salon
        FOREIGN KEY (salon_id) REFERENCES salons(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_bookings_staff
        FOREIGN KEY (staff_id) REFERENCES staff(id)
        ON DELETE CASCADE,

    INDEX idx_bookings_user (user_id),
    INDEX idx_bookings_salon (salon_id),
    INDEX idx_bookings_staff (staff_id),
    INDEX idx_bookings_date (booking_date),
    INDEX idx_bookings_status (status),
    INDEX idx_bookings_payment_status (payment_status),
    INDEX idx_bookings_slot (staff_id, booking_date, start_time, end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: booking_holds
-- Temporary slot holds used during booking selection
-- =============================================
CREATE TABLE booking_holds (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    session_id VARCHAR(128) NOT NULL,
    staff_id INT UNSIGNED NOT NULL,
    service_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_booking_holds_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_booking_holds_staff
        FOREIGN KEY (staff_id) REFERENCES staff(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_booking_holds_session_slot (session_id, staff_id, service_date, start_time),
    INDEX idx_booking_holds_staff_slot (staff_id, service_date, start_time, end_time),
    INDEX idx_booking_holds_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: payments
-- gateway_response lưu JSON string
-- =============================================
CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    gateway ENUM('vnpay', 'cash') NOT NULL,
    transaction_id VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'VND',
    status ENUM('pending', 'success', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    gateway_response LONGTEXT NULL,
    paid_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_payments_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_payments_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_payments_transaction_id (transaction_id),
    INDEX idx_payments_booking (booking_id),
    INDEX idx_payments_user (user_id),
    INDEX idx_payments_gateway (gateway),
    INDEX idx_payments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: refunds
-- =============================================
CREATE TABLE refunds (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id INT UNSIGNED NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    reason TEXT NULL,
    status ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',
    refunded_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_refunds_payment
        FOREIGN KEY (payment_id) REFERENCES payments(id)
        ON DELETE CASCADE,

    INDEX idx_refunds_payment (payment_id),
    INDEX idx_refunds_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: reviews
-- images lưu JSON string
-- =============================================
CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    salon_id INT UNSIGNED NOT NULL,
    staff_id INT UNSIGNED NOT NULL,
    rating TINYINT NOT NULL,
    content TEXT NOT NULL,
    images LONGTEXT NULL,
    status ENUM('published', 'flagged', 'removed') NOT NULL DEFAULT 'published',
    owner_reply TEXT NULL,
    owner_replied_at DATETIME NULL,
    report_count INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reviews_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_reviews_salon
        FOREIGN KEY (salon_id) REFERENCES salons(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_reviews_staff
        FOREIGN KEY (staff_id) REFERENCES staff(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_reviews_booking (booking_id),
    INDEX idx_reviews_user (user_id),
    INDEX idx_reviews_salon (salon_id),
    INDEX idx_reviews_staff (staff_id),
    INDEX idx_reviews_status (status),
    INDEX idx_reviews_rating (rating),
    INDEX idx_reviews_report_count (report_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: review_reports
-- =============================================
CREATE TABLE review_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id INT UNSIGNED NOT NULL,
    reporter_id INT UNSIGNED NOT NULL,
    reason ENUM('spam', 'offensive', 'false_info') NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_review_reports_review
        FOREIGN KEY (review_id) REFERENCES reviews(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_review_reports_reporter
        FOREIGN KEY (reporter_id) REFERENCES users(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_review_reporter (review_id, reporter_id),
    INDEX idx_review_reports_review (review_id),
    INDEX idx_review_reports_reporter (reporter_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SEED DATA
-- Password hash cho tất cả account dưới đây là của: Admin@123
-- =============================================

INSERT INTO users (
    id, name, email, password, role, is_active, email_verified_at, phone, address, city, district, created_at, updated_at
) VALUES
(
    1,
    'System Admin',
    'admin@barberspa.vn',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lv6nQ7F9Z7Q0fZ8J9nJ8u',
    'admin',
    1,
    NOW(),
    '0900000001',
    '12 Nguyen Hue',
    'Ho Chi Minh',
    'Quan 1',
    NOW(),
    NOW()
),
(
    2,
    'Owner One',
    'owner1@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lv6nQ7F9Z7Q0fZ8J9nJ8u',
    'owner',
    1,
    NOW(),
    '0900000002',
    '25 Le Loi',
    'Ho Chi Minh',
    'Quan 1',
    NOW(),
    NOW()
),
(
    3,
    'Nguyen Van An',
    'an@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9lv6nQ7F9Z7Q0fZ8J9nJ8u',
    'customer',
    1,
    NOW(),
    '0900000003',
    '48 Tran Hung Dao',
    'Ho Chi Minh',
    'Quan 5',
    NOW(),
    NOW()
);

-- Categories
INSERT INTO categories (id, name, icon, description, sort_order, is_active) VALUES
(1, 'Cắt tóc', '✂', 'Dịch vụ cắt tóc nam nữ', 1, 1),
(2, 'Uốn tóc', '💈', 'Dịch vụ uốn và tạo kiểu tóc', 2, 1),
(3, 'Nhuộm tóc', '🎨', 'Dịch vụ nhuộm và phục hồi màu tóc', 3, 1),
(4, 'Spa & Chăm sóc da', '🌿', 'Chăm sóc da mặt và spa thư giãn', 4, 1),
(5, 'Gội đầu & Massage', '💆', 'Gội đầu dưỡng sinh và massage thư giãn', 5, 1);

-- Salons
INSERT INTO salons (
    id, owner_id, name, address, district, city, phone, description, search_keywords,
    open_time, close_time, working_days, avg_rating, total_reviews, total_bookings,
    status, latitude, longitude, created_at, updated_at
) VALUES
(
    1,
    2,
    'Barber House Quan 1',
    '101 Le Thanh Ton',
    'Quan 1',
    'Ho Chi Minh',
    '0901111111',
    'Salon toc nam cao cap tai trung tam Quan 1, chuyen cat toc, uon, nhuom va cham soc da.',
    'barber, cat toc nam, salon quan 1, fade, uon toc, nhuom toc',
    '08:00:00',
    '20:00:00',
    '1,2,3,4,5,6',
    4.80,
    12,
    85,
    'active',
    10.7765300,
    106.7009800,
    NOW(),
    NOW()
),
(
    2,
    2,
    'Gentleman Barber Quan 3',
    '220 Vo Van Tan',
    'Quan 3',
    'Ho Chi Minh',
    '0902222222',
    'Khong gian barber hien dai, phu hop khach hang tre va dan van phong.',
    'barber quan 3, gentleman, cat toc, goi dau, massage',
    '08:00:00',
    '20:00:00',
    '1,2,3,4,5,6',
    4.60,
    8,
    53,
    'active',
    10.7799000,
    106.6842000,
    NOW(),
    NOW()
),
(
    3,
    2,
    'Luxury Spa Binh Thanh',
    '58 Dien Bien Phu',
    'Binh Thanh',
    'Ho Chi Minh',
    '0903333333',
    'Dich vu cham soc da, massage, goi dau duong sinh va lam dep tong hop.',
    'spa binh thanh, cham soc da, massage, goi dau duong sinh, facial',
    '08:00:00',
    '20:00:00',
    '1,2,3,4,5,6',
    4.90,
    15,
    97,
    'active',
    10.8012000,
    106.7103000,
    NOW(),
    NOW()
);

-- Salon Images
INSERT INTO salon_images (salon_id, image_path, is_primary, sort_order) VALUES
(1, 'public/uploads/salons/salon-1-main.jpg', 1, 1),
(1, 'public/uploads/salons/salon-1-2.jpg', 0, 2),
(2, 'public/uploads/salons/salon-2-main.jpg', 1, 1),
(2, 'public/uploads/salons/salon-2-2.jpg', 0, 2),
(3, 'public/uploads/salons/salon-3-main.jpg', 1, 1),
(3, 'public/uploads/salons/salon-3-2.jpg', 0, 2);

-- Services salon 1
INSERT INTO services (salon_id, category_id, name, description, price, duration, image, is_active, sort_order) VALUES
(1, 1, 'Cat toc Classic', 'Cat toc co dien gon gang, phu hop dan van phong.', 120000, 45, 'public/uploads/services/s1-classic.jpg', 1, 1),
(1, 1, 'Cat toc Fade', 'Cat toc fade hien dai, xu huong tre trung.', 150000, 60, 'public/uploads/services/s1-fade.jpg', 1, 2),
(1, 2, 'Uon texture nam', 'Uon texture nhe tao volume tu nhien.', 450000, 120, 'public/uploads/services/s1-uon.jpg', 1, 3),
(1, 3, 'Nhuom mau thoi trang', 'Nhuom cac tong mau phu hop phong cach ca tinh.', 550000, 150, 'public/uploads/services/s1-nhuom.jpg', 1, 4),
(1, 5, 'Goi dau massage', 'Goi dau sach da dau ket hop massage thu gian.', 100000, 30, 'public/uploads/services/s1-goi.jpg', 1, 5),
(1, 4, 'Cham soc da co ban', 'Lam sach sau va duong am da mat.', 250000, 60, 'public/uploads/services/s1-da.jpg', 1, 6);

-- Services salon 2
INSERT INTO services (salon_id, category_id, name, description, price, duration, image, is_active, sort_order) VALUES
(2, 1, 'Cat toc Gentleman', 'Cat toc lich lam phong cach gentleman.', 130000, 45, 'public/uploads/services/s2-gentleman.jpg', 1, 1),
(2, 1, 'Cat toc Modern Pompadour', 'Tao kieu pompadour hien dai.', 170000, 60, 'public/uploads/services/s2-pompadour.jpg', 1, 2),
(2, 2, 'Uon layer', 'Uon layer nhe giup toc bong va vao nep.', 420000, 120, 'public/uploads/services/s2-uon.jpg', 1, 3),
(2, 3, 'Nhuom nau lanh', 'Nhuom tong nau lanh thanh lich.', 480000, 140, 'public/uploads/services/s2-nhuom.jpg', 1, 4),
(2, 5, 'Goi dau thu gian', 'Goi dau va massage vai co gay.', 90000, 30, 'public/uploads/services/s2-goi.jpg', 1, 5),
(2, 4, 'Detox da dau', 'Lam sach da dau va duong nang toc.', 220000, 50, 'public/uploads/services/s2-detox.jpg', 1, 6);

-- Services salon 3
INSERT INTO services (salon_id, category_id, name, description, price, duration, image, is_active, sort_order) VALUES
(3, 4, 'Facial co ban', 'Cham soc da mat co ban, lam sach va duong am.', 300000, 60, 'public/uploads/services/s3-facial-basic.jpg', 1, 1),
(3, 4, 'Facial chuyen sau', 'Cham soc da chuyen sau, hut mun, phuc hoi da.', 550000, 90, 'public/uploads/services/s3-facial-deep.jpg', 1, 2),
(3, 5, 'Goi dau duong sinh', 'Goi dau ket hop massage thu gian.', 180000, 45, 'public/uploads/services/s3-goi.jpg', 1, 3),
(3, 5, 'Massage co vai gay', 'Massage giam moi, giai toa cang thang.', 250000, 60, 'public/uploads/services/s3-massage.jpg', 1, 4),
(3, 3, 'Nhuom toc phu bac', 'Nhuom phu bac tu nhien va ben mau.', 350000, 90, 'public/uploads/services/s3-phubac.jpg', 1, 5),
(3, 2, 'Uon phuc hoi', 'Uon toc ket hop duong chat phuc hoi.', 500000, 140, 'public/uploads/services/s3-uon.jpg', 1, 6);

-- Staff
INSERT INTO staff (id, salon_id, name, phone, avatar, specialties, is_active) VALUES
(1, 1, 'Tran Minh Barber', '0911000001', 'public/uploads/avatars/staff-1.jpg', '["fade","classic","texture"]', 1),
(2, 1, 'Le Quang Stylist', '0911000002', 'public/uploads/avatars/staff-2.jpg', '["nhuom","uon","cham soc da"]', 1),
(3, 1, 'Pham Anh Spa', '0911000003', 'public/uploads/avatars/staff-3.jpg', '["massage","goi dau","facial"]', 1),

(4, 2, 'Nguyen Hoang', '0912000001', 'public/uploads/avatars/staff-4.jpg', '["gentleman","pompadour"]', 1),
(5, 2, 'Do Thanh', '0912000002', 'public/uploads/avatars/staff-5.jpg', '["uon","nhuom"]', 1),
(6, 2, 'Vo Tuan', '0912000003', 'public/uploads/avatars/staff-6.jpg', '["goi dau","massage"]', 1),

(7, 3, 'Mai Linh Spa', '0913000001', 'public/uploads/avatars/staff-7.jpg', '["facial","cham soc da"]', 1),
(8, 3, 'Bao Ngoc', '0913000002', 'public/uploads/avatars/staff-8.jpg', '["goi dau","massage"]', 1),
(9, 3, 'Minh Thu', '0913000003', 'public/uploads/avatars/staff-9.jpg', '["uon","nhuom","phu bac"]', 1);

-- Staff schedules: T2-T7 = 1..6, 08:00-20:00
INSERT INTO staff_schedules (staff_id, day_of_week, start_time, end_time, is_off) VALUES
(1, 1, '08:00:00', '20:00:00', 0),
(1, 2, '08:00:00', '20:00:00', 0),
(1, 3, '08:00:00', '20:00:00', 0),
(1, 4, '08:00:00', '20:00:00', 0),
(1, 5, '08:00:00', '20:00:00', 0),
(1, 6, '08:00:00', '20:00:00', 0),

(2, 1, '08:00:00', '20:00:00', 0),
(2, 2, '08:00:00', '20:00:00', 0),
(2, 3, '08:00:00', '20:00:00', 0),
(2, 4, '08:00:00', '20:00:00', 0),
(2, 5, '08:00:00', '20:00:00', 0),
(2, 6, '08:00:00', '20:00:00', 0),

(3, 1, '08:00:00', '20:00:00', 0),
(3, 2, '08:00:00', '20:00:00', 0),
(3, 3, '08:00:00', '20:00:00', 0),
(3, 4, '08:00:00', '20:00:00', 0),
(3, 5, '08:00:00', '20:00:00', 0),
(3, 6, '08:00:00', '20:00:00', 0),

(4, 1, '08:00:00', '20:00:00', 0),
(4, 2, '08:00:00', '20:00:00', 0),
(4, 3, '08:00:00', '20:00:00', 0),
(4, 4, '08:00:00', '20:00:00', 0),
(4, 5, '08:00:00', '20:00:00', 0),
(4, 6, '08:00:00', '20:00:00', 0),

(5, 1, '08:00:00', '20:00:00', 0),
(5, 2, '08:00:00', '20:00:00', 0),
(5, 3, '08:00:00', '20:00:00', 0),
(5, 4, '08:00:00', '20:00:00', 0),
(5, 5, '08:00:00', '20:00:00', 0),
(5, 6, '08:00:00', '20:00:00', 0),

(6, 1, '08:00:00', '20:00:00', 0),
(6, 2, '08:00:00', '20:00:00', 0),
(6, 3, '08:00:00', '20:00:00', 0),
(6, 4, '08:00:00', '20:00:00', 0),
(6, 5, '08:00:00', '20:00:00', 0),
(6, 6, '08:00:00', '20:00:00', 0),

(7, 1, '08:00:00', '20:00:00', 0),
(7, 2, '08:00:00', '20:00:00', 0),
(7, 3, '08:00:00', '20:00:00', 0),
(7, 4, '08:00:00', '20:00:00', 0),
(7, 5, '08:00:00', '20:00:00', 0),
(7, 6, '08:00:00', '20:00:00', 0),

(8, 1, '08:00:00', '20:00:00', 0),
(8, 2, '08:00:00', '20:00:00', 0),
(8, 3, '08:00:00', '20:00:00', 0),
(8, 4, '08:00:00', '20:00:00', 0),
(8, 5, '08:00:00', '20:00:00', 0),
(8, 6, '08:00:00', '20:00:00', 0),

(9, 1, '08:00:00', '20:00:00', 0),
(9, 2, '08:00:00', '20:00:00', 0),
(9, 3, '08:00:00', '20:00:00', 0),
(9, 4, '08:00:00', '20:00:00', 0),
(9, 5, '08:00:00', '20:00:00', 0),
(9, 6, '08:00:00', '20:00:00', 0);

-- Sample staff day off
INSERT INTO staff_day_off (staff_id, off_date, reason, created_at) VALUES
(2, DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'Nghi viec rieng', NOW()),
(8, DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Dao tao noi bo', NOW());

-- Sample bookings
INSERT INTO bookings (
    id, user_id, salon_id, staff_id, services, booking_date, start_time, end_time,
    total_price, status, payment_method, payment_status, notes, slot_held_until,
    created_at, updated_at
) VALUES
(
    1,
    3,
    1,
    1,
    '[{"service_id":1,"name":"Cat toc Classic","price":120000,"duration":45}]',
    DATE_ADD(CURDATE(), INTERVAL 2 DAY),
    '09:00:00',
    '09:45:00',
    120000,
    'confirmed',
    'at_counter',
    'unpaid',
    'Cat gon gang',
    NULL,
    NOW(),
    NOW()
),
(
    2,
    3,
    3,
    7,
    '[{"service_id":13,"name":"Facial co ban","price":300000,"duration":60}]',
    DATE_SUB(CURDATE(), INTERVAL 3 DAY),
    '15:00:00',
    '16:00:00',
    300000,
    'completed',
    'online',
    'paid',
    'Da su dung dich vu',
    NULL,
    NOW(),
    NOW()
);

-- Sample payments
INSERT INTO payments (
    booking_id, user_id, gateway, transaction_id, amount, currency, status, gateway_response, paid_at, created_at
) VALUES
(
    2,
    3,
    'vnpay',
    'VNPAY_SAMPLE_0001',
    300000,
    'VND',
    'success',
    '{"vnp_TxnRef":"BOOKING_2","vnp_ResponseCode":"00"}',
    NOW(),
    NOW()
);

-- Sample refunds
INSERT INTO refunds (
    payment_id, amount, reason, status, refunded_at, created_at
) VALUES
(
    1,
    50000,
    'Hoan mot phan de demo du lieu',
    'pending',
    NULL,
    NOW()
);

-- Sample reviews
INSERT INTO reviews (
    booking_id, user_id, salon_id, staff_id, rating, content, images, status,
    owner_reply, owner_replied_at, report_count, created_at, updated_at
) VALUES
(
    2,
    3,
    3,
    7,
    5,
    'Dich vu tot, nhan vien nhiet tinh, khong gian sach se va thu gian.',
    '["public/uploads/reviews/review-1.jpg"]',
    'published',
    'Cam on ban da tin tuong va ung ho salon.',
    NOW(),
    0,
    NOW(),
    NOW()
);

-- Đồng bộ lại total_reviews / avg_rating cơ bản
UPDATE salons s
LEFT JOIN (
    SELECT salon_id,
           COUNT(*) AS total_reviews_calc,
           ROUND(AVG(rating), 2) AS avg_rating_calc
    FROM reviews
    WHERE status = 'published'
    GROUP BY salon_id
) r ON s.id = r.salon_id
SET
    s.total_reviews = IFNULL(r.total_reviews_calc, 0),
    s.avg_rating = IFNULL(r.avg_rating_calc, 0.00);

UPDATE salons s
LEFT JOIN (
    SELECT salon_id, COUNT(*) AS total_bookings_calc
    FROM bookings
    GROUP BY salon_id
) b ON s.id = b.salon_id
SET
    s.total_bookings = IFNULL(b.total_bookings_calc, 0);
