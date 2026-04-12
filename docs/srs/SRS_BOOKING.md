# Software Requirement Specification (SRS)

## Chức năng: Đặt lịch dịch vụ

- **Mã chức năng:** BOOK-01
- **Trạng thái:** Hoàn thiện
- **Dự án:** Barber Spa (PHP thuần + MySQL + Bootstrap 5 + MVC)

---

## 1. Mô tả tổng quan

Chức năng đặt lịch cho phép khách hàng chọn dịch vụ, chọn nhân viên, chọn ngày giờ và xác nhận lịch hẹn tại salon.

Mục tiêu:

- tạo trải nghiệm đặt lịch rõ ràng, nhanh, ít nhầm lẫn
- kiểm soát xung đột lịch nhân viên
- ngăn đặt lịch quá sát giờ
- hỗ trợ giữ slot tạm thời trước khi thanh toán/xác nhận

---

## 2. Phạm vi chức năng

Module BOOK-01 bao gồm:

- Wizard đặt lịch 4 bước
- Chọn một hoặc nhiều dịch vụ
- Chọn nhân viên
- Chọn ngày và khung giờ
- Xác nhận thông tin lịch hẹn
- Chọn hình thức thanh toán
- Giữ slot trong 10 phút
- Kiểm tra conflict lịch nhân viên
- Giới hạn số booking active của mỗi user
- Xem danh sách booking của tôi
- Xem chi tiết booking
- Hủy lịch
- Đổi lịch

---

## 3. Wizard đặt lịch 4 bước

### Bước 1: Chọn dịch vụ

Người dùng chọn một hoặc nhiều dịch vụ thuộc salon.

### Bước 2: Chọn nhân viên

Người dùng chọn nhân viên phù hợp hoặc được gợi ý nhân viên của salon.

### Bước 3: Chọn ngày/giờ

Người dùng chọn ngày và khung giờ trống.

### Bước 4: Xác nhận

Người dùng xem lại toàn bộ thông tin:

- salon
- dịch vụ
- nhân viên
- ngày giờ
- tổng tiền
- phương thức thanh toán
- ghi chú

Sau đó xác nhận tạo booking.

---

## 4. Luồng nghiệp vụ chính

| Bước | Hành động người dùng                  | Phản hồi hệ thống                  |
| ---- | ------------------------------------- | ---------------------------------- |
| 1    | Truy cập flow booking từ salon detail | Kiểm tra user đã đăng nhập chưa    |
| 2    | Chọn 1 hoặc nhiều dịch vụ             | Tính tổng thời lượng và tổng tiền  |
| 3    | Chọn nhân viên                        | Kiểm tra nhân viên active          |
| 4    | Chọn ngày và giờ                      | Hệ thống tải slot trống            |
| 5    | Chọn slot                             | Hệ thống giữ slot tạm thời 10 phút |
| 6    | Xác nhận booking                      | Tạo booking nếu slot còn hợp lệ    |
| 7    | Chọn thanh toán                       | Điều hướng theo payment method     |
| 8    | Hoàn tất                              | Lưu booking và hiển thị chi tiết   |

---

## 5. Luồng hủy lịch

| Bước | Hành động người dùng       | Phản hồi hệ thống                           |
| ---- | -------------------------- | ------------------------------------------- |
| 1    | Vào “Lịch hẹn của tôi”     | Hiển thị danh sách booking                  |
| 2    | Chọn booking hợp lệ        | Kiểm tra trạng thái booking                 |
| 3    | Nhấn hủy lịch              | Yêu cầu xác nhận                            |
| 4    | Xác nhận hủy               | Hệ thống cập nhật trạng thái cancelled      |
| 5    | Kiểm tra thời gian còn lại | Trước 2h → hoàn 100%; trong 2h → không hoàn |

---

## 6. Luồng đổi lịch

| Bước | Hành động người dùng  | Phản hồi hệ thống                    |
| ---- | --------------------- | ------------------------------------ |
| 1    | Chọn booking muốn đổi | Kiểm tra quyền sở hữu booking        |
| 2    | Nhấn đổi lịch         | Kiểm tra còn đủ điều kiện đổi        |
| 3    | Chọn ngày giờ mới     | Tải slot mới                         |
| 4    | Xác nhận đổi          | Cập nhật ngày/giờ nếu không conflict |

---

## 7. Dữ liệu đầu vào

### 7.1 Dữ liệu chọn dịch vụ

- `salon_id`: integer, bắt buộc
- `service_ids[]`: mảng integer, bắt buộc, ít nhất 1 phần tử

### 7.2 Dữ liệu chọn nhân viên

- `staff_id`: integer, bắt buộc

### 7.3 Dữ liệu chọn thời gian

- `booking_date`: date, bắt buộc
- `start_time`: time, bắt buộc

### 7.4 Dữ liệu xác nhận

- `payment_method`: string, bắt buộc
  - `online`
  - `at_counter`
- `notes`: string, không bắt buộc

---

## 8. Bảng dữ liệu liên quan

### Bảng `bookings`

Các cột chính:

- `id`
- `user_id`
- `salon_id`
- `staff_id`
- `services`
- `booking_date`
- `start_time`
- `end_time`
- `total_price`
- `status`
- `payment_method`
- `payment_status`
- `notes`
- `cancel_reason`
- `slot_held_until`
- `created_at`
- `updated_at`

### Bảng `services`

- `id`
- `salon_id`
- `category_id`
- `name`
- `price`
- `duration`
- `is_active`

### Bảng `staff`

- `id`
- `salon_id`
- `name`
- `is_active`

### Bảng `staff_schedules`

- `id`
- `staff_id`
- `day_of_week`
- `start_time`
- `end_time`
- `is_off`

### Bảng `staff_day_off`

- `id`
- `staff_id`
- `off_date`
- `reason`
- `created_at`

---

## 9. Ràng buộc nghiệp vụ

### 9.1 Điều kiện đặt lịch

- Người dùng phải đăng nhập mới được đặt lịch
- Chỉ được chọn service đang active
- Chỉ được chọn staff đang active
- Staff phải thuộc đúng salon của booking
- Không cho đặt lịch trong vòng 2 giờ tới
- Một user chỉ được có tối đa 5 booking active
  - active gồm: `pending`, `confirmed`

### 9.2 Tính thời lượng và giờ kết thúc

- Tổng thời lượng booking = tổng `duration` của tất cả dịch vụ đã chọn
- `end_time` = `start_time` + tổng thời lượng

### 9.3 Kiểm tra slot hợp lệ

Slot được coi là hợp lệ khi:

- staff có lịch làm việc trong ngày đó
- staff không nghỉ đột xuất trong ngày đó
- slot nằm trong giờ mở cửa của salon
- slot không trùng booking khác của cùng staff
- slot chưa bị giữ bởi booking khác còn hiệu lực

### 9.4 Giữ slot

- Khi user chọn slot, hệ thống có thể giữ tạm bằng `slot_held_until`
- Thời gian giữ slot: 10 phút
- Nếu quá thời gian này mà chưa hoàn tất bước xác nhận/thanh toán:
  - slot được giải phóng

### 9.5 Trạng thái booking

Các trạng thái chính:

- `pending`
- `confirmed`
- `completed`
- `cancelled`

### 9.6 Hủy lịch

- Nếu hủy trước giờ hẹn ít nhất 2 giờ:
  - hoàn tiền 100% nếu đã thanh toán online
- Nếu hủy trong vòng dưới 2 giờ:
  - không hoàn tiền
- Chỉ booking chưa completed/cancelled mới được hủy

### 9.7 Đổi lịch

- Chỉ được đổi lịch trước giờ hẹn ít nhất 4 giờ
- Không cho đổi booking đã completed hoặc cancelled
- Thời gian mới phải hợp lệ như booking mới
- Có thể giữ nguyên payment nếu không phát sinh thay đổi giá

---

## 10. API liên quan

### 10.1 API lấy slot trống

**Endpoint:** `/api/get-slots.php`

**Method:** `GET`

**Input:**

- `staff_id`
- `booking_date`
- `duration`

**Output JSON mẫu:**

```json
{
  "success": true,
  "slots": ["09:00", "09:30", "10:00", "11:30"]
}
```
