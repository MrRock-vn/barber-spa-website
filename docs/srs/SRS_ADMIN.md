# Software Requirement Specification (SRS)

## Chức năng: Quản trị hệ thống

- **Mã chức năng:** ADMIN-01
- **Trạng thái:** Hoàn thiện
- **Dự án:** Barber Spa (PHP thuần + MySQL + Bootstrap 5 + MVC)

---

## 1. Mô tả tổng quan

Chức năng quản trị dành cho Admin để giám sát và điều hành toàn bộ hệ thống.

Mục tiêu:

- quản lý người dùng
- duyệt và kiểm soát salon
- quản lý danh mục
- theo dõi booking
- xử lý review bị report
- theo dõi KPI hệ thống

---

## 2. Phạm vi chức năng

Module ADMIN-01 bao gồm:

- Admin dashboard
- Quản lý users
- Search/filter users
- Ban/unban user
- Quản lý salons
- Duyệt/từ chối/ẩn/xóa salon
- Quản lý categories
- Giám sát bookings
- Xử lý review flagged
- Theo dõi top salon và thống kê hệ thống
- Xử lý refund pending

---

## 3. Luồng nghiệp vụ dashboard

| Bước | Hành động admin             | Phản hồi hệ thống                            |
| ---- | --------------------------- | -------------------------------------------- |
| 1    | Truy cập `/admin/dashboard` | Kiểm tra role admin                          |
| 2    | Tải dashboard               | Hiển thị KPI hệ thống                        |
| 3    | Xem top salon               | Hiển thị theo rating/bookings/revenue nếu có |
| 4    | Xem biểu đồ                 | Hiển thị tổng quan booking/user/revenue      |

Dashboard nên có:

- tổng user
- tổng owner
- tổng customer
- tổng salon
- salon pending
- tổng booking
- booking hôm nay
- tổng review
- review flagged
- refund pending
- top 10 salon

---

## 4. Luồng nghiệp vụ quản lý user

| Bước | Hành động admin             | Phản hồi hệ thống         |
| ---- | --------------------------- | ------------------------- |
| 1    | Truy cập `/admin/users`     | Hiển thị danh sách user   |
| 2    | Tìm kiếm/filter             | Lọc theo role, trạng thái |
| 3    | Chọn user                   | Xem thông tin             |
| 4    | Ban/unban user              | Cập nhật trạng thái       |
| 5    | Hệ thống lưu log/trạng thái | Hiển thị flash message    |

---

## 5. Luồng nghiệp vụ quản lý salon

| Bước | Hành động admin          | Phản hồi hệ thống                      |
| ---- | ------------------------ | -------------------------------------- |
| 1    | Truy cập `/admin/salons` | Hiển thị danh sách salon               |
| 2    | Lọc salon theo status    | pending/active/hidden/rejected/deleted |
| 3    | Duyệt salon pending      | Chuyển sang active                     |
| 4    | Từ chối salon            | Chuyển rejected và lưu lý do           |
| 5    | Ẩn salon                 | Chuyển hidden                          |
| 6    | Xóa salon                | Chỉ khi không còn booking active       |

---

## 6. Luồng nghiệp vụ quản lý category

| Bước | Hành động admin              | Phản hồi hệ thống                       |
| ---- | ---------------------------- | --------------------------------------- |
| 1    | Truy cập `/admin/categories` | Hiển thị danh sách category             |
| 2    | Thêm category                | Validate dữ liệu                        |
| 3    | Sửa category                 | Cập nhật DB                             |
| 4    | Xóa category                 | Chỉ xóa khi không còn service liên quan |

---

## 7. Luồng nghiệp vụ giám sát booking

| Bước | Hành động admin            | Phản hồi hệ thống                        |
| ---- | -------------------------- | ---------------------------------------- |
| 1    | Truy cập `/admin/bookings` | Hiển thị tất cả booking                  |
| 2    | Filter đa chiều            | Theo ngày, salon, trạng thái, thanh toán |
| 3    | Xem chi tiết               | Hiển thị đầy đủ booking                  |
| 4    | Theo dõi bất thường        | Hỗ trợ xử lý khiếu nại/đối soát          |

---

## 8. Luồng nghiệp vụ xử lý review flagged

| Bước | Hành động admin                 | Phản hồi hệ thống                 |
| ---- | ------------------------------- | --------------------------------- |
| 1    | Truy cập `/admin/reviews`       | Hiển thị review flagged/published |
| 2    | Xem chi tiết review + số report | Hiển thị thông tin liên quan      |
| 3    | Quyết định xử lý                | approve / remove / warn user      |
| 4    | Cập nhật trạng thái review      | Đồng bộ hiển thị công khai        |

---

## 9. Bảng dữ liệu liên quan

### Bảng `users`

- `id`
- `name`
- `email`
- `role`
- `is_active`
- `ban_reason`
- `created_at`

### Bảng `salons`

- `id`
- `owner_id`
- `name`
- `city`
- `district`
- `avg_rating`
- `total_reviews`
- `total_bookings`
- `status`
- `reject_reason`
- `created_at`

### Bảng `categories`

- `id`
- `name`
- `icon`
- `description`
- `sort_order`
- `is_active`

### Bảng `services`

- `id`
- `salon_id`
- `category_id`

### Bảng `bookings`

- `id`
- `user_id`
- `salon_id`
- `staff_id`
- `booking_date`
- `start_time`
- `total_price`
- `status`
- `payment_method`
- `payment_status`
- `created_at`

### Bảng `reviews`

- `id`
- `booking_id`
- `user_id`
- `salon_id`
- `rating`
- `content`
- `status`
- `report_count`
- `created_at`

### Bảng `review_reports`

- `id`
- `review_id`
- `reporter_id`
- `reason`
- `created_at`

### Bảng `payments`

- `id`
- `booking_id`
- `user_id`
- `gateway`
- `transaction_id`
- `amount`
- `status`

### Bảng `refunds`

- `id`
- `payment_id`
- `amount`
- `reason`
- `status`
- `refunded_at`
- `created_at`

---

## 10. Ràng buộc nghiệp vụ

### 10.1 Quyền admin

- Chỉ user có `role = admin` được vào admin panel
- Admin có quyền toàn hệ thống
- Tuy nhiên không được thực hiện một số thao tác nguy hiểm với chính mình nếu chính sách cấm

### 10.2 Quản lý user

- Có thể filter theo:
  - role
  - is_active
- Có thể ban/unban user
- Không cho admin tự ban chính mình
- Khi ban user:
  - nên lưu `ban_reason`

### 10.3 Quản lý salon

- Salon pending có thể:
  - approve
  - reject
- Khi reject salon:
  - cần nhập lý do
- Khi xóa salon:
  - chỉ được xóa nếu không còn booking active
- Salon hidden/deleted không hiển thị cho customer

### 10.4 Quản lý category

- Tên category nên unique
- Không cho xóa category nếu còn service dùng category đó
- Có thể dùng soft-delete hoặc chặn xóa cứng

### 10.5 Giám sát booking

- Admin được xem toàn bộ booking
- Có thể filter đa điều kiện
- Có thể export là tùy chọn mở rộng
- Không nên sửa tay các trường nhạy cảm nếu không có quy trình rõ ràng

### 10.6 Xử lý review

- Review có `report_count >= 3` thường sẽ flagged
- Admin quyết định:
  - giữ nguyên published
  - remove
  - cảnh báo user
- Review removed không hiển thị công khai

### 10.7 Refund

- Refund pending cần được admin duyệt/xử lý
- Sau khi refund thành công cần cập nhật trạng thái tương ứng

---

## 11. Xử lý lỗi và edge cases

| Trường hợp                                | Cách xử lý                          |
| ----------------------------------------- | ----------------------------------- |
| Admin truy cập route không tồn tại        | Trả 404                             |
| User thường cố vào admin panel            | Trả 403 hoặc redirect               |
| Admin tự ban chính mình                   | Từ chối thao tác                    |
| Xóa salon còn booking active              | Từ chối                             |
| Xóa category còn service                  | Từ chối                             |
| Review flagged nhưng thiếu dữ liệu report | Hiển thị để admin kiểm tra thủ công |
| Refund pending không còn payment hợp lệ   | Đánh dấu lỗi xử lý                  |

---

## 12. Yêu cầu bảo mật

- Kiểm tra role admin trong mọi controller admin
- Dùng prepared statement cho mọi query
- Escape output chống XSS
- Form POST phải có CSRF token
- Action nguy hiểm cần confirm dialog
- Không hiển thị stack trace/lỗi SQL ra giao diện admin production

---

## 13. UI/UX yêu cầu

### 13.1 Admin panel

- Sidebar layout
- Dashboard card KPI
- Bảng dữ liệu rõ ràng
- Bộ lọc tiện dùng
- Badge trạng thái màu sắc rõ ràng
- Pagination cho danh sách dài

### 13.2 Action nguy hiểm

- Ban user
- Reject salon
- Xóa salon
- Remove review

Các action này cần:

- confirm dialog
- flash message sau khi xử lý

---

## 14. Tiêu chí hoàn thành

Module ADMIN-01 được coi là hoàn thành khi:

- Dashboard hiển thị KPI cơ bản
- Quản lý user hoạt động
- Ban/unban user hoạt động
- Không cho admin tự ban mình
- Duyệt/từ chối/ẩn/xóa salon hoạt động đúng điều kiện
- Quản lý category hoạt động đúng điều kiện
- Giám sát booking hoạt động
- Xử lý review flagged hoạt động
- Refund pending được theo dõi/xử lý

---

## 15. Route dự kiến

- `/admin/dashboard`
- `/admin/users`
- `/admin/salons`
- `/admin/categories`
- `/admin/bookings`
- `/admin/reviews`

---

## 16. Ghi chú triển khai cho dự án PHP thuần

Trong project `barber-spa`, module này sẽ được triển khai qua:

- `controllers/admin/DashboardController.php`
- `controllers/admin/UserController.php`
- `controllers/admin/SalonController.php`
- `controllers/admin/CategoryController.php`
- `controllers/admin/BookingController.php`
- `controllers/admin/ReviewController.php`
- `models/User.php`
- `models/Salon.php`
- `models/Category.php`
- `models/Booking.php`
- `models/Review.php`
- `models/Refund.php`
- `views/admin/*`
