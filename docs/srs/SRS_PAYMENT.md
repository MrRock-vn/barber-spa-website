# Software Requirement Specification (SRS)
## Chức năng: Thanh toán trực tuyến (Payment)
**Mã chức năng:** PAY-01  
**Trạng thái:** Hoàn thiện  
**Dự án:** Website Đặt Lịch Cắt Tóc & Làm Đẹp (Barber & Spa)

---

### 1. Mô tả tổng quan
Tích hợp cổng thanh toán VNPay, ZaloPay và Momo để xử lý thanh toán trực tuyến cho các lịch hẹn. Đảm bảo tính an toàn, nhất quán dữ liệu và xử lý đúng các trường hợp thất bại.

### 2. Cổng thanh toán hỗ trợ

| Cổng | Phương thức | Môi trường |
|:---|:---|:---|
| VNPay | QR Code, ATM nội địa, Visa/Master | Sandbox + Production |
| ZaloPay | Ví ZaloPay, QR Code | Sandbox + Production |
| Momo | Ví Momo, QR Code | Sandbox + Production |
| Tại quầy | Cash khi đến salon | N/A |

### 3. Luồng thanh toán VNPay (tham khảo)

| Bước | Hành động | Phản hồi hệ thống |
|:---|:---|:---|
| 1 | Chọn "Thanh toán VNPay" | Tạo đơn hàng, generate URL redirect |
| 2 | Redirect sang cổng VNPay | Hiển thị trang thanh toán VNPay |
| 3 | Người dùng thanh toán | VNPay xử lý giao dịch |
| 4 | Thành công | VNPay redirect về `/payment/return?...` |
| 5 | Hệ thống nhận IPN | Verify chữ ký, cập nhật trạng thái booking |
| 6 | Xác nhận hoàn tất | Hiển thị trang "Đặt lịch thành công" |

### 4. Yêu cầu dữ liệu

**Bảng `payments`:**
- `id`, `booking_id`, `user_id`
- `gateway` (vnpay/zalopay/momo/cash)
- `transaction_id` (ID từ cổng thanh toán)
- `amount` (decimal), `currency` (VND)
- `status` (pending/success/failed/refunded)
- `gateway_response` (JSON, raw response từ cổng)
- `paid_at` (timestamp), `created_at`

**Bảng `refunds`:**
- `id`, `payment_id`, `amount`, `reason`, `status`, `refunded_at`

### 5. Ràng buộc kỹ thuật & Bảo mật
- **Verify chữ ký (checksum)** từ cổng thanh toán trước khi xử lý IPN
- **Idempotency:** Không xử lý trùng IPN cùng transaction_id
- **HTTPS bắt buộc** cho tất cả callback/return URL
- **Không lưu** thông tin thẻ ngân hàng trực tiếp (PCI DSS)
- **Log đầy đủ** request/response từ cổng để đối soát

### 6. Xử lý Hoàn tiền (Refund)

| Tình huống | Điều kiện | Quy trình |
|:---|:---|:---|
| Khách hủy lịch trước 2h | Đã thanh toán online | Hoàn 100% qua cổng gốc, xử lý trong 1-3 ngày |
| Salon hủy lịch | Bất kỳ | Hoàn 100%, ưu tiên xử lý trong 24h |
| Khách hủy muộn | Đã thanh toán | Không hoàn, cần thỏa thuận trực tiếp |

### 7. Edge Cases & Xử lý lỗi

| Trường hợp | Xử lý |
|:---|:---|
| Timeout không nhận được IPN | Job cron check trạng thái sau 5 phút |
| IPN đến nhưng chữ ký sai | Log lại, trả về lỗi 400, không cập nhật DB |
| Thanh toán thất bại | Giải phóng slot, thông báo thất bại, cho phép thử lại |
| Trùng IPN cùng transaction | Bỏ qua, trả 200 OK để cổng không gửi lại |
| Kết nối mạng mất khi redirect | Trang chờ confirm với polling mỗi 3 giây |

### 8. Giao diện (UI/UX)
- Trang chọn phương thức thanh toán hiển thị logo từng cổng rõ ràng
- Trang loading "Đang xử lý thanh toán..." trong khi chờ redirect
- Trang kết quả: Success (confetti animation) / Failed (nút thử lại)
- Hiển thị mã giao dịch và hướng dẫn khi có vấn đề
