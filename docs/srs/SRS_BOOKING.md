# Software Requirement Specification (SRS)
## Chức năng: Đặt lịch hẹn (Booking)
**Mã chức năng:** BOOK-01  
**Trạng thái:** Hoàn thiện  
**Dự án:** Website Đặt Lịch Cắt Tóc & Làm Đẹp (Barber & Spa)

---

### 1. Mô tả tổng quan
Cho phép khách hàng đặt lịch hẹn tại salon theo luồng: chọn dịch vụ → chọn nhân viên → chọn ngày/giờ → xác nhận → thanh toán. Hệ thống tự động kiểm tra slot trống và gửi thông báo xác nhận.

### 2. Luồng nghiệp vụ chính

| Bước | Hành động người dùng | Phản hồi hệ thống |
|:---|:---|:---|
| 1 | Vào trang chi tiết salon | Hiển thị danh sách dịch vụ, giá, thời gian |
| 2 | Chọn 1 hoặc nhiều dịch vụ | Cập nhật tổng giá, tổng thời gian dự kiến |
| 3 | Chọn nhân viên (hoặc "Bất kỳ") | Hiển thị lịch rảnh của nhân viên được chọn |
| 4 | Chọn ngày trên calendar | Hiển thị các khung giờ còn trống |
| 5 | Chọn khung giờ | Giữ slot tạm thời trong 10 phút |
| 6 | Xem lại thông tin & xác nhận | Hiển thị trang tóm tắt đặt lịch |
| 7 | Chọn phương thức thanh toán | Chuyển sang cổng thanh toán hoặc lưu "Thanh toán tại quầy" |
| 8 | Thanh toán thành công | Lưu booking, gửi email/SMS xác nhận, redirect trang "Lịch hẹn của tôi" |

### 3. Yêu cầu dữ liệu

**Bảng `bookings`:**
- `id`, `user_id`, `salon_id`, `staff_id`
- `services` (JSON array: service_id, name, price, duration)
- `booking_date` (date), `start_time` (time), `end_time` (time, tự tính)
- `total_price` (decimal), `status` (pending/confirmed/completed/cancelled)
- `payment_method` (online/at_counter), `payment_status` (paid/unpaid)
- `notes` (text, ghi chú của khách), `created_at`, `updated_at`

**Bảng `staff_schedules`:**
- `staff_id`, `day_of_week` (0-6), `start_time`, `end_time`, `is_off` (boolean)

### 4. Ràng buộc nghiệp vụ
- Chỉ hiển thị slot trống (không bị đặt trùng, trong giờ làm việc của nhân viên và salon)
- Không cho phép đặt lịch trong quá khứ hoặc trong vòng 2 giờ tới
- Tối đa 5 lịch hẹn đang active cho 1 tài khoản
- Slot giữ tạm thời 10 phút, sau đó tự động giải phóng nếu chưa thanh toán
- Thời gian kết thúc = thời gian bắt đầu + tổng duration các dịch vụ

### 5. Thông báo (Notification)
- Email xác nhận gửi ngay sau khi đặt thành công
- Email nhắc lịch 24h trước giờ hẹn
- SMS nhắc lịch 2h trước (nếu đã cấu hình SĐT)
- Thông báo realtime cho chủ salon qua dashboard

### 6. Chức năng Hủy & Đổi lịch

| Hành động | Điều kiện | Kết quả |
|:---|:---|:---|
| Hủy lịch | Trước 2h so với giờ hẹn | Refund 100% (nếu đã thanh toán online) |
| Hủy lịch | Trong vòng 2h | Không hoàn tiền, cần liên hệ salon |
| Đổi lịch | Trước 4h so với giờ hẹn | Cho phép chọn lại ngày/giờ |

### 7. Edge Cases & Xử lý lỗi

| Trường hợp | Xử lý |
|:---|:---|
| Slot bị đặt trong lúc chờ | "Khung giờ vừa được đặt, vui lòng chọn lại" |
| Nhân viên nghỉ đột xuất | Thông báo, gợi ý đổi nhân viên hoặc hủy |
| Salon đóng cửa ngày đó | Ẩn hoàn toàn ngày trên calendar |
| Session hết hạn khi đang đặt | Redirect login, giữ thông tin đặt lịch trong local storage |

### 8. Giao diện (UI/UX)
- Step-by-step wizard (4 bước rõ ràng với progress bar)
- Calendar component có đánh dấu ngày còn slot / đầy / đóng cửa
- Hiển thị thời gian thực countdown 10 phút khi đang giữ slot
- Mobile-first: các khung giờ hiển thị dạng grid dễ chọn bằng ngón tay
