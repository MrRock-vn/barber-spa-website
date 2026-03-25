# 💈 Website Đặt Lịch Cắt Tóc & Làm Đẹp (Barber & Spa)

> **Đề tài thực tập tốt nghiệp** — Lớp LTWNC-D18CNPM2

Hệ thống website cho phép khách hàng tìm kiếm salon/barber, đặt lịch hẹn và thanh toán trực tuyến. Chủ salon quản lý dịch vụ, nhân viên và lịch hẹn. Admin giám sát toàn bộ hệ thống.

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
| Backend | PHP thuần / Laravel |
| Database | MySQL |
| Thanh toán | VNPay / ZaloPay / Momo |

---

## 📋 Tài liệu Đặc tả Yêu cầu Phần mềm (SRS)

Tất cả tài liệu SRS được lưu trong thư mục [`/docs/srs/`](./docs/srs/).

| Mã | Chức năng | Tài liệu | Trạng thái |
|---|---|---|---|
| AUTH-01 | 🔐 Xác thực Người dùng (Đăng nhập / Đăng ký / Quên mật khẩu) | [SRS_AUTH.md](./docs/srs/SRS_AUTH.md) | ✅ Hoàn thiện |
| SEARCH-01 | 🔍 Tìm kiếm & Khám phá Salon | [SRS_SEARCH.md](./docs/srs/SRS_SEARCH.md) | ✅ Hoàn thiện |
| BOOK-01 | 📅 Đặt lịch hẹn (Booking) | [SRS_BOOKING.md](./docs/srs/SRS_BOOKING.md) | ✅ Hoàn thiện |
| PAY-01 | 💳 Thanh toán trực tuyến (VNPay / ZaloPay / Momo) | [SRS_PAYMENT.md](./docs/srs/SRS_PAYMENT.md) | ✅ Hoàn thiện |
| SALON-01 | 💈 Quản lý Salon (Chủ Salon) | [SRS_SALON_MANAGEMENT.md](./docs/srs/SRS_SALON_MANAGEMENT.md) | ✅ Hoàn thiện |
| REVIEW-01 | ⭐ Đánh giá & Review | [SRS_REVIEW.md](./docs/srs/SRS_REVIEW.md) | ✅ Hoàn thiện |
| ADMIN-01 | 🛡️ Quản trị Hệ thống (Admin) | [SRS_ADMIN.md](./docs/srs/SRS_ADMIN.md) | ✅ Hoàn thiện |

---

## 🗂️ Cấu trúc thư mục dự án

```
barber-spa/
├── docs/
│   └── srs/
│       ├── SRS_AUTH.md
│       ├── SRS_SEARCH.md
│       ├── SRS_BOOKING.md
│       ├── SRS_PAYMENT.md
│       ├── SRS_SALON_MANAGEMENT.md
│       ├── SRS_REVIEW.md
│       └── SRS_ADMIN.md
├── public/
│   ├── index.php
│   ├── css/
│   ├── js/
│   └── images/
├── app/                    # (Laravel) hoặc src/ (PHP thuần)
│   ├── Controllers/
│   ├── Models/
│   └── Views/
├── database/
│   ├── migrations/
│   └── seeders/
├── .env.example
├── composer.json
└── README.md
```

---

## 🗓️ Kế hoạch thực hiện

| Ngày | Công việc |
|---|---|
| Ngày 1 | Thiết kế Database, dựng cấu trúc project, phân chia task |
| Ngày 2 | Giao diện User: trang chủ, tìm kiếm, chi tiết salon |
| Ngày 3 | Chức năng đặt lịch, chọn nhân viên, chọn giờ |
| Ngày 4 | Giao diện Chủ Salon: quản lý lịch, dịch vụ, nhân viên |
| Ngày 5 | Giao diện Admin + tích hợp thanh toán VNPay/Momo |
| Ngày 6 | Chức năng review, thống kê, báo cáo |
| Ngày 7 | Test toàn bộ, fix bug, hoàn thiện giao diện |

---

## ⚙️ Hướng dẫn cài đặt

```bash
# Clone repository
git clone https://github.com/<username>/barber-spa.git
cd barber-spa

# Cài đặt dependencies (Laravel)
composer install
npm install

# Cấu hình môi trường
cp .env.example .env
php artisan key:generate

# Chạy migration và seed dữ liệu mẫu
php artisan migrate --seed

# Khởi động server
php artisan serve
```

Truy cập tại: `http://localhost:8000`

---

## 📝 Ghi chú

- Tài liệu SRS được soạn thảo theo chuẩn IEEE 830
- Mọi thay đổi yêu cầu phải cập nhật file SRS tương ứng và ghi chú vào commit message
- Liên hệ nhóm trưởng Nguyễn Công Sơn (MSSV: 23810310102) để được hỗ trợ

---

*Hà Nội, tháng 03 năm 2026*
