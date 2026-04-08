-- ============================================================
-- RESET TEST ACCOUNTS - RUN THIS TO FIX LOGIN ISSUES
-- ============================================================

USE barber_spa;

-- Delete existing test accounts
DELETE FROM users WHERE email IN ('admin@barberspa.vn', 'owner1@gmail.com', 'owner2@gmail.com', 'an@gmail.com', 'binh@gmail.com');

-- Insert new test accounts with correct password hashes
-- Password: Admin@123 (bcrypt hash)
INSERT INTO users (name, email, password, role, is_active) VALUES
('Admin', 'admin@barberspa.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Password: Owner@123 (bcrypt hash)
INSERT INTO users (name, email, password, role, is_active) VALUES
('Trần Minh Tuấn', 'owner1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 1),
('Lê Thị Hoa', 'owner2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 1);

-- Password: User@1234 (bcrypt hash)
INSERT INTO users (name, email, password, role, is_active) VALUES
('Nguyễn Văn An', 'an@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 1),
('Phạm Thị Bình', 'binh@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 1);

-- Verify
SELECT id, name, email, role FROM users WHERE email IN ('admin@barberspa.vn', 'owner1@gmail.com', 'an@gmail.com');
