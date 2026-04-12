# Software Requirement Specification (SRS)

## Chức năng: Quản lý Salon dành cho Chủ Salon

- **Mã chức năng:** SALON-01
- **Trạng thái:** Hoàn thiện
- **Dự án:** Barber Spa (PHP thuần + MySQL + Bootstrap 5 + MVC)

---

## 1. Mô tả tổng quan

Chức năng này dành cho **Owner** để quản lý salon của mình, bao gồm:

- đăng ký salon
- chỉnh sửa thông tin salon
- quản lý dịch vụ
- quản lý nhân viên
- quản lý lịch làm việc
- xử lý booking
- theo dõi đánh giá
- theo dõi doanh thu

Mục tiêu:

- giúp chủ salon vận hành salon ngay trên hệ thống
- tách rõ quyền owner với admin và customer
- hỗ trợ quản lý booking và hiệu suất hoạt động salon

---

## 2. Phạm vi chức năng

Module SALON-01 bao gồm:

- Owner dashboard
- Đăng ký salon mới
- Chỉnh sửa thông tin salon
- Quản lý dịch vụ (CRUD)
- Quản lý nhân viên (CRUD)
- Quản lý lịch làm việc tuần của nhân viên
- Quản lý ngày nghỉ đột xuất
- Xác nhận hoặc từ chối booking
- Theo dõi review
- Trả lời review
- Theo dõi doanh thu
- Theo dõi booking hôm nay và sắp tới

---

## 3. Luồng nghiệp vụ đăng ký salon

| Bước | Hành động owner                | Phản hồi hệ thống                           |
| ---- | ------------------------------ | ------------------------------------------- |
| 1    | Đăng nhập owner                | Kiểm tra quyền owner                        |
| 2    | Truy cập `/owner/salon/create` | Hiển thị form tạo salon                     |
| 3    | Nhập thông tin salon           | Validate dữ liệu                            |
| 4    | Submit                         | Tạo salon với `status = pending`            |
| 5    | Chờ admin duyệt                | Owner chưa thể public salon cho customer    |
| 6    | Admin duyệt                    | Salon chuyển `active` và hiển thị công khai |

---

## 4. Luồng nghiệp vụ quản lý salon

| Bước | Hành động owner                 | Phản hồi hệ thống           |
| ---- | ------------------------------- | --------------------------- |
| 1    | Truy cập `/owner/salon`         | Hiển thị salon thuộc owner  |
| 2    | Sửa thông tin salon             | Validate dữ liệu            |
| 3    | Lưu thay đổi                    | Cập nhật DB                 |
| 4    | Nếu salon đang pending/rejected | Hiển thị trạng thái rõ ràng |
| 5    | Nếu salon bị rejected           | Hiển thị lý do từ chối      |

---

## 5. Luồng nghiệp vụ quản lý dịch vụ

| Bước | Hành động owner            | Phản hồi hệ thống             |
| ---- | -------------------------- | ----------------------------- |
| 1    | Truy cập `/owner/services` | Hiển thị danh sách dịch vụ    |
| 2    | Thêm dịch vụ mới           | Validate tên, giá, thời lượng |
| 3    | Sửa dịch vụ                | Cập nhật dữ liệu              |
| 4    | Ẩn/xóa mềm dịch vụ         | Ngừng hiển thị cho customer   |
| 5    | Sắp xếp thứ tự             | Cập nhật `sort_order`         |

---

## 6. Luồng nghiệp vụ quản lý nhân viên

| Bước | Hành động owner         | Phản hồi hệ thống                  |
| ---- | ----------------------- | ---------------------------------- |
| 1    | Truy cập `/owner/staff` | Hiển thị danh sách staff           |
| 2    | Thêm nhân viên          | Lưu thông tin cơ bản + specialties |
| 3    | Cập nhật lịch tuần      | Ghi vào `staff_schedules`          |
| 4    | Thêm ngày nghỉ đột xuất | Ghi vào `staff_day_off`            |
| 5    | Tắt hoạt động nhân viên | Không cho nhận booking mới         |

---

## 7. Luồng nghiệp vụ quản lý booking

| Bước | Hành động owner            | Phản hồi hệ thống                            |
| ---- | -------------------------- | -------------------------------------------- |
| 1    | Truy cập `/owner/bookings` | Hiển thị booking của salon                   |
| 2    | Xem chi tiết booking       | Hiển thị dịch vụ, staff, giờ hẹn, thanh toán |
| 3    | Xác nhận booking           | Đổi trạng thái phù hợp                       |
| 4    | Từ chối/hủy booking        | Ghi lý do và xử lý refund nếu cần            |
| 5    | Hoàn thành booking         | Cho phép customer review sau đó              |

---

## 8. Luồng nghiệp vụ dashboard

Dashboard owner cần hiển thị:

- tổng số booking
- booking hôm nay
- booking sắp tới
- doanh thu
- số review mới
- đánh giá trung bình
- danh sách booking gần nhất
- review gần đây

---

## 9. Bảng dữ liệu liên quan

### Bảng `salons`

- `id`
- `owner_id`
- `name`
- `address`
- `district`
- `city`
- `phone`
- `description`
- `search_keywords`
- `open_time`
- `close_time`
- `working_days`
- `avg_rating`
- `total_reviews`
- `total_bookings`
- `status`
- `reject_reason`
- `latitude`
- `longitude`
- `created_at`
- `updated_at`

### Bảng `salon_images`

- `id`
- `salon_id`
- `image_path`
- `is_primary`
- `sort_order`

### Bảng `services`

- `id`
- `salon_id`
- `category_id`
- `name`
- `description`
- `price`
- `duration`
- `image`
- `is_active`
- `sort_order`

### Bảng `staff`

- `id`
- `salon_id`
- `name`
- `phone`
- `avatar`
- `specialties`
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

### Bảng `bookings`

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

### Bảng `reviews`

- `id`
- `booking_id`
- `user_id`
- `salon_id`
- `staff_id`
- `rating`
- `content`
- `images`
- `status`
- `owner_reply`
- `owner_replied_at`
- `report_count`
- `created_at`
- `updated_at`

### Bảng `payments`

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

---

## 10. Ràng buộc nghiệp vụ

### 10.1 Quyền owner

- Chỉ user có `role = owner` mới vào được khu vực owner
- Owner chỉ được thao tác với salon thuộc chính mình
- Không được sửa dữ liệu salon của owner khác

### 10.2 Trạng thái salon

Các trạng thái:

- `pending`
- `active`
- `hidden`
- `rejected`
- `deleted`

Quy tắc:

- salon mới tạo mặc định là `pending`
- chỉ `active` mới hiển thị cho customer
- nếu `rejected` phải có `reject_reason`

### 10.3 Quản lý dịch vụ

- Service phải gắn với 1 salon hợp lệ
- Service phải gắn với 1 category hợp lệ
- Giá phải >= 0
- Thời lượng phải > 0
- Chỉ service active mới cho đặt lịch
- Có thể sắp xếp bằng `sort_order`

### 10.4 Quản lý nhân viên

- Staff phải thuộc salon của owner
- Có thể lưu specialties
- Chỉ staff active mới nhận booking
- Lịch tuần phải hợp lệ về thời gian
- Ngày nghỉ đột xuất không được trùng logic sai

### 10.5 Xử lý booking

- Owner có thể xem booking của salon mình
- Owner có thể xác nhận booking pending
- Owner có thể hủy booking trong trường hợp phù hợp
- Nếu owner/salon hủy booking đã thanh toán online:
  - tạo refund theo chính sách
- Owner có thể đánh dấu booking completed sau khi dịch vụ kết thúc

### 10.6 Review

- Owner được xem review của salon mình
- Owner có thể trả lời review
- Không được sửa nội dung gốc review của customer

### 10.7 Revenue

- Revenue lấy từ booking/payment hợp lệ
- Chỉ nên tính những booking hoàn thành hoặc payment thành công theo quy tắc hệ thống
- Cần lọc theo ngày/tháng nếu có

---

## 11. Xử lý lỗi và edge cases

| Trường hợp                                               | Cách xử lý                              |
| -------------------------------------------------------- | --------------------------------------- |
| Owner chưa có salon                                      | Hiển thị CTA tạo salon                  |
| Salon đang pending                                       | Không hiển thị công khai cho customer   |
| Salon bị rejected                                        | Hiển thị reject reason                  |
| Owner cố truy cập salon không thuộc mình                 | Trả 403                                 |
| Thêm service giá âm                                      | Báo lỗi validate                        |
| Thêm staff thiếu thông tin bắt buộc                      | Báo lỗi validate                        |
| Xóa/ẩn service đang có booking tương lai                 | Chỉ cho ẩn mềm, cân nhắc không xóa cứng |
| Owner hủy booking đã paid                                | Tạo refund pending                      |
| Booking đã completed/cancelled mà vẫn sửa trạng thái sai | Từ chối thao tác                        |

---

## 12. Yêu cầu bảo mật

- Kiểm tra role owner ở mọi controller owner
- Kiểm tra salon ownership trước mọi thao tác
- Dùng prepared statement cho mọi truy vấn
- Escape output chống XSS
- Upload ảnh phải kiểm tra MIME/type/size
- Không cho owner tự set trạng thái vượt quyền admin, ví dụ tự duyệt salon từ pending sang active nếu chính sách yêu cầu admin duyệt

---

## 13. UI/UX yêu cầu

### 13.1 Owner panel

- Sidebar layout rõ ràng
- Dashboard dễ nhìn
- Responsive cơ bản
- Có flash message sau mỗi action

### 13.2 Quản lý salon

- Form rõ ràng, có phân nhóm thông tin
- Hiển thị trạng thái salon bằng badge

### 13.3 Quản lý dịch vụ

- Bảng danh sách service
- Có nút thêm/sửa/ẩn
- Có sort order

### 13.4 Quản lý nhân viên

- Hiển thị avatar, tên, chuyên môn
- Có màn hình chỉnh lịch tuần
- Có quản lý ngày nghỉ đột xuất

### 13.5 Quản lý booking

- Bộ lọc theo ngày/trạng thái
- Nút xác nhận/hủy rõ ràng
- Hiển thị payment status

### 13.6 Quản lý review và doanh thu

- Hiển thị rating trung bình
- Có ô trả lời review
- Có thống kê doanh thu tổng quan

---

## 14. Tiêu chí hoàn thành

Module SALON-01 được coi là hoàn thành khi:

- Owner tạo được salon mới
- Salon mới vào trạng thái pending
- Owner sửa được thông tin salon của mình
- CRUD service hoạt động
- CRUD staff hoạt động
- Quản lý lịch tuần và ngày nghỉ đột xuất hoạt động
- Owner xem và xử lý booking của salon mình được
- Owner xem review và trả lời review được
- Dashboard hiển thị dữ liệu tổng quan
- Revenue hiển thị đúng theo dữ liệu hợp lệ

---

## 15. Route dự kiến

- `/owner/dashboard`
- `/owner/salon/create`
- `/owner/salon`
- `/owner/services`
- `/owner/staff`
- `/owner/staff/schedule`
- `/owner/bookings`
- `/owner/reviews`
- `/owner/revenue`

---

## 16. Ghi chú triển khai cho dự án PHP thuần

Trong project `barber-spa`, module này sẽ được triển khai qua:

- `controllers/owner/DashboardController.php`
- `controllers/owner/SalonController.php`
- `controllers/owner/ServiceController.php`
- `controllers/owner/StaffController.php`
- `controllers/owner/BookingController.php`
- `controllers/owner/ReviewController.php`
- `controllers/owner/RevenueController.php`
- `models/Salon.php`
- `models/Service.php`
- `models/Staff.php`
- `models/Booking.php`
- `models/Review.php`
- `models/Payment.php`
- `views/owner/*`
