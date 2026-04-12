# Software Requirement Specification (SRS)

## Chức năng: Xác thực Người dùng (Authentication)

- **Mã chức năng:** AUTH-01
- **Trạng thái:** Hoàn thiện
- **Dự án:** Barber Spa (PHP thuần + MySQL + Bootstrap 5 + MVC)

---

## 1. Mô tả tổng quan

Chức năng xác thực cho phép người dùng đăng ký, đăng nhập, đăng xuất, ghi nhớ đăng nhập, xác nhận email và khôi phục mật khẩu.

Hệ thống hỗ trợ 3 vai trò:

- **Admin**
- **Chủ Salon (Owner)**
- **Khách hàng (Customer)**

Mục tiêu:

- bảo vệ tài khoản người dùng
- phân quyền đúng vai trò
- hỗ trợ khôi phục truy cập an toàn
- ngăn chặn brute-force và giả mạo request

---

## 2. Phạm vi chức năng

Module AUTH-01 bao gồm:

- Đăng nhập
- Đăng ký
- Đăng xuất
- Remember Me
- Xác nhận email
- Quên mật khẩu
- Đặt lại mật khẩu
- Khóa tạm thời khi đăng nhập sai nhiều lần
- Redirect theo vai trò

---

## 3. Luồng nghiệp vụ đăng nhập

| Bước | Hành động người dùng                            | Phản hồi hệ thống                                           |
| ---- | ----------------------------------------------- | ----------------------------------------------------------- |
| 1    | Truy cập `/login`                               | Hiển thị form đăng nhập                                     |
| 2    | Nhập email, password, chọn Remember Me (nếu có) | Validate client-side và server-side                         |
| 3    | Nhấn nút đăng nhập                              | Hệ thống kiểm tra dữ liệu                                   |
| 4    | Thông tin hợp lệ                                | So khớp email và mật khẩu hash trong DB                     |
| 5    | Đăng nhập thành công                            | Tạo session, lưu remember token nếu cần, redirect theo role |
| 6    | Đăng nhập thất bại                              | Hiển thị lỗi, không lưu password trên form                  |

---

## 4. Luồng nghiệp vụ đăng ký

| Bước | Hành động người dùng                            | Phản hồi hệ thống                                 |
| ---- | ----------------------------------------------- | ------------------------------------------------- |
| 1    | Truy cập `/register`                            | Hiển thị form đăng ký                             |
| 2    | Nhập họ tên, email, mật khẩu, xác nhận mật khẩu | Validate dữ liệu                                  |
| 3    | Submit form                                     | Kiểm tra email trùng, độ mạnh mật khẩu            |
| 4    | Dữ liệu hợp lệ                                  | Tạo tài khoản mới với role mặc định là `customer` |
| 5    | Gửi email xác nhận                              | Sinh token xác thực email                         |
| 6    | Người dùng bấm link xác nhận                    | Kích hoạt email và cho phép đăng nhập đầy đủ      |

---

## 5. Luồng quên mật khẩu

| Bước | Hành động người dùng | Phản hồi hệ thống                    |
| ---- | -------------------- | ------------------------------------ |
| 1    | Chọn “Quên mật khẩu” | Hiển thị form nhập email             |
| 2    | Nhập email hợp lệ    | Hệ thống kiểm tra email tồn tại      |
| 3    | Submit               | Sinh reset token có hạn 15 phút      |
| 4    | Gửi link reset       | Gửi email chứa link đặt lại mật khẩu |
| 5    | Người dùng mở link   | Hiển thị form nhập mật khẩu mới      |
| 6    | Submit mật khẩu mới  | Cập nhật mật khẩu và xóa token cũ    |

---

## 6. Phân quyền và điều hướng sau đăng nhập

Sau khi đăng nhập thành công, hệ thống phải điều hướng như sau:

- **Admin** → `/admin/dashboard`
- **Owner** → `/owner/dashboard`
- **Customer** → `/home`

Nếu người dùng không có quyền truy cập vào route bị hạn chế, hệ thống trả về:

- `403 Forbidden`
- hoặc redirect về trang phù hợp theo vai trò

---

## 7. Yêu cầu dữ liệu

### 7.1 Input đăng nhập

- **Email**
  - kiểu dữ liệu: string
  - bắt buộc
  - đúng định dạng email
- **Password**
  - kiểu dữ liệu: string
  - bắt buộc
  - tối thiểu 8 ký tự
- **Remember Me**
  - kiểu dữ liệu: boolean
  - không bắt buộc
  - mặc định: false

### 7.2 Input đăng ký

- **Name**
  - bắt buộc
  - độ dài 2-150 ký tự
- **Email**
  - bắt buộc
  - unique
  - đúng định dạng
- **Password**
  - bắt buộc
  - ít nhất 8 ký tự
  - phải có chữ hoa, chữ thường và số
- **Password Confirmation**
  - bắt buộc
  - phải trùng với password

### 7.3 Input quên mật khẩu

- **Email**
  - bắt buộc
  - đúng định dạng
  - tồn tại trong hệ thống

### 7.4 Input đặt lại mật khẩu

- **Token**
  - bắt buộc
  - hợp lệ
  - chưa hết hạn
- **New Password**
  - bắt buộc
  - đủ mạnh
- **Confirm Password**
  - bắt buộc
  - trùng với mật khẩu mới

---

## 8. Bảng dữ liệu liên quan

### Bảng `users`

Các cột sử dụng chính:

- `id`
- `name`
- `email`
- `password`
- `role`
- `is_active`
- `ban_reason`
- `login_attempts`
- `locked_until`
- `last_login_at`
- `login_ip`
- `email_verified_at`
- `email_token`
- `reset_token`
- `reset_token_expires`
- `remember_token`
- `created_at`
- `updated_at`

### Bảng `password_resets` (nếu dùng riêng)

- `id`
- `email`
- `token`
- `created_at`

---

## 9. Ràng buộc nghiệp vụ

### 9.1 Đăng nhập

- Chỉ tài khoản đang hoạt động mới được đăng nhập
- Nếu tài khoản bị ban thì không cho đăng nhập
- Nếu email chưa xác nhận, có thể chặn đăng nhập hoặc yêu cầu xác nhận trước
- Password phải được kiểm tra bằng `password_verify()`

### 9.2 Brute-force protection

- Nếu đăng nhập sai từ 5 lần trở lên trong 1 phút:
  - khóa tạm tài khoản hoặc khóa theo email/IP
  - hiển thị thông báo phù hợp
- Trong thời gian bị khóa, không cho thử đăng nhập tiếp

### 9.3 Remember Me

- Nếu bật Remember Me:
  - sinh token ngẫu nhiên
  - lưu token vào DB
  - lưu cookie trong 7 ngày
- Khi quay lại website:
  - nếu cookie hợp lệ, tự khôi phục phiên đăng nhập

### 9.4 Đăng ký

- Email không được trùng
- Password và confirm password phải khớp
- Role mặc định khi đăng ký công khai là `customer`
- Owner/Admin không được tự đăng ký công khai bằng form customer

### 9.5 Quên mật khẩu

- Reset token hết hạn sau 15 phút
- Mỗi token chỉ dùng được 1 lần
- Sau khi đổi mật khẩu thành công:
  - xóa token reset
  - vô hiệu các token reset cũ

---

## 10. Yêu cầu bảo mật

- Tất cả form POST phải có **CSRF token**
- Password phải được hash bằng:
  - `PASSWORD_BCRYPT`
  - hoặc cơ chế hash an toàn tương đương
- Không lưu plaintext password
- Không hiển thị lỗi SQL hoặc stack trace cho người dùng cuối
- Escape toàn bộ output để chống XSS
- Dùng prepared statement để chống SQL Injection
- Cookie Remember Me nên có:
  - HttpOnly
  - SameSite hợp lý
- Ở môi trường production nên dùng HTTPS

---

## 11. Xử lý lỗi và edge cases

| Trường hợp                 | Cách xử lý                                |
| -------------------------- | ----------------------------------------- |
| Email sai định dạng        | Báo “Email không đúng định dạng”          |
| Password quá yếu           | Báo lỗi về độ mạnh mật khẩu               |
| Email đã tồn tại           | Báo “Email đã được sử dụng”               |
| Tài khoản bị khóa tạm thời | Báo rõ thời gian hoặc yêu cầu thử lại sau |
| Tài khoản bị ban           | Báo không thể đăng nhập                   |
| Email chưa xác nhận        | Yêu cầu xác nhận email trước              |
| CSRF token sai/hết hạn     | Từ chối request và yêu cầu tải lại form   |
| Token reset hết hạn        | Báo link không còn hiệu lực               |
| Token verify không hợp lệ  | Báo xác nhận email thất bại               |

---

## 12. UI/UX yêu cầu

- Responsive trên mobile và desktop
- Form đăng nhập/đăng ký rõ ràng, dễ dùng
- Password input có thể ẩn/hiện
- Nút submit có loading state
- Hiển thị flash message sau mỗi thao tác
- Hỗ trợ phím Enter để submit form
- Nếu đăng nhập lỗi:
  - giữ lại email
  - không giữ lại password

---

## 13. Tiêu chí hoàn thành

Module AUTH-01 được coi là hoàn thành khi:

- Đăng nhập đúng hoạt động
- Đăng nhập sai báo lỗi đúng
- Có khóa tạm sau 5 lần sai trong 1 phút
- Remember Me hoạt động
- Đăng ký tạo tài khoản mới thành công
- Email xác nhận hoạt động
- Reset password hoạt động với token 15 phút
- Redirect đúng theo role
- Toàn bộ form POST có CSRF token
- Password được hash an toàn

---

## 14. Route dự kiến

- `/login`
- `/register`
- `/logout`
- `/forgot-password`
- `/reset-password`
- `/verify-email`

---

## 15. Ghi chú triển khai cho dự án PHP thuần

Trong project `barber-spa`, module này sẽ được triển khai qua:

- `controllers/AuthController.php`
- `models/User.php`
- `views/auth/login.php`
- `views/auth/register.php`
- `views/auth/forgot-password.php`
- `views/auth/reset-password.php`
- `core/Auth.php`
- `core/helpers.php`
