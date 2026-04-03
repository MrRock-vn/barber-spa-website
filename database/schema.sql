-- ============================================================
-- BARBER & SPA — DATABASE SCHEMA
-- File: database/schema.sql
-- Người tạo: Nguyễn Văn Danh (BOOK-01)
-- Chạy file này TRƯỚC KHI các thành viên khác bắt đầu code
-- ============================================================

CREATE DATABASE IF NOT EXISTS barber_spa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barber_spa;

-- -------------------------------------------------------
-- BẢNG 1: users  (Quang dùng cho AUTH-01)
-- -------------------------------------------------------
CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- bcrypt hash
    role        ENUM('admin','owner','customer') NOT NULL DEFAULT 'customer',
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    login_attempts  INT NOT NULL DEFAULT 0,
    locked_until    DATETIME NULL,
    last_login_at   DATETIME NULL,
    login_ip        VARCHAR(45) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- BẢNG 2: categories  (Admin dùng, Sơn dùng để hiển thị)
-- -------------------------------------------------------
CREATE TABLE categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    icon        VARCHAR(100) NULL,
    description TEXT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1
);

-- -------------------------------------------------------
-- BẢNG 3: salons  (Sơn dùng cho SEARCH-01)
-- -------------------------------------------------------
CREATE TABLE salons (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id        INT UNSIGNED NOT NULL,
    name            VARCHAR(200) NOT NULL,
    address         VARCHAR(500) NOT NULL,
    district        VARCHAR(100) NULL,
    city            VARCHAR(100) NULL,
    phone           VARCHAR(20) NULL,
    description     TEXT NULL,
    open_time       TIME NOT NULL DEFAULT '08:00:00',
    close_time      TIME NOT NULL DEFAULT '20:00:00',
    working_days    VARCHAR(20) NOT NULL DEFAULT '1,2,3,4,5,6',  -- 0=CN,1=T2...6=T7
    avg_rating      DECIMAL(3,1) NOT NULL DEFAULT 0.0,
    total_reviews   INT NOT NULL DEFAULT 0,
    total_bookings  INT NOT NULL DEFAULT 0,
    status          ENUM('pending','active','hidden','rejected') NOT NULL DEFAULT 'pending',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id)
);

-- -------------------------------------------------------
-- BẢNG 4: salon_images
-- -------------------------------------------------------
CREATE TABLE salon_images (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    salon_id    INT UNSIGNED NOT NULL,
    image_path  VARCHAR(500) NOT NULL,
    is_primary  TINYINT(1) NOT NULL DEFAULT 0,
    sort_order  INT NOT NULL DEFAULT 0,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- BẢNG 5: services  (Sơn hiển thị, Danh dùng cho booking)
-- -------------------------------------------------------
CREATE TABLE services (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    salon_id    INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NULL,
    name        VARCHAR(200) NOT NULL,
    description TEXT NULL,
    price       DECIMAL(10,0) NOT NULL,
    duration    INT NOT NULL,               -- phút
    image       VARCHAR(500) NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    sort_order  INT NOT NULL DEFAULT 0,
    FOREIGN KEY (salon_id)    REFERENCES salons(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- -------------------------------------------------------
-- BẢNG 6: staff  (Danh dùng cho BOOK-01)
-- -------------------------------------------------------
CREATE TABLE staff (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    salon_id    INT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    phone       VARCHAR(20) NULL,
    avatar      VARCHAR(500) NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- BẢNG 7: staff_schedules  (Danh dùng cho BOOK-01)
-- -------------------------------------------------------
CREATE TABLE staff_schedules (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_id    INT UNSIGNED NOT NULL,
    day_of_week TINYINT NOT NULL,           -- 0=CN, 1=T2 ... 6=T7
    start_time  TIME NOT NULL,
    end_time    TIME NOT NULL,
    is_off      TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- BẢNG 8: bookings  (Danh dùng cho BOOK-01)
-- -------------------------------------------------------
CREATE TABLE bookings (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    salon_id        INT UNSIGNED NOT NULL,
    staff_id        INT UNSIGNED NULL,
    services        JSON NOT NULL,          -- [{id,name,price,duration}]
    booking_date    DATE NOT NULL,
    start_time      TIME NOT NULL,
    end_time        TIME NOT NULL,          -- tự tính = start + sum(duration)
    total_price     DECIMAL(10,0) NOT NULL,
    status          ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    payment_method  ENUM('online','at_counter') NOT NULL DEFAULT 'at_counter',
    payment_status  ENUM('paid','unpaid') NOT NULL DEFAULT 'unpaid',
    notes           TEXT NULL,
    slot_held_until DATETIME NULL,          -- giữ slot 10 phút
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id),
    FOREIGN KEY (salon_id) REFERENCES salons(id),
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE SET NULL
);

-- ============================================================
-- SEED DỮ LIỆU MẪU
-- ============================================================

-- Admin account (password: Admin@123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@barberspa.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Owner account (password: Owner@123)
INSERT INTO users (name, email, password, role) VALUES
('Trần Minh Tuấn', 'owner1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner'),
('Lê Thị Hoa',     'owner2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner');

-- Customer account (password: User@1234)
INSERT INTO users (name, email, password, role) VALUES
('Nguyễn Văn An',  'an@gmail.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Phạm Thị Bình',  'binh@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- Categories
INSERT INTO categories (name, icon, sort_order) VALUES
('Cắt tóc', 'scissors', 1),
('Uốn tóc', 'curl', 2),
('Nhuộm tóc', 'paint', 3),
('Spa & Chăm sóc da', 'leaf', 4),
('Gội đầu & Massage', 'droplet', 5);

-- Salons
INSERT INTO salons (owner_id, name, address, district, city, phone, description, avg_rating, total_reviews, total_bookings, status) VALUES
(2, 'Barber Shop 30Shine Hoàn Kiếm', '45 Hàng Bông, Hoàn Kiếm', 'Hoàn Kiếm', 'Hà Nội', '0901234567', 'Tiệm cắt tóc nam phong cách hiện đại, phục vụ tận tâm.', 4.8, 120, 350, 'active'),
(3, 'Hair Salon Tóc Đẹp Cầu Giấy',   '12 Trần Thái Tông, Cầu Giấy', 'Cầu Giấy', 'Hà Nội', '0912345678', 'Chuyên uốn, nhuộm, phục hồi tóc hư tổn.', 4.5, 80, 210, 'active'),
(2, 'Royal Spa & Barber Đống Đa',     '88 Tây Sơn, Đống Đa', 'Đống Đa', 'Hà Nội', '0923456789', 'Không gian sang trọng, dịch vụ spa và cắt tóc cao cấp.', 4.7, 95, 180, 'active');

-- Salon images
INSERT INTO salon_images (salon_id, image_path, is_primary) VALUES
(1, 'https://placehold.co/800x500/1a1a2e/ffffff?text=30Shine', 1),
(2, 'https://placehold.co/800x500/16213e/ffffff?text=Toc+Dep', 1),
(3, 'https://placehold.co/800x500/0f3460/ffffff?text=Royal+Spa', 1);

-- Services
INSERT INTO services (salon_id, category_id, name, price, duration) VALUES
(1, 1, 'Cắt tóc nam kiểu Hàn', 120000, 45),
(1, 1, 'Cắt + Gội đầu', 150000, 60),
(1, 5, 'Gội đầu + Massage', 80000, 30),
(2, 2, 'Uốn tóc Hàn Quốc', 350000, 120),
(2, 3, 'Nhuộm màu thời trang', 400000, 150),
(2, 1, 'Cắt tóc nữ', 150000, 60),
(3, 4, 'Chăm sóc da mặt cơ bản', 250000, 60),
(3, 1, 'Cắt tóc + Tạo kiểu', 200000, 75);

-- Staff
INSERT INTO staff (salon_id, name, phone) VALUES
(1, 'Anh Hùng', '0901111111'),
(1, 'Anh Khoa',  '0902222222'),
(2, 'Chị Lan',  '0903333333'),
(2, 'Chị Mai',  '0904444444'),
(3, 'Anh Phong', '0905555555');

-- Staff schedules (T2-T7, 8:00-20:00)
INSERT INTO staff_schedules (staff_id, day_of_week, start_time, end_time) VALUES
(1,1,'08:00','20:00'),(1,2,'08:00','20:00'),(1,3,'08:00','20:00'),
(1,4,'08:00','20:00'),(1,5,'08:00','20:00'),(1,6,'08:00','20:00'),
(2,1,'08:00','20:00'),(2,2,'08:00','20:00'),(2,3,'08:00','20:00'),
(2,4,'08:00','20:00'),(2,5,'08:00','20:00'),(2,6,'08:00','20:00'),
(3,1,'09:00','19:00'),(3,2,'09:00','19:00'),(3,3,'09:00','19:00'),
(3,4,'09:00','19:00'),(3,5,'09:00','19:00'),(3,6,'09:00','19:00'),
(4,1,'09:00','19:00'),(4,2,'09:00','19:00'),(4,3,'09:00','19:00'),
(4,4,'09:00','19:00'),(4,5,'09:00','19:00'),(4,6,'09:00','19:00'),
(5,1,'08:00','21:00'),(5,2,'08:00','21:00'),(5,3,'08:00','21:00'),
(5,4,'08:00','21:00'),(5,5,'08:00','21:00'),(5,6,'08:00','21:00');
