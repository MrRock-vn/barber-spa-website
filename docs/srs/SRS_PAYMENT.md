# Software Requirement Specification (SRS)

## Chức năng: Thanh toán & Hoàn tiền

- **Mã chức năng:** PAY-01
- **Trạng thái:** Hoàn thiện
- **Dự án:** Barber Spa (PHP thuần + MySQL + Bootstrap 5 + MVC)

---

## 1. Mô tả tổng quan

Chức năng thanh toán cho phép khách hàng thanh toán lịch hẹn bằng hình thức tại quầy hoặc online qua cổng thanh toán.

Mục tiêu:

- hỗ trợ nhiều phương thức thanh toán
- lưu được giao dịch rõ ràng, chống trùng
- cập nhật đúng trạng thái thanh toán của booking
- hỗ trợ hoàn tiền theo chính sách hủy lịch hoặc salon hủy lịch

---

## 2. Phạm vi chức năng

Module PAY-01 bao gồm:

- Thanh toán tại quầy
- Thanh toán online
- Tích hợp VNPay sandbox
- Tích hợp Momo sandbox
- Xử lý callback/return
- Xác minh checksum/chữ ký
- Lưu giao dịch thanh toán
- Chống xử lý trùng transaction
- Tạo yêu cầu hoàn tiền
- Admin duyệt hoàn tiền
- Đồng bộ trạng thái payment và booking

---

## 3. Luồng nghiệp vụ thanh toán tại quầy

| Bước | Hành động người dùng             | Phản hồi hệ thống                              |
| ---- | -------------------------------- | ---------------------------------------------- |
| 1    | Chọn payment method = at_counter | Hệ thống ghi nhận booking                      |
| 2    | Xác nhận booking                 | Tạo booking với `payment_status = unpaid`      |
| 3    | Hoàn tất                         | Không redirect sang cổng thanh toán            |
| 4    | Khi thanh toán thực tế tại salon | Owner/Admin có thể cập nhật trạng thái phù hợp |

---

## 4. Luồng nghiệp vụ thanh toán online

| Bước | Hành động người dùng           | Phản hồi hệ thống                        |
| ---- | ------------------------------ | ---------------------------------------- |
| 1    | Chọn payment method = online   | Hệ thống chuẩn bị request thanh toán     |
| 2    | Chọn gateway                   | VNPay hoặc Momo                          |
| 3    | Submit                         | Tạo booking/pending payment              |
| 4    | Redirect sang gateway          | Người dùng thực hiện thanh toán          |
| 5    | Gateway trả về return/callback | Hệ thống verify dữ liệu                  |
| 6    | Hợp lệ                         | Cập nhật payment success và booking paid |
| 7    | Không hợp lệ/thất bại          | Cập nhật trạng thái failed               |

---

## 5. Luồng hoàn tiền

| Bước | Hành động                                        | Phản hồi hệ thống                            |
| ---- | ------------------------------------------------ | -------------------------------------------- |
| 1    | User hủy lịch đúng điều kiện hoặc salon hủy lịch | Kiểm tra điều kiện refund                    |
| 2    | Hệ thống tạo record refund                       | `status = pending`                           |
| 3    | Admin xem yêu cầu hoàn tiền                      | Duyệt hoặc từ chối                           |
| 4    | Hoàn tiền thành công                             | Cập nhật `refunds.status = success`          |
| 5    | Đồng bộ payment                                  | Payment có thể chuyển `refunded` nếu hoàn đủ |

---

## 6. Dữ liệu đầu vào

### 6.1 Input tạo payment

- `booking_id`: integer, bắt buộc
- `gateway`: string, bắt buộc
  - `vnpay`
  - `momo`
  - `cash`
- `amount`: number, bắt buộc
- `currency`: string, mặc định `VND`

### 6.2 Input callback gateway

Tùy gateway sẽ có các trường khác nhau, nhưng phải có:

- mã giao dịch
- số tiền
- trạng thái
- chữ ký/checksum
- dữ liệu tham chiếu booking/order

### 6.3 Input refund

- `payment_id`: integer, bắt buộc
- `amount`: number, bắt buộc
- `reason`: string, không bắt buộc

---

## 7. Bảng dữ liệu liên quan

### Bảng `payments`

Các cột chính:

- `id`
- `booking_id`
- `user_id`
- `gateway`
- `transaction_id`
- `amount`
- `currency`
- `status`
- `gateway_response`
- `paid_at`
- `created_at`

### Bảng `refunds`

- `id`
- `payment_id`
- `amount`
- `reason`
- `status`
- `refunded_at`
- `created_at`

### Bảng `bookings`

Các cột liên quan:

- `id`
- `payment_method`
- `payment_status`
- `status`
- `total_price`

---

## 8. Ràng buộc nghiệp vụ

### 8.1 Payment method

Hệ thống hỗ trợ:

- `at_counter`
- `online`

Nếu `at_counter`:

- không redirect
- booking được tạo với `payment_status = unpaid`

Nếu `online`:

- phải chọn gateway online hợp lệ

### 8.2 Gateway hỗ trợ

- **VNPay sandbox**
- **Momo sandbox**
- **Cash** cho thanh toán trực tiếp

### 8.3 Trạng thái payment

Các trạng thái:

- `pending`
- `success`
- `failed`
- `refunded`

### 8.4 Đồng bộ booking

- Khi payment online thành công:
  - `bookings.payment_status = paid`
- Khi payment online thất bại:
  - `bookings.payment_status = unpaid`
- Khi booking thanh toán tại quầy:
  - ban đầu thường là `unpaid`

### 8.5 Idempotency

- Không được xử lý một `transaction_id` nhiều lần
- `transaction_id` phải unique
- Nếu callback bị gửi lặp:
  - hệ thống phải nhận ra giao dịch đã xử lý
  - không tạo duplicate payment

### 8.6 Xác minh callback

- VNPay phải verify checksum
- Momo phải verify chữ ký/callback
- Chỉ cập nhật trạng thái khi xác minh hợp lệ

### 8.7 Hoàn tiền

- Nếu user hủy trước giờ hẹn ít nhất 2 giờ:
  - hoàn 100% với booking đã thanh toán online
- Nếu hủy trong vòng dưới 2 giờ:
  - không hoàn tiền
- Nếu salon tự hủy:
  - có thể hoàn 100%
- Refund tạo record riêng, không sửa tay trực tiếp payment gốc

---

## 9. API/Files dự kiến

### 9.1 VNPay

- `api/payment/vnpay-config.php`
- `api/payment/vnpay-redirect.php`
- `api/payment/vnpay-return.php`

### 9.2 Momo

- `api/payment/momo-config.php`
- `api/payment/momo-redirect.php`
- `api/payment/momo-return.php`

### 9.3 Route/controller

- `/payment`
- `/payment/confirm`

---

## 10. Xử lý lỗi và edge cases

| Trường hợp                          | Cách xử lý                                                    |
| ----------------------------------- | ------------------------------------------------------------- |
| Gateway trả callback thiếu dữ liệu  | Đánh dấu lỗi, không cập nhật success                          |
| Checksum/chữ ký sai                 | Từ chối callback                                              |
| `transaction_id` đã tồn tại         | Không xử lý trùng                                             |
| Thanh toán thất bại                 | Giữ payment ở failed/unpaid                                   |
| User đóng trang giữa chừng          | Booking/payment vẫn ở trạng thái pending hoặc unpaid tùy flow |
| Số tiền callback không khớp booking | Từ chối xác nhận giao dịch                                    |
| Refund vượt số tiền thanh toán      | Không cho tạo refund                                          |
| Payment không tồn tại               | Báo lỗi hợp lệ                                                |

---

## 11. Yêu cầu bảo mật

- Không trust dữ liệu amount từ client
- Số tiền phải lấy từ booking trên server
- Dùng prepared statement cho mọi query
- Không để lộ secret key gateway trong code public
- File config payment phải tách riêng
- Verify đầy đủ checksum/chữ ký trước khi ghi success
- Chống xử lý lặp callback
- Log callback quan trọng để debug nội bộ

---

## 12. UI/UX yêu cầu

### 12.1 Màn hình xác nhận thanh toán

- Hiển thị rõ:
  - mã booking
  - salon
  - tổng tiền
  - phương thức thanh toán
- Có trạng thái thanh toán dễ hiểu:
  - Chưa thanh toán
  - Thanh toán thành công
  - Thanh toán thất bại
  - Đã hoàn tiền

### 12.2 Sau thanh toán

- Có flash message rõ ràng
- Nếu thành công:
  - chuyển đến chi tiết booking/payment result
- Nếu thất bại:
  - cho phép thử lại hoặc chọn phương thức khác

---

## 13. Tiêu chí hoàn thành

Module PAY-01 được coi là hoàn thành khi:

- Thanh toán tại quầy hoạt động
- Có thể tạo URL thanh toán VNPay sandbox
- Có thể xử lý return/callback VNPay
- Có thể tạo URL thanh toán Momo sandbox
- Có thể xử lý return/callback Momo
- Verify checksum/chữ ký đúng
- Không xử lý trùng transaction_id
- Lưu payment record đúng
- Đồng bộ đúng `payment_status` của booking
- Tạo refund record đúng
- Admin có thể xử lý refund

---

## 14. Route dự kiến

- `/payment`
- `/payment/confirm`
- `api/payment/vnpay-redirect.php`
- `api/payment/vnpay-return.php`
- `api/payment/momo-redirect.php`
- `api/payment/momo-return.php`

---

## 15. Ghi chú triển khai cho dự án PHP thuần

Trong project `barber-spa`, module này sẽ được triển khai qua:

- `controllers/PaymentController.php`
- `models/Payment.php`
- `models/Refund.php`
- `models/Booking.php`
- `views/payment/index.php`
- `api/payment/vnpay-config.php`
- `api/payment/vnpay-redirect.php`
- `api/payment/vnpay-return.php`
- `api/payment/momo-config.php`
- `api/payment/momo-redirect.php`
- `api/payment/momo-return.php`
