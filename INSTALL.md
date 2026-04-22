# INSTALL

## Yeu cau moi truong

- PHP 8.1+ khuyen nghi
- MySQL/MariaDB
- Apache hoac XAMPP
- Composer

## Cai dat

1. Dat source vao thu muc web server, vi du:

```text
c:\aiu\htdocs\barber-spa
```

2. Cai dependency:

```bash
composer install
```

3. Tao database MySQL:

```sql
CREATE DATABASE barber_spa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Import database:

```bash
mysql -u root -p barber_spa < database/schema.sql
```

Neu database da ton tai va chi can them chuc nang giu slot:

```bash
mysql -u root -p barber_spa < database/add-booking-holds.sql
```

5. Tao file `.env` tu `.env.example` va cau hinh:

```text
APP_URL=http://localhost/barber-spa
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=barber_spa
DB_USER=root
DB_PASS=
```

6. Mo trinh duyet:

```text
http://localhost/barber-spa
```

## Luu y truoc khi nop

- Khong nop `.env` that.
- Khong nop file `debug-*.php`, `test-*.php`, `fix-test-*.php`, `reset-all-passwords.php`.
- Kiem tra lai payment sandbox trong `.env`.
- Chay nhanh `php -l` voi cac file PHP neu co sua code.
