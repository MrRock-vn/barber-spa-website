# Software Requirement Specification (SRS)
## Chức năng: Quản lý Salon (Salon Management)
**Mã chức năng:** SALON-01  
**Trạng thái:** Hoàn thiện  
**Dự án:** Website Đặt Lịch Cắt Tóc & Làm Đẹp (Barber & Spa)

---

### 1. Mô tả tổng quan
Cho phép Chủ Salon quản lý toàn bộ thông tin salon bao gồm: thông tin cơ bản, dịch vụ, nhân viên, lịch làm việc và lịch hẹn. Admin có thể duyệt, ẩn hoặc xóa salon khỏi hệ thống.

### 2. Quản lý thông tin Salon

| Trường | Kiểu dữ liệu | Ghi chú |
|:---|:---|:---|
| `name` | string | Tên salon, bắt buộc |
| `address` | string | Địa chỉ đầy đủ, bắt buộc |
| `phone` | string | SĐT liên hệ |
| `description` | text | Mô tả ngắn về salon |
| `images` | array | Tối đa 10 ảnh, tối thiểu 1 ảnh |
| `open_time` | time | Giờ mở cửa (mặc định 08:00) |
| `close_time` | time | Giờ đóng cửa (mặc định 20:00) |
| `working_days` | array | Các ngày hoạt động trong tuần |
| `status` | enum | pending/active/hidden/rejected |

### 3. Quản lý Dịch vụ

**Luồng thêm dịch vụ:**

| Bước | Hành động | Phản hồi |
|:---|:---|:---|
| 1 | Vào "Quản lý dịch vụ" → "Thêm mới" | Mở form thêm dịch vụ |
| 2 | Nhập tên, giá, thời gian, mô tả, ảnh | Validate realtime |
| 3 | Lưu | Dịch vụ xuất hiện ngay trên trang salon |

**Bảng `services`:**
- `id`, `salon_id`, `category_id`
- `name`, `description`, `price` (decimal), `duration` (minutes)
- `image`, `is_active` (boolean), `sort_order`

### 4. Quản lý Nhân viên

**Bảng `staff`:**
- `id`, `salon_id`, `name`, `phone`, `avatar`
- `specialties` (JSON: danh sách dịch vụ nhân viên làm được)
- `is_active` (boolean)

**Bảng `staff_schedules`:**
- `staff_id`, `day_of_week` (0=CN, 1-6=T2-T7)
- `start_time`, `end_time`, `is_off` (boolean)

**Chức năng:**
- Thêm/sửa/xóa nhân viên
- Gán dịch vụ cho từng nhân viên
- Thiết lập lịch làm việc theo tuần
- Đánh dấu nghỉ phép đột xuất theo ngày

### 5. Quản lý Lịch hẹn (Góc nhìn Chủ Salon)

| Trạng thái | Hành động được phép | Điều kiện |
|:---|:---|:---|
| pending | Xác nhận / Từ chối | Trước giờ hẹn 2h |
| confirmed | Hoàn thành / Yêu cầu hủy | Sau giờ hẹn |
| completed | Xem chi tiết | Chỉ đọc |
| cancelled | Xem lý do | Chỉ đọc |

**Giao diện quản lý lịch:**
- View dạng Calendar (tháng/tuần/ngày)
- View dạng danh sách có filter theo nhân viên, trạng thái, ngày
- Badge số lượng lịch pending cần xử lý

### 6. Luồng Đăng ký Salon mới

| Bước | Hành động | Phản hồi |
|:---|:---|:---|
| 1 | Chủ salon đăng ký tài khoản type "Owner" | Gửi email xác nhận |
| 2 | Điền thông tin salon & upload giấy phép | Submit chờ duyệt |
| 3 | Admin duyệt | Email thông báo, salon active |
| 4 | Admin từ chối | Email kèm lý do, cho phép sửa và nộp lại |

### 7. Thống kê Doanh thu (Dashboard Salon)

- Tổng doanh thu ngày / tuần / tháng
- Số lịch hẹn theo trạng thái
- Dịch vụ được đặt nhiều nhất
- Nhân viên có rating cao nhất
- Biểu đồ đường doanh thu theo ngày (7 ngày gần nhất)

### 8. Edge Cases & Xử lý lỗi

| Trường hợp | Xử lý |
|:---|:---|
| Xóa nhân viên còn lịch hẹn tương lai | Cảnh báo, yêu cầu chuyển lịch sang nhân viên khác |
| Sửa giá dịch vụ | Không ảnh hưởng các lịch đã đặt trước đó |
| Salon tắt dịch vụ đang có booking | Cảnh báo, phải xử lý các booking pending trước |

### 9. Giao diện (UI/UX)
- Sidebar navigation: Tổng quan, Lịch hẹn, Dịch vụ, Nhân viên, Đánh giá, Doanh thu
- Responsive: hoạt động tốt trên tablet (phục vụ chủ salon dùng iPad)
- Notification badge realtime khi có booking mới
