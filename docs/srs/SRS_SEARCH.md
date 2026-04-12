# Software Requirement Specification (SRS)

## Chức năng: Tìm kiếm Salon & Khám phá dịch vụ

- **Mã chức năng:** SEARCH-01
- **Trạng thái:** Hoàn thiện
- **Dự án:** Barber Spa (PHP thuần + MySQL + Bootstrap 5 + MVC)

---

## 1. Mô tả tổng quan

Chức năng tìm kiếm cho phép người dùng khám phá salon/barber/spa theo từ khóa, khu vực, danh mục dịch vụ, mức giá và đánh giá.

Mục tiêu:

- giúp người dùng tìm salon nhanh
- lọc được salon phù hợp nhu cầu
- xem chi tiết salon, dịch vụ, nhân viên và đánh giá
- hỗ trợ trải nghiệm đặt lịch nhanh từ trang chi tiết

---

## 2. Phạm vi chức năng

Module SEARCH-01 bao gồm:

- Trang chủ với hero search
- Hiển thị salon nổi bật
- Hiển thị danh mục dịch vụ
- Autocomplete từ khóa
- Danh sách kết quả tìm kiếm
- Bộ lọc theo khu vực, category, rating, giá
- Sắp xếp kết quả
- Trang chi tiết salon
- Nút “Đặt lịch ngay” trên trang salon detail

---

## 3. Luồng nghiệp vụ trang chủ

| Bước | Hành động người dùng       | Phản hồi hệ thống                            |
| ---- | -------------------------- | -------------------------------------------- |
| 1    | Truy cập `/home`           | Hiển thị banner/hero search                  |
| 2    | Xem salon nổi bật          | Hiển thị tối đa 6 salon active               |
| 3    | Xem categories             | Hiển thị 5 category chính                    |
| 4    | Nhập từ khóa tìm kiếm      | Gợi ý autocomplete theo thời gian thực       |
| 5    | Chọn khu vực hoặc category | Chuẩn bị dữ liệu tìm kiếm                    |
| 6    | Nhấn tìm kiếm              | Chuyển đến trang `/search` với query phù hợp |

---

## 4. Luồng nghiệp vụ tìm kiếm

| Bước | Hành động người dùng          | Phản hồi hệ thống                                  |
| ---- | ----------------------------- | -------------------------------------------------- |
| 1    | Truy cập `/search`            | Hiển thị form lọc + danh sách salon                |
| 2    | Nhập từ khóa hoặc chọn bộ lọc | Hệ thống nhận điều kiện lọc                        |
| 3    | Submit tìm kiếm               | Query DB bằng điều kiện phù hợp                    |
| 4    | Có kết quả                    | Hiển thị danh sách salon, phân trang               |
| 5    | Không có kết quả              | Hiển thị trạng thái “Không tìm thấy salon phù hợp” |
| 6    | Chọn 1 salon                  | Chuyển sang trang chi tiết salon                   |

---

## 5. Luồng nghiệp vụ chi tiết salon

| Bước | Hành động người dùng   | Phản hồi hệ thống                         |
| ---- | ---------------------- | ----------------------------------------- |
| 1    | Truy cập `/salon/{id}` | Hệ thống kiểm tra salon tồn tại và active |
| 2    | Tải dữ liệu salon      | Hiển thị thông tin cơ bản, ảnh, mô tả     |
| 3    | Xem danh sách dịch vụ  | Hiển thị service đang active              |
| 4    | Xem nhân viên          | Hiển thị staff của salon                  |
| 5    | Xem đánh giá           | Hiển thị review published                 |
| 6    | Nhấn “Đặt lịch ngay”   | Chuyển sang luồng booking                 |

---

## 6. Yêu cầu dữ liệu đầu vào

### 6.1 Từ khóa tìm kiếm

- kiểu dữ liệu: string
- không bắt buộc
- có thể tìm theo:
  - tên salon
  - mô tả
  - search keywords
  - tên dịch vụ
  - khu vực

### 6.2 Bộ lọc

- **city**
  - string
  - không bắt buộc
- **district**
  - string
  - không bắt buộc
- **category_id**
  - integer
  - không bắt buộc
- **min_price**
  - number
  - không bắt buộc
- **max_price**
  - number
  - không bắt buộc
- **rating**
  - number
  - không bắt buộc
  - giá trị thường: 3, 4, 5
- **sort**
  - string
  - không bắt buộc
  - giá trị cho phép:
    - `rating_desc`
    - `newest`
    - `popular`
    - `price_asc`

### 6.3 Phân trang

- **page**
  - integer
  - không bắt buộc
  - mặc định: 1

---

## 7. Bảng dữ liệu liên quan

### Bảng `salons`

Các cột chính:

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

### Bảng `categories`

- `id`
- `name`
- `icon`
- `description`
- `sort_order`
- `is_active`

### Bảng `staff`

- `id`
- `salon_id`
- `name`
- `phone`
- `avatar`
- `specialties`
- `is_active`

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

---

## 8. Ràng buộc nghiệp vụ

### 8.1 Trang chủ

- Chỉ hiển thị salon có `status = active`
- Hiển thị tối đa 6 salon nổi bật
- Hiển thị tối đa 5 category đang active
- Có ô nhập từ khóa tìm kiếm ở hero section

### 8.2 Autocomplete

- Endpoint dự kiến: `/api/autocomplete.php?q=...`
- Trả về JSON
- Chỉ trả về tối đa một số lượng hợp lý, ví dụ 5-10 kết quả
- Gợi ý có thể bao gồm:
  - tên salon
  - district/city
  - dịch vụ phổ biến

### 8.3 Tìm kiếm

- Chỉ tìm trong salon đang active
- Có thể lọc theo khu vực
- Có thể lọc theo category dịch vụ
- Có thể lọc theo rating
- Có thể lọc theo khoảng giá
- Kết quả phải có phân trang

### 8.4 Sắp xếp

Hệ thống hỗ trợ:

- **Đánh giá cao nhất** → theo `avg_rating DESC`
- **Mới nhất** → theo `created_at DESC`
- **Phổ biến nhất** → theo `total_bookings DESC`
- **Giá thấp nhất** → theo mức giá dịch vụ thấp nhất tăng dần

### 8.5 Trang chi tiết salon

- Chỉ hiển thị salon active
- Hiển thị gallery ảnh
- Hiển thị danh sách dịch vụ active
- Hiển thị danh sách nhân viên active
- Hiển thị đánh giá có `status = published`
- Có nút “Đặt lịch ngay” luôn nhìn thấy tốt trên mobile

---

## 9. Yêu cầu API

### 9.1 API autocomplete

**Endpoint:** `/api/autocomplete.php`

**Method:** `GET`

**Input:**

- `q`: từ khóa tìm kiếm

**Output JSON mẫu:**

```json
[
  {
    "type": "salon",
    "label": "Barber House Quận 1",
    "value": "Barber House Quận 1"
  },
  { "type": "district", "label": "Quận 1", "value": "Quận 1" }
]
```
