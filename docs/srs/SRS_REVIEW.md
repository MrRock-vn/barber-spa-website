# Software Requirement Specification (SRS)
## Chức năng: Đánh giá & Review (Review & Rating)
**Mã chức năng:** REVIEW-01  
**Trạng thái:** Hoàn thiện  
**Dự án:** Website Đặt Lịch Cắt Tóc & Làm Đẹp (Barber & Spa)

---

### 1. Mô tả tổng quan
Cho phép khách hàng đánh giá sao và viết nhận xét sau khi hoàn thành dịch vụ. Chủ salon có thể phản hồi đánh giá. Admin có quyền duyệt và xóa review vi phạm.

### 2. Ràng buộc nghiệp vụ
- Chỉ khách hàng có lịch hẹn `status = completed` mới được viết review
- Mỗi lịch hẹn chỉ được đánh giá **1 lần**
- Thời hạn viết review: trong vòng **30 ngày** sau khi hoàn thành dịch vụ
- Review mặc định hiển thị ngay (không cần duyệt), trừ review bị flag

### 3. Luồng nghiệp vụ

| Bước | Hành động | Phản hồi |
|:---|:---|:---|
| 1 | Lịch hẹn chuyển sang `completed` | Gửi email/notification nhắc viết review |
| 2 | Khách vào "Lịch sử đặt lịch" | Thấy nút "Viết đánh giá" bên cạnh lịch đã xong |
| 3 | Click nút → Modal hoặc trang mới | Form đánh giá: chọn sao + nhập nội dung + upload ảnh |
| 4 | Submit | Lưu review, cập nhật avg_rating của salon ngay lập tức |
| 5 | Chủ salon phản hồi | Reply hiển thị bên dưới review gốc |

### 4. Yêu cầu dữ liệu

**Input khi viết review:**
- **Rating:** 1–5 sao, bắt buộc
- **Content:** text, tối thiểu 10 ký tự, tối đa 1000 ký tự, tùy chọn
- **Images:** tối đa 5 ảnh (JPG/PNG, mỗi ảnh ≤ 5MB), tùy chọn

**Bảng `reviews`:**
- `id`, `booking_id`, `user_id`, `salon_id`, `staff_id`
- `rating` (tinyint 1-5)
- `content` (text), `images` (JSON array paths)
- `status` (published/flagged/removed)
- `owner_reply` (text), `owner_replied_at` (timestamp)
- `created_at`, `updated_at`

### 5. Tính điểm Rating

```
avg_rating = SUM(rating) / COUNT(reviews WHERE status = 'published')
```
- Cập nhật `avg_rating` và `total_reviews` trên bảng `salons` ngay sau mỗi review mới / xóa review
- Làm tròn đến 1 chữ số thập phân (ví dụ: 4.7)

### 6. Phân quyền

| Hành động | Khách hàng | Chủ Salon | Admin |
|:---|:---:|:---:|:---:|
| Viết review | ✅ (đã dùng dịch vụ) | ❌ | ❌ |
| Sửa review | ✅ (trong 24h) | ❌ | ❌ |
| Xóa review | ❌ | ❌ | ✅ |
| Report review | ✅ | ✅ | — |
| Phản hồi review | ❌ | ✅ (salon của mình) | ❌ |
| Ẩn review vi phạm | ❌ | ❌ | ✅ |

### 7. Xử lý Review Vi phạm
- Người dùng / Chủ salon có thể report review (chọn lý do: Spam, Ngôn từ xúc phạm, Sai sự thật)
- Review bị report đủ 3 lần → tự động chuyển sang `flagged`, chờ Admin duyệt
- Admin xem xét và quyết định: Giữ nguyên / Xóa / Cảnh cáo tài khoản

### 8. Edge Cases & Xử lý lỗi

| Trường hợp | Xử lý |
|:---|:---|
| Viết review lịch chưa hoàn thành | Ẩn nút, không cho truy cập URL trực tiếp |
| Đã review rồi cố submit lại | "Bạn đã đánh giá lịch hẹn này rồi" |
| Quá 30 ngày | Nút đánh giá ẩn tự động |
| Upload ảnh quá dung lượng | "Ảnh quá lớn (tối đa 5MB mỗi ảnh)" |
| Chủ salon reply review bị xóa | Reply cũng bị xóa theo |

### 9. Giao diện (UI/UX)
- Star rating component: click hoặc hover để chọn số sao (animation fill)
- Hiển thị phân phối sao (bar chart: 5★ bao nhiêu %, 4★...) trên trang salon
- Review card: avatar, tên, ngày, sao, nội dung, ảnh, reply của salon
- Ảnh trong review: click để xem fullscreen lightbox
- Infinite scroll trong danh sách review, mới nhất lên đầu
