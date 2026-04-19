# ĐỀ CƯƠNG THỰC TẬP TỐT NGHIỆP

## Tên đề tài: Xây Dựng Website Đặt Lịch Cắt Tóc & Làm Đẹp (Barber & Spa)

**Lớp:** LTWNC-D18CNPM2  
**Nhóm:** 21  
**Thành viên:**

| STT | Họ và tên        | MSSV        | Vai trò     |
| --- | ---------------- | ----------- | ----------- |
| 1   | Nguyễn Công Sơn  | 23810310102 | Nhóm trưởng |
| 2   | Nguyễn Văn Quang | 23810310108 | Thành viên  |
| 3   | Nguyễn Văn Danh  | 23810310136 | Thành viên  |

---

## Mô tả tóm tắt

Website cho phép khách hàng tìm kiếm salon/barber, xem danh sách dịch vụ, đặt lịch hẹn theo ngày/giờ và thanh toán trực tuyến. Chủ salon có thể quản lý lịch hẹn, nhân viên và doanh thu. Admin quản lý toàn bộ hệ thống.

---

## Chức năng dự kiến

### 🔐 Admin

- Quản lý tài khoản người dùng (xem, khóa, mở khóa, phân quyền)
- Quản lý danh sách Salon (duyệt đăng ký, ẩn, xóa)
- Quản lý danh mục dịch vụ (cắt tóc, uốn, nhuộm, spa,...)
- Quản lý lịch hẹn toàn hệ thống
- Quản lý đánh giá / review (duyệt, xóa vi phạm)
- Thống kê / Báo cáo (doanh thu, lịch hẹn, salon nổi bật)

### 💈 Chủ Salon (Owner)

- Đăng ký / Đăng nhập tài khoản salon
- Quản lý thông tin salon (tên, địa chỉ, ảnh, giờ mở cửa)
- Quản lý dịch vụ (thêm, sửa, xóa dịch vụ, cập nhật giá)
- Quản lý nhân viên (thêm, sửa, xóa, phân công ca làm)
- Quản lý lịch hẹn (xác nhận, từ chối, cập nhật trạng thái)
- Xem doanh thu & báo cáo theo ngày/tháng
- Xem đánh giá từ khách hàng

### 👤 User (Khách hàng)

- Trang chủ (salon nổi bật, dịch vụ hot, tìm kiếm nhanh)
- Tìm kiếm & lọc salon (theo khu vực, loại dịch vụ, đánh giá sao)
- Xem chi tiết salon & danh sách dịch vụ
- Đặt lịch hẹn (chọn dịch vụ → chọn nhân viên → chọn ngày/giờ)
- Thanh toán online qua VNPay / ZaloPay / Momo
- Xem & quản lý lịch hẹn của bản thân (hủy, đổi lịch)
- Đăng ký / Đăng nhập / Quên mật khẩu
- Quản lý thông tin cá nhân
- Xem lịch sử đặt lịch
- Viết review & đánh giá sao sau khi sử dụng dịch vụ

---

## Công nghệ sử dụng

| Thành phần | Công nghệ              |
| ---------- | ---------------------- |
| Frontend   | HTML, CSS, JavaScript  |
| Backend    | PHP thuần / Laravel    |
| Database   | MySQL                  |
| Thanh toán | VNPay / ZaloPay / Momo |
| Giao diện  | Bootstrap 5            |

---

## Phân chia công việc

| Thành viên   | Phụ trách                                                                 |
| ------------ | ------------------------------------------------------------------------- |
| Thành viên 1 | Frontend: Giao diện User (trang chủ, tìm salon, đặt lịch, thanh toán)     |
| Thành viên 2 | Frontend: Giao diện Chủ Salon + Admin (quản lý lịch, nhân viên, thống kê) |
| Thành viên 3 | Backend: Database, API xử lý đặt lịch, tích hợp thanh toán                |

---

## Kế hoạch thực hiện (1 tuần)

| Ngày   | Công việc                                                 |
| ------ | --------------------------------------------------------- |
| Ngày 1 | Thiết kế Database, dựng cấu trúc project, phân chia task  |
| Ngày 2 | Làm giao diện User: trang chủ, tìm kiếm, chi tiết salon   |
| Ngày 3 | Làm chức năng đặt lịch, chọn nhân viên, chọn giờ          |
| Ngày 4 | Làm giao diện Chủ Salon: quản lý lịch, dịch vụ, nhân viên |
| Ngày 5 | Làm giao diện Admin + tích hợp thanh toán VNPay/Momo      |
| Ngày 6 | Làm chức năng review, thống kê, báo cáo                   |
| Ngày 7 | Test toàn bộ, fix bug, hoàn thiện giao diện, viết README  |

---

_Hà Nội, tháng 03 năm 2026_
