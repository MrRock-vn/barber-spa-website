# Bao cao tom tat du an Barber Spa

## 1. Gioi thieu

Barber Spa Booking Platform la ung dung web ho tro dat lich dich vu salon/barber spa. He thong phuc vu ba nhom nguoi dung: customer, owner va admin.

## 2. Muc tieu

- Customer tim salon, xem dich vu va dat lich nhanh.
- Owner quan ly salon, staff, service, booking va review.
- Admin quan tri user, salon, booking, payment, review va theo doi dashboard.
- He thong co xu ly tranh trung lich bang conflict check va hold slot tam thoi.

## 3. Cong nghe

- PHP thuan theo mo hinh MVC.
- MySQL/MariaDB.
- Bootstrap 5.
- Chart.js.
- VNPay sandbox.
- PHPMailer.

## 4. Phan quyen

### Customer

- Dang ky/dang nhap.
- Tim salon.
- Dat lich.
- Thanh toan.
- Xem lich hen.
- Review sau khi booking completed.
- Report review vi pham.

### Owner

- Dashboard salon.
- Quan ly staff/service.
- Quan ly booking.
- Xem va phan hoi review.
- Theo doi doanh thu va lich hen gan day.

### Admin

- Dashboard he thong.
- Quan ly users, salons, categories, bookings, reviews.
- Kiem duyet review: publish, flag, remove.

## 5. Luong nghiep vu chinh

### Booking

1. Customer chon salon.
2. Chon dich vu.
3. Chon nhan vien.
4. Chon ngay gio.
5. API `hold-slot.php` giu slot tam 10 phut.
6. Tao booking neu khong co conflict.
7. Thanh toan online hoac tai quay.

### Review

1. Chi booking co `status = completed` moi duoc review.
2. Moi booking chi co mot review.
3. Customer co the sua/xoa review cua minh.
4. Owner xem review salon va phan hoi.
5. Admin publish/flag/remove review.
6. Rating salon duoc tinh lai theo review published.

## 6. Co so du lieu chinh

- `users`: tai khoan va phan quyen.
- `salons`: thong tin salon.
- `services`: dich vu cua salon.
- `staff`: nhan vien.
- `bookings`: lich hen.
- `booking_holds`: giu slot tam thoi.
- `payments`: giao dich thanh toan.
- `reviews`: danh gia khach hang.
- `review_reports`: bao cao review vi pham.

## 7. Dashboard

### Admin Dashboard

- Tong user, salon, booking, revenue.
- Payment success, total payment.
- Tong review va review flagged.
- Bieu do booking 7 ngay.
- Bieu do doanh thu 6 thang.
- Top salon nhieu booking.
- Top dich vu duoc chon.
- Booking moi, payment moi.

### Owner Dashboard

- Tong booking, doanh thu, staff, service.
- Rating trung binh, so review.
- Booking sap toi va completed.
- Bieu do booking 7 ngay.
- Bieu do doanh thu 6 thang.
- Khung gio dong khach.
- Nhan vien va dich vu duoc dat nhieu.
- Booking/review gan day.

## 8. Bao mat

- CSRF token cho form.
- Prepared statements voi PDO.
- Hash password.
- Kiem tra role.
- Kiem tra ownership khi thao tac booking/review.
- Khong commit `.env`.
- Tach `.env.example`.

## 9. Ket qua dat duoc

- Hoan thien luong tim salon, dat lich, thanh toan sandbox va review.
- Co dashboard admin/owner voi so lieu truc quan.
- Co API autocomplete va hold slot.
- Co tai lieu cai dat, bao mat va kich ban demo.

## 10. Han che

- Upload anh review chua hoan thien.
- Dashboard chua co bo loc ngay/thang tuy bien nang cao.
- Notification realtime chua co.
- Video demo va screenshot can chuan bi rieng khi nop bao cao.

## 11. Huong phat trien

- Them calendar view cho owner.
- Them notification realtime.
- Them rate limit nang cao.
- Them upload anh review va gallery salon quan ly tu admin/owner.
