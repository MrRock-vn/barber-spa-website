# Software Requirement Specification (SRS)
## Chức năng: Tìm kiếm & Khám phá Salon (Search & Discovery)
**Mã chức năng:** SEARCH-01  
**Trạng thái:** Hoàn thiện  
**Dự án:** Website Đặt Lịch Cắt Tóc & Làm Đẹp (Barber & Spa)

---

### 1. Mô tả tổng quan
Cho phép khách hàng tìm kiếm salon theo nhiều tiêu chí (khu vực, dịch vụ, đánh giá, giá) và xem thông tin chi tiết salon trước khi đặt lịch. Trang chủ hiển thị các salon nổi bật và dịch vụ hot.

### 2. Trang chủ (Homepage)

**Các thành phần chính:**
- **Hero Section:** Thanh tìm kiếm nhanh (nhập tên salon / dịch vụ + khu vực)
- **Salon nổi bật:** Top 6-8 salon có rating cao nhất hoặc được gợi ý theo vị trí
- **Dịch vụ phổ biến:** Danh mục dịch vụ hot (Cắt tóc, Uốn, Nhuộm, Spa...)
- **Khuyến mãi:** Banner salon đang có ưu đãi (nếu có)

### 3. Luồng Tìm kiếm

| Bước | Hành động | Phản hồi |
|:---|:---|:---|
| 1 | Nhập từ khóa vào ô tìm kiếm | Autocomplete gợi ý salon / dịch vụ realtime |
| 2 | Submit hoặc nhấn Enter | Redirect đến trang kết quả `/search?q=...` |
| 3 | Áp dụng bộ lọc | Cập nhật kết quả không reload trang (AJAX) |
| 4 | Sắp xếp kết quả | Thay đổi thứ tự hiển thị |
| 5 | Click vào salon | Redirect sang trang chi tiết salon |

### 4. Bộ lọc (Filter)

| Tiêu chí | Loại | Giá trị |
|:---|:---|:---|
| Khu vực | Dropdown | Theo tỉnh/thành phố, quận/huyện |
| Loại dịch vụ | Multi-select checkbox | Danh mục từ Admin |
| Đánh giá sao | Radio | 4★+, 3★+, Tất cả |
| Khoảng giá | Range slider | 0 - 500.000đ, có thể nhập tay |
| Còn slot hôm nay | Toggle | Chỉ hiện salon còn slot trống hôm nay |

### 5. Sắp xếp (Sort)

| Tùy chọn | Logic |
|:---|:---|
| Phù hợp nhất (mặc định) | Kết hợp rating + số booking + độ gần |
| Đánh giá cao nhất | Giảm dần theo `avg_rating` |
| Giá thấp nhất | Tăng dần theo giá dịch vụ rẻ nhất |
| Mới nhất | Ngày tham gia hệ thống |

### 6. Trang chi tiết Salon

**Thông tin hiển thị:**
- Gallery ảnh salon (carousel, click để zoom)
- Tên, địa chỉ, SĐT, giờ mở cửa
- Điểm đánh giá tổng hợp (★ x.x / 5) và số lượng review
- Map embed (Google Maps / OpenStreetMap)
- Danh sách nhân viên (ảnh, tên, rating)
- Danh sách dịch vụ kèm giá và thời gian
- Tab đánh giá (pagination 10 review/trang)
- Nút "Đặt lịch ngay" cố định (sticky) khi scroll

### 7. Yêu cầu dữ liệu

**Bảng `salons` (thêm các trường phục vụ search):**
- `avg_rating` (decimal, tự động tính lại sau mỗi review)
- `total_reviews` (int)
- `total_bookings` (int)
- `latitude`, `longitude` (để tính khoảng cách)
- `search_keywords` (text, fulltext index)

**Index cần tạo:**
- `FULLTEXT INDEX` trên `name`, `description`, `search_keywords`
- `INDEX` trên `avg_rating`, `status`, `district_id`

### 8. Edge Cases & Xử lý lỗi

| Trường hợp | Xử lý |
|:---|:---|
| Không có kết quả | Gợi ý mở rộng khu vực hoặc thay đổi filter |
| Salon hết slot hôm nay | Hiển thị ngày gần nhất còn slot |
| Salon không active | Ẩn khỏi kết quả, redirect về trang chủ nếu truy cập trực tiếp |
| Mạng chậm | Skeleton loading placeholder |

### 9. Giao diện (UI/UX)
- Layout: Filter bên trái (desktop) / Drawer từ dưới lên (mobile)
- Kết quả dạng card grid (2 cột mobile, 3-4 cột desktop)
- Infinite scroll hoặc pagination (10 salon/trang)
- Ảnh thumbnail lazy loading
- Badge "Còn slot hôm nay" / "Hot" / "Mới" trên card salon
