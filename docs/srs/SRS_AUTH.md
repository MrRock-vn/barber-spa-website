# Software Requirement Specification (SRS)
## Chức năng: Xác thực Người dùng (Authentication)
**Mã chức năng:** AUTH-01  
**Trạng thái:** Hoàn thiện  
**Dự án:** Website Đặt Lịch Cắt Tóc & Làm Đẹp (Barber & Spa)

---

### 1. Mô tả tổng quan
Cung cấp cơ chế đăng ký, đăng nhập, đăng xuất và khôi phục mật khẩu cho tất cả các loại người dùng (Khách hàng, Chủ Salon, Admin). Đảm bảo bảo mật thông tin và phân quyền đúng vai trò.

### 2. Luồng nghiệp vụ

| Bước | Hành động người dùng | Phản hồi hệ thống |
|:---|:---|:---|
| 1 | Truy cập `/login` | Hiển thị form đăng nhập (Email, Password, Remember Me) |
| 2 | Nhập thông tin & nhấn "Đăng nhập" | Validate client-side & server-side |
| 3 | Hệ thống kiểm tra thông tin | So khớp email và Bcrypt hash trong DB |
| 4 | Xác thực thành công | Tạo session/token, redirect theo role |
| 5 | Xác thực thất bại | Hiển thị lỗi, xóa trường Password |

### 3. Yêu cầu dữ liệu

**Input Fields:**
- **Email:** string, định dạng hợp lệ, bắt buộc
- **Password:** string, tối thiểu 8 ký tự, ẩn ký tự, bắt buộc
- **Remember Me:** boolean, mặc định false

**Bảng `users`:**
- `id`, `email` (unique), `password` (hashed), `role` (admin/owner/customer)
- `is_active` (boolean), `last_login_at` (timestamp), `login_ip` (string)

### 4. Ràng buộc kỹ thuật & Bảo mật
- **HTTPS** bắt buộc trên toàn bộ luồng xác thực
- **CSRF Token** tích hợp trong mọi POST request
- **Bcrypt/Argon2** để hash mật khẩu, không lưu plaintext
- **Brute-force protection:** khóa IP/tài khoản sau 5 lần sai trong 1 phút
- **JWT Token** hoặc Session với thời hạn 24h (7 ngày nếu bật Remember Me)

### 5. Chức năng Đăng ký

| Bước | Hành động | Phản hồi |
|:---|:---|:---|
| 1 | Truy cập `/register` | Hiển thị form đăng ký |
| 2 | Nhập thông tin | Validate realtime (email trùng, password confirm) |
| 3 | Submit | Tạo tài khoản, gửi email xác nhận |
| 4 | Xác nhận email | Kích hoạt tài khoản |

### 6. Chức năng Quên mật khẩu

| Bước | Hành động | Phản hồi |
|:---|:---|:---|
| 1 | Nhấn "Quên mật khẩu" | Hiển thị form nhập email |
| 2 | Nhập email hợp lệ | Gửi link reset (hết hạn 15 phút) |
| 3 | Click link trong email | Form đặt lại mật khẩu mới |
| 4 | Nhập mật khẩu mới | Cập nhật DB, redirect login |

### 7. Edge Cases & Xử lý lỗi

| Trường hợp | Xử lý |
|:---|:---|
| Email sai định dạng | "Email không đúng định dạng" |
| Tài khoản bị khóa | "Tài khoản tạm thời bị đình chỉ. Liên hệ Admin." |
| CSRF token hết hạn | Redirect login + "Phiên làm việc hết hạn" |
| Email chưa xác nhận | "Vui lòng xác nhận email trước khi đăng nhập" |

### 8. Giao diện (UI/UX)
- Responsive (Desktop & Mobile)
- Nút đăng nhập hiển thị spinner khi đang xử lý
- Hỗ trợ phím Enter để submit form
- Redirect đúng trang theo role sau login
