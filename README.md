# 💈 Website Đặt Lịch Cắt Tóc & Làm Đẹp (Barber & Spa)

> **Đề tài thực tập tốt nghiệp** — Lớp LTWNC-D18CNPM2

Hệ thống website cho phép khách hàng tìm kiếm salon/barber, đặt lịch hẹn, viết đánh giá và thanh toán trực tuyến. Chủ salon quản lý dịch vụ, nhân viên và lịch hẹn. Admin giám sát toàn bộ hệ thống.

---

## 👥 Thành viên nhóm

| STT | Họ và tên | MSSV | Vai trò |
|---|---|---|---|
| 1 | Nguyễn Công Sơn | 23810310102 | Nhóm trưởng |
| 2 | Nguyễn Văn Quang | 23810310108 | Thành viên |
| 3 | Nguyễn Văn Danh | 23810310136 | Thành viên |

---

## 🚀 Công nghệ sử dụng

| Thành phần | Công nghệ |
|---|---|
| Frontend | HTML, CSS, JavaScript, Bootstrap 5 |
| Backend | PHP thuần  |
| Database | MySQL |
| Thanh toán | VNPay / ZaloPay / Momo |

---

## 📋 Tài liệu Đặc tả Yêu cầu Phần mềm (SRS)

Tất cả tài liệu SRS được lưu trong thư mục [`/docs/srs/`](./docs/srs/).

| Mã | Chức năng | Tài liệu | Trạng thái |
|---|---|---|---|
| AUTH-01 | 🔐 Xác thực Người dùng (Đăng nhập / Đăng ký / Quên mật khẩu) | [SRS_AUTH.md](./docs/srs/SRS_AUTH.md) | ✅ 95% |
| SEARCH-01 | 🔍 Tìm kiếm & Khám phá Salon | [SRS_SEARCH.md](./docs/srs/SRS_SEARCH.md) | ✅ 90% |
| BOOK-01 | 📅 Đặt lịch hẹn (Booking) | [SRS_BOOKING.md](./docs/srs/SRS_BOOKING.md) | ✅ 85% |
| REVIEW-01 | ⭐ Đánh giá & Review | [SRS_REVIEW.md](./docs/srs/SRS_REVIEW.md) | 🔄 50% |
| PAY-01 | 💳 Thanh toán trực tuyến (VNPay / ZaloPay / Momo) | [SRS_PAYMENT.md](./docs/srs/SRS_PAYMENT.md) | ⏳ 0% |
| SALON-01 | 💈 Quản lý Salon (Chủ Salon) | [SRS_SALON_MANAGEMENT.md](./docs/srs/SRS_SALON_MANAGEMENT.md) | ⏳ 0% |
| ADMIN-01 | 🛡️ Quản trị Hệ thống (Admin) | [SRS_ADMIN.md](./docs/srs/SRS_ADMIN.md) | ⏳ 0% |

---

## 🗂️ Cấu trúc thư mục dự án

```
barber-spa-website/
├── config/
│   └── db.php                 # Kết nối MySQL + Helper functions
├── database/
│   └── schema.sql             # Database schema (11 bảng)
├── docs/
│   └── srs/                   # Tài liệu yêu cầu chi tiết
├── includes/
│   └── navbar.php             # Navigation bar
├── public/
│   ├── index.php              # Trang chủ
│   ├── login.php              # Đăng nhập
│   ├── register.php           # Đăng ký
│   ├── logout.php             # Đăng xuất
│   ├── search.php             # Tìm kiếm salon
│   ├── salon-detail.php       # Chi tiết salon
│   ├── booking.php            # Đặt lịch (4-step wizard)
│   ├── booking-success.php    # Xác nhận đặt lịch
│   ├── my-bookings.php        # Lịch hẹn của tôi
│   ├── write-review.php       # Viết đánh giá
│   ├── forgot-password.php    # Quên mật khẩu
│   └── css/
│       └── style.css          # Stylesheet chính
├── README.md                  # File này
└── DECUONG.md                 # Đề cương thực tập
```

---

## � Tiến độ dự án

**Hoàn thành: 55%**

### ✅ Hoàn thành
- **AUTH-01** (95%): Đăng nhập, đăng ký, đăng xuất, quên mật khẩu
- **SEARCH-01** (90%): Tìm kiếm salon, lọc theo khu vực, xem chi tiết
- **BOOK-01** (85%): Đặt lịch 4 bước, chọn dịch vụ, nhân viên, ngày/giờ
- **Salon Detail**: Hiển thị thông tin salon, dịch vụ, nhân viên, gallery

### 🔄 Đang làm
- **REVIEW-01** (50%): Form viết đánh giá, lưu vào database, cập nhật rating

### ⏳ Chưa làm
- **PAY-01**: Tích hợp VNPay, ZaloPay, Momo
- **SALON-01**: Dashboard chủ salon
- **ADMIN-01**: Dashboard admin

---

## ⚙️ Hướng dẫn cài đặt & Chạy Code

### **Yêu cầu:**
- XAMPP (Apache + MySQL + PHP 8.0+)
- Git
- Browser (Chrome, Firefox, Edge)

### **Bước 1: Cài đặt XAMPP**
- Tải từ: https://www.apachefriends.org/download.html
- Cài đặt vào `C:\xampp` (Windows)
- Khởi động Apache & MySQL

### **Bước 2: Clone Code**
```bash
cd C:\xampp\htdocs
git clone https://github.com/<username>/barber-spa-website.git
cd barber-spa-website
```

### **Bước 3: Tạo Database**
1. Mở: http://localhost/phpmyadmin
2. Tạo database: `barber_spa`
3. Import file: `database/schema.sql`

### **Bước 4: Cấu hình Database**
Mở `config/db.php` và cập nhật (nếu cần):
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'barber_spa');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### **Bước 5: Chạy Website**
```
http://localhost/barber-spa-website/public/
```

✅ **Xong!** Website đã chạy!

### **Tài khoản Test:**
- **Admin:** admin@barberspa.vn / Admin@123
- **Owner:** owner1@gmail.com / Owner@123
- **Customer:** an@gmail.com / User@1234

---

## 🔧 Công nghệ Backend

### MySQLi Helper Functions (config/db.php)
```php
escape($str)           // Escape string để tránh SQL injection
fetchAll($sql)         // Lấy nhiều dòng
fetchOne($sql)         // Lấy 1 dòng
execute($sql)          // INSERT/UPDATE/DELETE
lastInsertId()         // Lấy ID vừa insert
currentUser()          // Lấy user đang đăng nhập
requireLogin()         // Yêu cầu đăng nhập
```

### Ví dụ sử dụng:
```php
// Lấy tất cả salon
$salons = fetchAll("SELECT * FROM salons WHERE status = 'active'");

// Lấy 1 salon
$salon = fetchOne("SELECT * FROM salons WHERE id = 1");

// Thêm booking
$userId = currentUser()['id'];
execute("INSERT INTO bookings (user_id, salon_id, ...) VALUES ($userId, 1, ...)");
$bookingId = lastInsertId();
```

---

## 📝 Ghi chú

- Tài liệu SRS được soạn thảo theo chuẩn IEEE 830
- Mọi thay đổi yêu cầu phải cập nhật file SRS tương ứng
- Database schema có 11 bảng: users, categories, salons, salon_images, services, staff, staff_schedules, bookings, reviews, payments, refunds
- Sử dụng PHP thuần (MySQLi) thay vì Laravel/PDO

---

*Hà Nội, tháng 04 năm 2026*
