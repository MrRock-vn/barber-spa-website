# Software Requirement Specification (SRS)

## Chức năng: Đánh giá & Phản hồi

- **Mã chức năng:** REVIEW-01
- **Trạng thái:** Hoàn thiện
- **Dự án:** Barber Spa (PHP thuần + MySQL + Bootstrap 5 + MVC)

---

## 1. Mô tả tổng quan

Chức năng đánh giá cho phép khách hàng để lại nhận xét sau khi sử dụng dịch vụ và cho phép chủ salon phản hồi lại đánh giá đó.

Mục tiêu:

- tăng độ tin cậy cho salon
- phản ánh chất lượng dịch vụ thực tế
- hỗ trợ owner tương tác lại với khách hàng
- kiểm soát nội dung xấu, spam hoặc báo cáo sai lệch

---

## 2. Phạm vi chức năng

Module REVIEW-01 bao gồm:

- Tạo review sau khi hoàn thành booking
- Mỗi booking chỉ được review 1 lần
- Chỉ review trong 30 ngày kể từ khi hoàn thành
- Chấm điểm từ 1 đến 5 sao
- Viết nội dung review
- Upload ảnh review
- Sửa review trong 24 giờ
- Report review
- Tự động flagged nếu bị report nhiều
- Owner reply review
- Cập nhật rating trung bình của salon

---

## 3. Luồng nghiệp vụ tạo review

| Bước | Hành động customer          | Phản hồi hệ thống                        |
| ---- | --------------------------- | ---------------------------------------- |
| 1    | Vào booking đã hoàn thành   | Kiểm tra booking hợp lệ để review        |
| 2    | Chọn số sao + nhập nội dung | Validate dữ liệu                         |
| 3    | Upload ảnh nếu có           | Kiểm tra số lượng, dung lượng, định dạng |
| 4    | Submit                      | Tạo review mới                           |
| 5    | Thành công                  | Cập nhật `avg_rating` của salon          |

---

## 4. Luồng nghiệp vụ sửa review

| Bước | Hành động customer           | Phản hồi hệ thống                   |
| ---- | ---------------------------- | ----------------------------------- |
| 1    | Truy cập review của mình     | Kiểm tra quyền sở hữu               |
| 2    | Chọn sửa review              | Kiểm tra còn trong 24 giờ hay không |
| 3    | Cập nhật nội dung/rating/ảnh | Validate dữ liệu                    |
| 4    | Submit                       | Lưu thay đổi                        |
| 5    | Thành công                   | Tính lại điểm trung bình nếu cần    |

---

## 5. Luồng report review

| Bước | Hành động người dùng         | Phản hồi hệ thống                   |
| ---- | ---------------------------- | ----------------------------------- |
| 1    | Chọn report review           | Hiển thị lý do report               |
| 2    | Chọn lý do                   | Lưu report                          |
| 3    | Hệ thống tăng `report_count` | Nếu đủ ngưỡng thì chuyển flagged    |
| 4    | Admin kiểm tra               | Duyệt giữ nguyên hoặc xóa/ẩn review |

---

## 6. Luồng owner reply

| Bước | Hành động owner           | Phản hồi hệ thống                       |
| ---- | ------------------------- | --------------------------------------- |
| 1    | Xem review của salon mình | Kiểm tra ownership                      |
| 2    | Nhập nội dung phản hồi    | Validate dữ liệu                        |
| 3    | Submit                    | Lưu `owner_reply` và `owner_replied_at` |
| 4    | Thành công                | Hiển thị phản hồi công khai cùng review |

---

## 7. Dữ liệu đầu vào

### 7.1 Tạo review

- `booking_id`: integer, bắt buộc
- `rating`: integer, bắt buộc, từ 1 đến 5
- `content`: string, bắt buộc, từ 10 đến 1000 ký tự
- `images[]`: file, không bắt buộc, tối đa 5 ảnh

### 7.2 Sửa review

- `review_id`: integer, bắt buộc
- `rating`: integer, bắt buộc, từ 1 đến 5
- `content`: string, bắt buộc, từ 10 đến 1000 ký tự
- `images[]`: file, không bắt buộc

### 7.3 Report review

- `review_id`: integer, bắt buộc
- `reason`: string, bắt buộc
  - `spam`
  - `offensive`
  - `false_info`

### 7.4 Owner reply

- `review_id`: integer, bắt buộc
- `owner_reply`: string, bắt buộc

---

## 8. Bảng dữ liệu liên quan

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

### Bảng `review_reports`

- `id`
- `review_id`
- `reporter_id`
- `reason`
- `created_at`

### Bảng `bookings`

Các cột liên quan:

- `id`
- `user_id`
- `salon_id`
- `staff_id`
- `status`
- `updated_at`

### Bảng `salons`

Các cột liên quan:

- `id`
- `avg_rating`
- `total_reviews`

---

## 9. Ràng buộc nghiệp vụ

### 9.1 Điều kiện được review

- Chỉ customer sở hữu booking mới được review booking đó
- Booking phải có `status = completed`
- Mỗi booking chỉ được tạo tối đa 1 review
- Chỉ được review trong vòng 30 ngày kể từ ngày hoàn thành

### 9.2 Rating và nội dung

- Rating chỉ từ 1 đến 5
- Content từ 10 đến 1000 ký tự
- Không cho nội dung rỗng hoặc quá ngắn

### 9.3 Ảnh review

- Tối đa 5 ảnh
- Mỗi ảnh tối đa 5MB
- Chỉ chấp nhận:
  - JPG
  - JPEG
  - PNG
- Ảnh phải được đổi tên an toàn khi lưu
- Lưu vào `public/uploads/reviews/`

### 9.4 Sửa review

- Chỉ chủ review mới được sửa
- Chỉ được sửa trong vòng 24 giờ kể từ lúc tạo
- Sau 24 giờ thì khóa chỉnh sửa

### 9.5 Report review

- Mỗi user chỉ được report 1 lần cho 1 review
- Nếu `report_count >= 3`:
  - review tự động chuyển `status = flagged`
- Admin là người quyết định cuối cùng:
  - giữ review
  - remove review
  - cảnh báo user

### 9.6 Owner reply

- Chỉ owner của salon chứa review mới được reply
- Owner không được sửa nội dung review gốc
- Owner reply được hiển thị công khai bên dưới review

### 9.7 Cập nhật rating salon

- Sau khi tạo review mới:
  - cập nhật `avg_rating`
  - cập nhật `total_reviews`
- Sau khi sửa review:
  - tính lại `avg_rating` nếu rating thay đổi
- Chỉ tính các review có `status = published`

---

## 10. Trạng thái review

Các trạng thái:

- `published`
- `flagged`
- `removed`

Quy tắc:

- review mới mặc định thường là `published`
- nếu bị report nhiều có thể chuyển `flagged`
- review `removed` không hiển thị công khai

---

## 11. Xử lý lỗi và edge cases

| Trường hợp                                    | Cách xử lý         |
| --------------------------------------------- | ------------------ |
| Booking chưa completed                        | Không cho review   |
| Booking không thuộc user hiện tại             | Từ chối            |
| Booking đã có review                          | Không cho tạo thêm |
| Review quá 30 ngày                            | Không cho tạo mới  |
| Review quá 24h                                | Không cho sửa      |
| Upload quá 5 ảnh                              | Báo lỗi            |
| Ảnh vượt quá 5MB                              | Báo lỗi            |
| Ảnh sai định dạng                             | Báo lỗi            |
| User report trùng                             | Từ chối            |
| Owner reply vào review không thuộc salon mình | Trả 403            |

---

## 12. Yêu cầu bảo mật

- Kiểm tra quyền sở hữu booking/review trước mọi thao tác
- Dùng prepared statement cho mọi query
- Escape output chống XSS
- Kiểm tra MIME type thực tế khi upload ảnh
- Đổi tên file an toàn, không dùng tên gốc trực tiếp
- Không cho upload file thực thi
- Validate server-side toàn bộ rating/content/images

---

## 13. UI/UX yêu cầu

### 13.1 Tạo review

- Form thân thiện
- Có chọn sao trực quan
- Có preview ảnh đã chọn
- Có hiển thị giới hạn số ảnh

### 13.2 Danh sách review

- Hiển thị:
  - tên người dùng
  - số sao
  - nội dung
  - ảnh review
  - thời gian tạo
  - owner reply nếu có

### 13.3 Edit review

- Chỉ hiện nút sửa nếu còn trong 24 giờ
- Có flash message sau khi cập nhật

### 13.4 Report review

- Có nút report rõ ràng
- Có modal/chọn lý do report

### 13.5 Owner reply

- Ô nhập phản hồi ngay trong owner panel hoặc dưới review
- Hiển thị phân biệt rõ giữa review và phản hồi của salon

---

## 14. Tiêu chí hoàn thành

Module REVIEW-01 được coi là hoàn thành khi:

- Chỉ booking completed mới review được
- Mỗi booking chỉ có 1 review
- Giới hạn 30 ngày hoạt động đúng
- Rating 1-5 và content 10-1000 validate đúng
- Upload ảnh tối đa 5 file, đúng định dạng, đúng dung lượng
- Sửa review trong 24h hoạt động
- Report review hoạt động
- Tự flagged khi đủ 3 report
- Owner reply hoạt động
- `avg_rating` của salon cập nhật đúng

---

## 15. Route dự kiến

- `/write-review`
- `/edit-review/{id}`

---

## 16. Ghi chú triển khai cho dự án PHP thuần

Trong project `barber-spa`, module này sẽ được triển khai qua:

- `controllers/ReviewController.php`
- `models/Review.php`
- `models/Booking.php`
- `models/Salon.php`
- `views/review/create.php`
- `views/review/edit.php`
- `controllers/owner/ReviewController.php`
