# 💈 Barber Spa - Website Đặt Lịch Salon / Barber Trực Tuyến

Đồ án / bài tập lớn Lập trình Web  
Xây dựng hệ thống website cho phép khách hàng tìm kiếm salon, đặt lịch dịch vụ, thanh toán, đánh giá và quản lý lịch hẹn trực tuyến.

## 👥 Thành viên nhóm

| STT | Họ và tên          | MSSV          | Vai trò     |
| --- | ------------------ | ------------- | ----------- |
| 1   | [Nguyễn Công Sơn]  | [23810310102] | Nhóm trưởng |
| 2   | [Nguyễn Văn Quang] | [23810310108] | Thành viên  |
| 3   | [Nguyễn Văn Danh]  | [23810310136] | Thành viên  |

## 🎯 Giới thiệu

Barber Spa là nền tảng đặt lịch salon/barber trực tuyến, kết nối khách hàng với các salon có dịch vụ cắt tóc, gội đầu, massage, chăm sóc da và làm đẹp. Hệ thống hỗ trợ 3 vai trò chính:

- **Khách hàng (Customer):** tìm kiếm salon, xem dịch vụ, đặt lịch, thanh toán, theo dõi booking, viết review.
- **Chủ salon (Owner):** quản lý salon, nhân viên, dịch vụ, lịch hẹn, doanh thu và review của salon mình.
- **Quản trị viên (Admin):** quản lý người dùng, salon, danh mục, booking, payment, review và dashboard toàn hệ thống.

## 🚀 Công nghệ sử dụng

| Thành phần | Công nghệ                             |
| ---------- | ------------------------------------- |
| Frontend   | HTML5, CSS3, JavaScript, Bootstrap 5  |
| Backend    | PHP 8 thuần theo mô hình MVC          |
| Database   | MySQL / MariaDB                       |
| Web server | Apache (XAMPP)                        |
| UI / Chart | Bootstrap 5 CDN, Chart.js             |
| Auth       | PHP Sessions, bcrypt password hashing |
| Payment    | VNPay Sandbox                         |
| Mail       | PHPMailer / SMTP                      |

## 📋 Tài liệu Đặc tả Yêu cầu Phần mềm (SRS)

Các tài liệu SRS được lưu trong thư mục [`/docs/srs/`](./docs/srs/)

| Mã        | Chức năng                 | Tài liệu                                                      | Trạng thái |
| --------- | ------------------------- | ------------------------------------------------------------- | ---------- |
| AUTH-01   | Xác thực người dùng       | [SRS_AUTH.md](./docs/srs/SRS_AUTH.md)                         | ✅         |
| SEARCH-01 | Tìm kiếm & khám phá salon | [SRS_SEARCH.md](./docs/srs/SRS_SEARCH.md)                     | ✅         |
| BOOK-01   | Đặt lịch hẹn              | [SRS_BOOKING.md](./docs/srs/SRS_BOOKING.md)                   | ✅         |
| PAY-01    | Thanh toán                | [SRS_PAYMENT.md](./docs/srs/SRS_PAYMENT.md)                   | ✅         |
| SALON-01  | Quản lý salon             | [SRS_SALON_MANAGEMENT.md](./docs/srs/SRS_SALON_MANAGEMENT.md) | ✅         |
| REVIEW-01 | Đánh giá & review         | [SRS_REVIEW.md](./docs/srs/SRS_REVIEW.md)                     | ✅         |
| ADMIN-01  | Quản trị hệ thống         | [SRS_ADMIN.md](./docs/srs/SRS_ADMIN.md)                       | ✅         |

## 🗂️ Cấu trúc thư mục dự án

```text
barber-spa/
├── api/
│   ├── autocomplete.php
│   ├── get-slots.php
│   ├── hold-slot.php
│   └── payment/
│       ├── vnpay-config.php
│       ├── vnpay-redirect.php
│       └── vnpay-return.php
├── config/
│   ├── db.php
│   ├── mail.php
│   └── vnpay.php
├── controllers/
│   ├── admin/
│   │   ├── BookingController.php
│   │   ├── CategoryController.php
│   │   ├── DashboardController.php
│   │   ├── ReviewController.php
│   │   ├── SalonController.php
│   │   └── UserController.php
│   ├── owner/
│   │   ├── BookingController.php
│   │   ├── DashboardController.php
│   │   ├── ReviewController.php
│   │   ├── RevenueController.php
│   │   ├── SalonController.php
│   │   ├── ServiceController.php
│   │   └── StaffController.php
│   ├── AuthController.php
│   ├── BookingController.php
│   ├── PaymentController.php
│   ├── ReviewController.php
│   ├── SearchController.php
│   └── UserController.php
├── core/
│   ├── Auth.php
│   ├── Database.php
│   ├── Mailer.php
│   └── helpers.php
├── database/
│   ├── add-booking-holds.sql
│   ├── schema.sql
│   └── setup-data.sql
├── docs/
│   ├── PROJECT_REPORT.md
│   ├── VIVA_QA.md
│   └── srs/
├── models/
│   ├── Booking.php
│   ├── Category.php
│   ├── Payment.php
│   ├── Review.php
│   ├── Salon.php
│   ├── Service.php
│   ├── Staff.php
│   └── User.php
├── public/
│   └── css/
│       └── style.css
├── views/
│   ├── admin/
│   ├── auth/
│   ├── booking/
│   ├── layouts/
│   ├── owner/
│   ├── payment/
│   ├── review/
│   ├── search/
│   └── user/
├── .env.example
├── .htaccess
├── composer.json
├── DEMO_SCRIPT.md
├── INSTALL.md
├── SECURITY.md
├── index.php
└── README.md
```

## ✨ Chức năng chính của hệ thống

### 1. Khách hàng (Customer)

- Đăng ký, đăng nhập, đăng xuất.
- Quên mật khẩu / đặt lại mật khẩu.
- Quản lý hồ sơ cá nhân.
- Tìm kiếm salon theo từ khóa, khu vực, dịch vụ.
- Autocomplete gợi ý salon / dịch vụ theo thời gian thực.
- Xem chi tiết salon, dịch vụ, nhân viên, rating và review.
- Đặt lịch theo 4 bước:
  - Chọn dịch vụ.
  - Chọn nhân viên.
  - Chọn ngày giờ.
  - Xác nhận booking.
- Hệ thống giữ slot tạm 10 phút khi chọn giờ.
- Không cho đặt lịch quá khứ, trùng slot hoặc staff không thuộc salon.
- Thanh toán online qua VNPay sandbox hoặc thanh toán tại quầy.
- Xem My Bookings, tìm kiếm/filter booking theo trạng thái.
- Hủy lịch khi còn hợp lệ.
- Viết/sửa/xóa review sau khi booking completed.
- Report review vi phạm.

### 2. Chủ salon (Owner)

- Dashboard tổng quan: booking, doanh thu, nhân viên, dịch vụ, rating, review.
- Biểu đồ booking 7 ngày gần nhất.
- Biểu đồ doanh thu 6 tháng.
- Xem khung giờ đông khách, nhân viên được đặt nhiều, dịch vụ được chọn nhiều.
- Quản lý booking: xác nhận, hoàn thành, hủy.
- Quản lý dịch vụ.
- Quản lý nhân viên, lịch làm việc và ngày nghỉ.
- Xem review salon mình.
- Phản hồi review của khách hàng.
- Xem doanh thu theo khoảng thời gian.

### 3. Quản trị viên (Admin)

- Dashboard tổng quan hệ thống.
- Quản lý users: khóa/mở tài khoản.
- Quản lý salons: duyệt, ẩn, mở lại, xóa mềm.
- Quản lý categories.
- Quản lý bookings.
- Quản lý reviews: publish, flag, remove.
- Xem thống kê:
  - tổng user, salon, booking, revenue
  - payment success
  - tổng review / flagged review
  - top salon nhiều booking
  - top dịch vụ được đặt nhiều
  - biểu đồ booking và doanh thu

## ⭐ Tính năng nổi bật

- **Hold slot 10 phút:** tránh hai khách đặt cùng một khung giờ.
- **Staff schedule/day off:** chỉ hiển thị slot hợp lệ theo lịch làm việc nhân viên.
- **Review moderation:** report review, owner reply, admin publish/flag/remove.
- **Verified review:** review gắn với booking đã completed.
- **Dashboard thật:** dùng dữ liệu MySQL và Chart.js.
- **Autocomplete API:** gợi ý salon/dịch vụ realtime.
- **Payment guard:** không cho thanh toán lại booking đã paid.

## ⚙️ Hướng dẫn cài đặt và chạy dự án

### 1️⃣ Yêu cầu môi trường

- XAMPP hoặc Apache + MySQL riêng biệt.
- PHP 8.0+.
- MySQL 8.0+ / MariaDB 10.x+.
- Composer.
- Trình duyệt web: Chrome, Edge, Firefox.
- phpMyAdmin nếu dùng XAMPP.

### 2️⃣ Đưa project vào htdocs

Clone hoặc copy dự án vào:

```text
C:\xampp\htdocs\barber-spa
```

Hoặc theo môi trường hiện tại:

```text
c:\aiu\htdocs\barber-spa
```

### 3️⃣ Khởi động dịch vụ

Mở XAMPP Control Panel và bật:

- ✅ Apache
- ✅ MySQL

### 4️⃣ Cài dependency

```bash
composer install
```

### 5️⃣ Tạo database & import dữ liệu

#### Cách 1: phpMyAdmin

1. Truy cập:

```text
http://localhost/phpmyadmin
```

2. Tạo database:

```text
barber_spa
```

Collation:

```text
utf8mb4_unicode_ci
```

3. Chọn database vừa tạo.
4. Tab Import → chọn file:

```text
database/schema.sql
hoặc chọn thêm các database nữa trong bảng database để chạy dữ liệu
```

5. Bấm Import.

#### Cách 2: MySQL CLI

```sql
CREATE DATABASE barber_spa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barber_spa;
SOURCE database/schema.sql;
```

Nếu database cũ chưa có bảng giữ slot:

```sql
SOURCE database/add-booking-holds.sql;
```

### 6️⃣ Cấu hình `.env`

Copy `.env.example` thành `.env` và kiểm tra:

```env
APP_URL=http://localhost/barber-spa
DB_HOST=localhost
DB_PORT=3306
DB_NAME=barber_spa
DB_USER=root
DB_PASS=
```

Nếu dùng VNPay sandbox, cập nhật thêm các biến gateway trong `.env`.

### 7️⃣ Khởi chạy ứng dụng

Mở trình duyệt:

```text
http://localhost/barber-spa
```

Nếu thấy 404, kiểm tra:

- Apache đã start chưa?
- Project có đúng trong `htdocs/barber-spa` không?
- `.htaccess` có tồn tại không?
- `APP_URL` có đúng không?

## 🔐 Tài khoản test

| Vai trò  | Email                     | Mật khẩu       | Ghi chú            |
| -------- | ------------------------- | -------------- | ------------------ |
| Admin    | `admin@barberspa.vn`      | `Admin@123`    | Quản trị hệ thống  |
| Owner    | `owner1@gmail.com`        | `Owner@123`    | Chủ salon          |
| Customer | `an@gmail.com`            | `Customer@123` | Khách hàng test    |
| Customer | `damtrungson00@gmail.com` | `Anhhd@12345`  | test quên mật khẩu |

⚠️ Nếu đăng nhập sai quá 5 lần, tài khoản sẽ bị khóa tạm 30 phút.

## 🧪 Hướng dẫn test hoàn chỉnh

### Test Customer Flow

1. Vào trang chủ:

```text
http://localhost/barber-spa
```

2. Tìm kiếm salon:

- Gõ `bar` vào ô tìm kiếm.
- Kiểm tra dropdown autocomplete.
- Chọn salon hoặc bấm tìm kiếm.

3. Xem chi tiết salon:

- Xem thông tin salon.
- Xem dịch vụ.
- Xem nhân viên.
- Xem rating và review.
- Lọc review theo số sao.

4. Đăng nhập customer:

```text
Email: an@gmail.com
Password: Customer@123
```

5. Đặt lịch:

- Chọn dịch vụ.
- Chọn nhân viên.
- Chọn ngày từ ngày mai trở đi.
- Chọn giờ.
- Hệ thống giữ slot 10 phút.
- Xác nhận booking.

6. Thanh toán:

- Nếu chọn online: thanh toán VNPay sandbox.
- Nếu chọn tại quầy: xác nhận thanh toán tại quầy.

7. My Bookings:

- Xem danh sách booking.
- Lọc theo trạng thái.
- Tìm theo mã booking / salon / nhân viên.
- Hủy booking nếu còn hợp lệ.

8. Review:

- Với booking đã completed, bấm viết đánh giá.
- Gửi rating 1-5 sao.
- Sửa hoặc xóa review của chính mình.
- Report review của người khác.

### Test Owner Flow

1. Đăng nhập owner:

```text
Email: owner1@gmail.com
Password: Owner@123
```

2. Vào Owner Dashboard:

- Xem tổng booking, doanh thu, staff, service, rating, review.
- Xem biểu đồ booking 7 ngày.
- Xem biểu đồ doanh thu 6 tháng.
- Xem khung giờ đông khách.
- Xem nhân viên/dịch vụ được đặt nhiều.

3. Quản lý Booking:

- Vào Owner Bookings.
- Xác nhận booking.
- Hoàn thành booking.
- Hủy booking nếu cần.

4. Quản lý Staff:

- Thêm/sửa/xóa nhân viên.
- Cập nhật lịch làm việc.
- Thêm ngày nghỉ.

5. Quản lý Service:

- Thêm/sửa/xóa dịch vụ.
- Bật/tắt trạng thái dịch vụ.

6. Quản lý Review:

- Vào Owner Reviews.
- Xem review của salon.
- Phản hồi review.

### Test Admin Flow

1. Đăng nhập admin:

```text
Email: admin@barberspa.vn
Password: Admin@123
```

2. Vào Admin Dashboard:

- Xem tổng users, salons, bookings, revenue.
- Xem payment success.
- Xem review / flagged review.
- Xem biểu đồ booking và doanh thu.
- Xem top salon và top dịch vụ.

3. Quản lý Users:

- Xem danh sách user.
- Khóa / mở khóa tài khoản.

4. Quản lý Salons:

- Duyệt salon.
- Ẩn salon.
- Mở lại salon.
- Xóa mềm salon.

5. Quản lý Categories:

- Thêm/sửa/xóa danh mục.

6. Quản lý Bookings:

- Xác nhận booking.
- Hoàn thành booking.
- Hủy booking.

7. Quản lý Reviews:

- Publish review.
- Flag review.
- Remove review vi phạm.

## 🐛 Troubleshooting

### Lỗi: Cannot connect to database

- Kiểm tra MySQL đã start.
- Kiểm tra `.env`: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
- Kiểm tra database `barber_spa` đã tạo.
- Kiểm tra đã import `database/schema.sql`.

### Lỗi: 404 Not Found

- Kiểm tra Apache đã start.
- Kiểm tra URL đúng:

```text
http://localhost/barber-spa
```

- Kiểm tra project nằm đúng trong `htdocs/barber-spa`.
- Kiểm tra `.htaccess` tồn tại.

### Lỗi: không có slot giờ hẹn

- Chọn ngày từ ngày mai trở đi.
- Kiểm tra nhân viên có lịch làm việc trong ngày đó.
- Kiểm tra nhân viên không bị day off.
- Kiểm tra slot chưa bị booking hoặc hold.
- Nếu test nhiều lần, xóa hold hết hạn trong bảng `booking_holds`.

### Lỗi: không đăng nhập được

- Dùng đúng tài khoản test trong README.
- Kiểm tra `email_verified_at` không bị null.
- Nếu nhập sai quá 5 lần, tài khoản bị khóa tạm 30 phút.
- Có thể reset bằng admin panel hoặc cập nhật `login_attempts = 0`, `locked_until = NULL`.

### Lỗi: thanh toán sandbox

- Kiểm tra `.env` có cấu hình VNPay sandbox.
- Kiểm tra return URL đúng `APP_URL`.
- Không thanh toán lại booking đã `paid`.
- Booking `cancelled` không được thanh toán.

### Lỗi: session không hoạt động

- Kiểm tra browser có bật cookie.
- Clear cache/cookie.
- Kiểm tra `session.save_path` trong `php.ini`.

## 📝 Changelog

## 📸 Ảnh chụp màn hình đề xuất khi nộp/demo

### Customer

- Trang chủ với autocomplete.
- Trang kết quả tìm kiếm salon.
- Trang chi tiết salon.
- Booking step 1-4.
- Payment page.
- My Bookings.
- Form viết review.

### Owner

- Owner Dashboard.
- Owner Bookings.
- Owner Staff Schedule.
- Owner Services.
- Owner Reviews.

### Admin

- Admin Dashboard.
- Admin Users.
- Admin Salons.
- Admin Bookings.
- Admin Reviews.

## 🧪 Checklist kiểm thử nhanh

### Customer flow

- ✅ Đăng ký tài khoản mới.
- ✅ Đăng nhập.
- ✅ Quên mật khẩu.
- ✅ Tìm kiếm salon.
- ✅ Autocomplete salon/service.
- ✅ Xem chi tiết salon.
- ✅ Đặt lịch.
- ✅ Giữ slot 10 phút.
- ✅ Thanh toán.
- ✅ Xem My Bookings.
- ✅ Hủy lịch nếu hợp lệ.
- ✅ Viết/sửa/xóa review.
- ✅ Report review.

### Owner flow

- ✅ Đăng nhập owner.
- ✅ Xem dashboard.
- ✅ Quản lý booking.
- ✅ Quản lý service.
- ✅ Quản lý staff.
- ✅ Quản lý schedule/day off.
- ✅ Xem và phản hồi review.
- ✅ Xem doanh thu.

### Admin flow

- ✅ Đăng nhập admin.
- ✅ Xem dashboard tổng quan.
- ✅ Quản lý user.
- ✅ Quản lý salon.
- ✅ Quản lý category.
- ✅ Quản lý booking.
- ✅ Kiểm duyệt review.

## 📄 Tài liệu bổ sung

- [INSTALL.md](INSTALL.md)
- [SECURITY.md](SECURITY.md)
- [DEMO_SCRIPT.md](DEMO_SCRIPT.md)
- [docs/PROJECT_REPORT.md](docs/PROJECT_REPORT.md)
- [docs/VIVA_QA.md](docs/VIVA_QA.md)

## 📄 License

Dự án này được phát triển cho mục đích học tập và nghiên cứu.

## 🙏 Acknowledgments

- Bootstrap for UI framework.
- Chart.js for dashboard charts.
- Unsplash / Picsum / Pravatar for demo placeholder images.
- PHP documentation and community examples.
  # Giao diện chính
  <img width="1915" height="969" alt="image" src="https://github.com/user-attachments/assets/fd22bc86-8f0a-43fb-be6c-e37911c8b81b" />
  <img width="1567" height="805" alt="image" src="https://github.com/user-attachments/assets/69ddfdcf-21b7-4b50-b6e1-85366f89ec7c" />
  <img width="1919" height="974" alt="image" src="https://github.com/user-attachments/assets/dbe36291-d29d-4a8f-b196-41e2b06f6ce8" />
  <img width="1914" height="994" alt="image" src="https://github.com/user-attachments/assets/4187925c-166d-48f1-90fb-2f9bd070315c" />
  <img width="1920" height="965" alt="image" src="https://github.com/user-attachments/assets/8cf9fb5a-ec3f-42e0-8f87-dc6bfa997b73" />
  <img width="1831" height="947" alt="image" src="https://github.com/user-attachments/assets/ea8570a8-a22e-4c2e-9931-44d775e49cbe" />
  <img width="1900" height="937" alt="image" src="https://github.com/user-attachments/assets/d3eca6aa-865e-495e-b982-c89c5d561846" />
  <img width="1919" height="925" alt="image" src="https://github.com/user-attachments/assets/b4d62b06-bbe3-422e-aa25-410880f34398" />
  <img width="1917" height="924" alt="image" src="https://github.com/user-attachments/assets/646700bf-ae6e-465d-8db9-ab1ec0db2ec3" />


## 📺 Video Demo
- **Link Video:** https://drive.google.com/drive/folders/15EbfeE3ydUdFkIWciAJl4U6_bNz2mmBt

## 🌐 Link Online (Deploy)
- **Website:** [https://sonokela.online/](https://sonokela.online/)









Hà Nội, tháng 04 năm 2026.
