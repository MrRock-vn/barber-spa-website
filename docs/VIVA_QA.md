# Cau hoi van dap goi y

## 1. Vi sao chon MVC?

MVC giup tach ro 3 phan:

- Model xu ly database va nghiep vu du lieu.
- Controller dieu phoi request, validate va goi model/view.
- View hien thi giao dien.

Cach tach nay giup code de bao tri, de test luong chuc nang va tranh viet SQL truc tiep trong giao dien.

## 2. Route hoat dong the nao?

File `index.php` lay `path` tu URL, so khop voi bang route regex, require controller tuong ung va goi method. Route admin/owner/customer duoc tach theo folder controller.

## 3. Chong SQL injection bang cach nao?

Du an dung PDO prepared statements. Du lieu nguoi dung khong noi truc tiep vao SQL ma bind qua placeholder nhu `:id`, `:status`, `:keyword`.

## 4. Chong XSS bang cach nao?

View dung helper `e()` de escape output bang `htmlspecialchars`. Cac noi dung tu database/user input khi in ra HTML duoc escape.

## 5. CSRF duoc xu ly o dau?

Form co `csrfInput()`, server kiem tra bang `verifyCsrf()` truoc khi thuc hien thao tac POST nhu tao booking, huy booking, review, admin action.

## 6. Dang nhap co bao mat gi?

- Mat khau luu bang password hash.
- Khi login goi `session_regenerate_id(true)`.
- Session cookie HTTP-only va `SameSite=Lax`.
- Controller kiem tra role bang `Auth::requireRole()`.

## 7. Lam sao tranh dat trung slot?

He thong co 2 lop:

- `hasStaffConflict()` kiem tra booking da ton tai trong cung khoang thoi gian.
- `hasHeldConflict()` kiem tra slot dang duoc giu tam trong `booking_holds`.

Khi tao booking, model dung transaction va lock staff row bang `SELECT ... FOR UPDATE` de tranh race condition.

## 8. Vi sao can hold slot?

Neu hai nguoi cung chon mot gio, nguoi dau tien can duoc giu slot trong 10 phut de hoan tat booking/thanh toan. Slot het han thi tu duoc giai phong boi dieu kien `expires_at < NOW()`.

## 9. Staff schedule/day off duoc ap dung the nao?

API get-slots chi tra slot neu:

- staff dang active
- staff co lich lam viec trong ngay do
- staff khong co day off
- slot khong nam trong qua khu/qua gan hien tai
- slot khong bi booking/hold conflict

## 10. Payment verify chu ky nhu the nao?

VNPay return/IPN dung secret key de tinh HMAC va so sanh bang `hash_equals()`. Neu chu ky sai thi tu choi xu ly giao dich.

## 11. Lam sao khong thanh toan lai booking da paid?

Payment controller kiem tra `payment_status = paid` va payment success da ton tai truoc khi tao link thanh toan moi. Cac return/IPN cung co check idempotent bang transaction id da processed.

## 12. Review bi chan sai dieu kien nhu the nao?

Review controller kiem tra:

- user phai so huu booking
- booking phai `completed`
- booking chi co toi da 1 review
- rating 1-5
- content 10-1000 ky tu
- edit chi trong 24 gio

## 13. Owner va admin khac nhau o dau?

- Owner chi quan ly salon cua minh: booking, service, staff, review cua salon do.
- Admin quan tri toan he thong: user, salon, category, booking, review, dashboard tong.

## 14. Neu owner truy cap salon nguoi khac thi sao?

Controller owner lay salon theo `owner_id = Auth::id()` va kiem tra ownership truoc khi thao tac. Neu khong dung owner thi tra 403 hoac redirect.

## 15. Diem noi bat cua du an la gi?

- Hold slot 10 phut.
- Dashboard co chart va so lieu that.
- Review moderation: report, owner reply, admin publish/flag/remove.
- Autocomplete salon/service.
- Staff schedule/day off khi lay slot.
