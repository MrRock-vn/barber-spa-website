<?php
// ============================================================================
// docs/srs/login.php - Tài liệu SRS cho chức năng đăng nhập
// Người làm: Nguyễn Văn Quang
// ============================================================================
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRS - Chức năng Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; padding: 40px 0; background: #f8f9fa; }
        .container { max-width: 900px; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; border-bottom: 3px solid #667eea; padding-bottom: 10px; margin-bottom: 30px; }
        h2 { color: #764ba2; margin-top: 30px; margin-bottom: 15px; }
        h3 { color: #555; margin-top: 20px; }
        .feature-box { background: #f8f9fa; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0; }
        .requirement { background: #e7f3ff; padding: 15px; margin: 10px 0; border-radius: 5px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; color: #d63384; }
        table { width: 100%; margin: 20px 0; }
        table th { background: #667eea; color: white; padding: 12px; }
        table td { padding: 10px; border: 1px solid #ddd; }
        .badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; }
        .badge-high { background: #dc3545; color: white; }
        .badge-medium { background: #ffc107; color: black; }
        .badge-low { background: #28a745; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h1>📋 SRS - Đặc tả Yêu cầu Phần mềm</h1>
    <h2>Chức năng: Đăng nhập (AUTH-01)</h2>

    <div class="feature-box">
        <h3>📌 Mô tả chung</h3>
        <p>Chức năng đăng nhập cho phép người dùng xác thực danh tính và truy cập vào hệ thống Barber Spa. Hệ thống hỗ trợ 3 loại người dùng: Admin, Owner (chủ salon), và Customer (khách hàng).</p>
    </div>

    <h2>🎯 Yêu cầu chức năng</h2>

    <div class="requirement">
        <h3>FR-01: Form đăng nhập</h3>
        <p><strong>Mô tả:</strong> Hiển thị form đăng nhập với các trường email và mật khẩu</p>
        <p><strong>Input:</strong></p>
        <ul>
            <li>Email (bắt buộc, định dạng email hợp lệ)</li>
            <li>Mật khẩu (bắt buộc, tối thiểu 8 ký tự)</li>
            <li>Ghi nhớ đăng nhập (tùy chọn)</li>
        </ul>
        <p><strong>Output:</strong> Redirect đến trang tương ứng theo role</p>
        <p><strong>Độ ưu tiên:</strong> <span class="badge badge-high">Cao</span></p>
    </div>

    <div class="requirement">
        <h3>FR-02: Xác thực thông tin</h3>
        <p><strong>Mô tả:</strong> Kiểm tra email và mật khẩu có khớp với database</p>
        <p><strong>Quy trình:</strong></p>
        <ol>
            <li>Validate định dạng email</li>
            <li>Kiểm tra email tồn tại trong database</li>
            <li>Kiểm tra tài khoản có bị khóa không</li>
            <li>Verify mật khẩu bằng <code>password_verify()</code></li>
            <li>Tạo session nếu đăng nhập thành công</li>
        </ol>
        <p><strong>Độ ưu tiên:</strong> <span class="badge badge-high">Cao</span></p>
    </div>

    <div class="requirement">
        <h3>FR-03: Brute-force protection</h3>
        <p><strong>Mô tả:</strong> Bảo vệ tài khoản khỏi tấn công brute-force</p>
        <p><strong>Cơ chế:</strong></p>
        <ul>
            <li>Đếm số lần đăng nhập sai (lưu trong <code>login_attempts</code>)</li>
            <li>Khóa tài khoản 30 phút sau 5 lần nhập sai</li>
            <li>Reset counter khi đăng nhập thành công</li>
        </ul>
        <p><strong>Độ ưu tiên:</strong> <span class="badge badge-high">Cao</span></p>
    </div>

    <div class="requirement">
        <h3>FR-04: Remember Me</h3>
        <p><strong>Mô tả:</strong> Cho phép người dùng duy trì đăng nhập trong 7 ngày</p>
        <p><strong>Cơ chế:</strong></p>
        <ul>
            <li>Tạo token ngẫu nhiên 64 ký tự</li>
            <li>Lưu token vào bảng <code>remember_tokens</code></li>
            <li>Set cookie với thời hạn 7 ngày</li>
            <li>Auto-login khi phát hiện cookie hợp lệ</li>
        </ul>
        <p><strong>Độ ưu tiên:</strong> <span class="badge badge-medium">Trung bình</span></p>
    </div>

    <div class="requirement">
        <h3>FR-05: Redirect theo role</h3>
        <p><strong>Mô tả:</strong> Chuyển hướng người dùng đến trang phù hợp sau khi đăng nhập</p>
        <table>
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Redirect URL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>admin</td>
                    <td><code>/admin/dashboard</code></td>
                </tr>
                <tr>
                    <td>owner</td>
                    <td><code>/owner/dashboard</code></td>
                </tr>
                <tr>
                    <td>customer</td>
                    <td><code>/home</code></td>
                </tr>
            </tbody>
        </table>
        <p><strong>Độ ưu tiên:</strong> <span class="badge badge-high">Cao</span></p>
    </div>

    <h2>🔒 Yêu cầu bảo mật</h2>

    <div class="requirement">
        <h3>SEC-01: Password hashing</h3>
        <p>Mật khẩu phải được hash bằng <code>password_hash()</code> với thuật toán <code>PASSWORD_BCRYPT</code></p>
    </div>

    <div class="requirement">
        <h3>SEC-02: SQL Injection prevention</h3>
        <p>Sử dụng Prepared Statements cho tất cả query database</p>
    </div>

    <div class="requirement">
        <h3>SEC-03: Session security</h3>
        <p>Session phải được regenerate sau khi đăng nhập thành công</p>
    </div>

    <div class="requirement">
        <h3>SEC-04: Cookie security</h3>
        <p>Cookie phải có flag <code>httponly</code> và <code>samesite=Lax</code></p>
    </div>

    <h2>📊 Database Schema</h2>

    <h3>Bảng: users</h3>
    <table>
        <thead>
            <tr>
                <th>Cột</th>
                <th>Kiểu dữ liệu</th>
                <th>Mô tả</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>id</td>
                <td>INT PRIMARY KEY</td>
                <td>ID người dùng</td>
            </tr>
            <tr>
                <td>email</td>
                <td>VARCHAR(255) UNIQUE</td>
                <td>Email đăng nhập</td>
            </tr>
            <tr>
                <td>password</td>
                <td>VARCHAR(255)</td>
                <td>Mật khẩu đã hash</td>
            </tr>
            <tr>
                <td>role</td>
                <td>ENUM('admin','owner','customer')</td>
                <td>Vai trò người dùng</td>
            </tr>
            <tr>
                <td>is_active</td>
                <td>TINYINT(1)</td>
                <td>Trạng thái kích hoạt</td>
            </tr>
            <tr>
                <td>login_attempts</td>
                <td>INT DEFAULT 0</td>
                <td>Số lần đăng nhập sai</td>
            </tr>
            <tr>
                <td>locked_until</td>
                <td>DATETIME NULL</td>
                <td>Thời gian khóa tài khoản</td>
            </tr>
            <tr>
                <td>last_login_at</td>
                <td>DATETIME NULL</td>
                <td>Lần đăng nhập cuối</td>
            </tr>
            <tr>
                <td>login_ip</td>
                <td>VARCHAR(45) NULL</td>
                <td>IP đăng nhập cuối</td>
            </tr>
        </tbody>
    </table>

    <h3>Bảng: remember_tokens</h3>
    <table>
        <thead>
            <tr>
                <th>Cột</th>
                <th>Kiểu dữ liệu</th>
                <th>Mô tả</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>id</td>
                <td>INT PRIMARY KEY</td>
                <td>ID token</td>
            </tr>
            <tr>
                <td>user_id</td>
                <td>INT FOREIGN KEY</td>
                <td>ID người dùng</td>
            </tr>
            <tr>
                <td>token</td>
                <td>VARCHAR(64) UNIQUE</td>
                <td>Remember token</td>
            </tr>
            <tr>
                <td>expires_at</td>
                <td>DATETIME</td>
                <td>Thời gian hết hạn</td>
            </tr>
        </tbody>
    </table>

    <h2>🧪 Test Cases</h2>

    <div class="requirement">
        <h3>TC-01: Đăng nhập thành công</h3>
        <p><strong>Input:</strong> Email và password đúng</p>
        <p><strong>Expected:</strong> Redirect đến dashboard tương ứng</p>
    </div>

    <div class="requirement">
        <h3>TC-02: Email không tồn tại</h3>
        <p><strong>Input:</strong> Email không có trong database</p>
        <p><strong>Expected:</strong> Hiển thị lỗi "Email hoặc mật khẩu không đúng"</p>
    </div>

    <div class="requirement">
        <h3>TC-03: Mật khẩu sai</h3>
        <p><strong>Input:</strong> Email đúng, password sai</p>
        <p><strong>Expected:</strong> Tăng login_attempts, hiển thị lỗi</p>
    </div>

    <div class="requirement">
        <h3>TC-04: Tài khoản bị khóa</h3>
        <p><strong>Input:</strong> Nhập sai 5 lần liên tiếp</p>
        <p><strong>Expected:</strong> Khóa tài khoản 30 phút</p>
    </div>

    <div class="requirement">
        <h3>TC-05: Remember Me</h3>
        <p><strong>Input:</strong> Đăng nhập với checkbox "Ghi nhớ" được chọn</p>
        <p><strong>Expected:</strong> Tạo cookie, auto-login lần sau</p>
    </div>

    <h2>📝 Ghi chú</h2>
    <ul>
        <li>File này được tạo bởi: <strong>Nguyễn Văn Quang</strong></li>
        <li>Ngày tạo: <strong>03/04/2026</strong></li>
        <li>Branch: <strong>feature/auth</strong></li>
        <li>Commit: <strong>415fb16</strong></li>
    </ul>

    <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #ddd; text-align: center; color: #666;">
        <p>&copy; 2026 Barber Spa - Tài liệu SRS</p>
    </div>
</div>
</body>
</html>
