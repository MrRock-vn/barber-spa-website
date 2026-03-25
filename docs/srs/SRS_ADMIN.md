# Software Requirement Specification (SRS)
## Chức năng: Quản trị hệ thống (Admin Management)
**Mã chức năng:** ADMIN-01  
**Trạng thái:** Hoàn thiện  
**Dự án:** Website Đặt Lịch Cắt Tóc & Làm Đẹp (Barber & Spa)

---

### 1. Mô tả tổng quan
Cung cấp giao diện quản trị toàn hệ thống cho Admin: quản lý tài khoản người dùng, duyệt salon, quản lý danh mục dịch vụ, giám sát lịch hẹn, xử lý review vi phạm và xem báo cáo thống kê tổng hợp.

### 2. Phân quyền Admin

| Quyền | Mô tả |
|:---|:---|
| `admin.users.*` | Xem, khóa, mở khóa, đổi role người dùng |
| `admin.salons.*` | Duyệt, ẩn, xóa salon |
| `admin.categories.*` | CRUD danh mục dịch vụ |
| `admin.bookings.view` | Xem tất cả lịch hẹn toàn hệ thống |
| `admin.reviews.*` | Xem, ẩn, xóa review vi phạm |
| `admin.reports.*` | Xem báo cáo & thống kê |

### 3. Quản lý Tài khoản người dùng

**Luồng xử lý:**

| Bước | Hành động | Phản hồi |
|:---|:---|:---|
| 1 | Tìm kiếm user theo email/tên | Hiển thị danh sách có pagination |
| 2 | Click vào user | Xem chi tiết: thông tin, lịch sử booking, review |
| 3 | Khóa tài khoản | Nhập lý do → User không đăng nhập được, nhận email thông báo |
| 4 | Đổi role | Confirm modal → Cập nhật ngay lập tức |

**Bảng filter:**
- Lọc theo: Role (admin/owner/customer), Trạng thái (active/banned), Ngày đăng ký

### 4. Quản lý Salon

| Trạng thái | Hành động Admin | Kết quả |
|:---|:---|:---|
| pending | Duyệt / Từ chối (kèm lý do) | Owner nhận email thông báo |
| active | Ẩn tạm thời | Salon không xuất hiện trên trang tìm kiếm |
| hidden | Kích hoạt lại | Salon xuất hiện trở lại |
| active/hidden | Xóa vĩnh viễn | Xóa mềm (soft delete), giữ dữ liệu booking |

**Điều kiện xóa vĩnh viễn:** Chỉ xóa được khi salon không còn booking active/confirmed.

### 5. Quản lý Danh mục Dịch vụ

**Bảng `categories`:**
- `id`, `name`, `icon`, `description`, `sort_order`, `is_active`

**Chức năng:**
- Thêm / Sửa / Xóa danh mục
- Xóa danh mục: Cảnh báo nếu còn dịch vụ đang sử dụng, yêu cầu chuyển dịch vụ sang danh mục khác trước
- Kéo thả để thay đổi thứ tự hiển thị (drag & drop sort)

### 6. Giám sát Lịch hẹn toàn hệ thống

**Bảng danh sách lịch hẹn:**
- Cột: Mã lịch, Khách hàng, Salon, Dịch vụ, Ngày/Giờ, Tổng tiền, Trạng thái, Thanh toán
- Filter: Theo salon, theo trạng thái, theo khoảng ngày, theo cổng thanh toán
- Export ra Excel (`.xlsx`)

### 7. Xử lý Review Vi phạm

| Bước | Hành động | Phản hồi |
|:---|:---|:---|
| 1 | Vào tab "Review cần duyệt" | Hiển thị review có `status = flagged` |
| 2 | Xem nội dung + lý do report | Thông tin đầy đủ kèm lịch sử report |
| 3a | Giữ nguyên | Đổi status về `published`, xóa flag |
| 3b | Xóa vi phạm | Status → `removed`, gửi email thông báo tác giả |
| 3c | Cảnh cáo user | Xóa review + ghi chú vào profile người dùng |

### 8. Báo cáo & Thống kê

**Dashboard Admin - KPIs:**
- Tổng người dùng (và tăng trưởng tuần/tháng)
- Tổng salon đang hoạt động
- Tổng lịch hẹn (hôm nay / tháng này)
- Tổng doanh thu giao dịch qua hệ thống (chỉ online)

**Biểu đồ:**
- Đường: Số lịch hẹn 30 ngày gần nhất
- Cột: Doanh thu theo tháng (12 tháng)
- Tròn: Phân bổ theo cổng thanh toán (VNPay/Momo/ZaloPay/Tại quầy)
- Bảng Top 10 salon có nhiều booking nhất tháng

**Export báo cáo:** PDF hoặc Excel theo khoảng ngày tùy chọn

### 9. Edge Cases & Xử lý lỗi

| Trường hợp | Xử lý |
|:---|:---|
| Admin tự khóa tài khoản mình | Không cho phép, hiển thị cảnh báo |
| Xóa danh mục còn dịch vụ | Bắt buộc reassign trước, không cho xóa |
| Duyệt salon thiếu thông tin | Highlight trường còn thiếu, admin ghi chú yêu cầu bổ sung |

### 10. Giao diện (UI/UX)
- Layout sidebar cố định với navigation rõ ràng
- Các bảng dữ liệu có search, sort, pagination
- Toàn bộ action nguy hiểm (xóa, khóa) có confirm dialog
- Audit log: ghi lại mọi hành động admin (ai làm gì, lúc nào)
