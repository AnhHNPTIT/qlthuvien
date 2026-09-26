# Website Quản lý thư viện

Website quản lý thư viện được xây dựng bằng Laravel 12, Blade, Bootstrap và MySQL. Hệ thống hỗ trợ khách, độc giả, quản trị viên và tiến trình tự động cập nhật phiếu quá hạn.

## Công nghệ sử dụng

- PHP 8.2 trở lên.
- Laravel 12.
- MySQL 8 hoặc MariaDB tương thích.
- Blade Template Engine và Bootstrap 5.
- Laravel Session Authentication, Middleware, Validation và Scheduler.
- Composer và Artisan CLI.

Ứng dụng không sử dụng React, Vue hoặc framework SPA.

## Chức năng chính

### Khách và độc giả

- Xem sách đang hoạt động theo dạng lưới có ảnh bìa.
- Tìm theo tên hoặc mã sách; lọc theo tác giả và thể loại.
- Xem chi tiết sách, đăng ký, đăng nhập và đăng xuất.
- Độc giả được mượn sách trực tiếp khi đáp ứng các quy tắc nghiệp vụ.
- Xem lịch sử mượn, hạn trả, trạng thái và tiền phạt của chính mình.

### Quản trị viên

- Dashboard thống kê sách, phiếu mượn và tiền phạt.
- Quản lý sách, ảnh bìa, tác giả, thể loại và người dùng.
- Tìm sách theo từ khóa, tác giả, thể loại và trạng thái.
- Ẩn hoặc hiện lại sách mà không xóa dữ liệu vật lý.
- Quản lý phiếu mượn, xác nhận trả sách và thu tiền phạt.

### Hệ thống

- Scheduler chạy hằng ngày để chuyển phiếu `borrowing` đã quá hạn sang `overdue`.
- Nghiệp vụ mượn/trả sử dụng database transaction và row lock.

## Cài đặt bằng Laragon

### 1. Đặt mã nguồn vào Laragon

Sao chép thư mục dự án vào:

```text
C:\laragon\www\qlthuvien
```

### 2. Cài dependency

Mở **Terminal** trong Laragon, sau đó chạy:

```bash
cd C:\laragon\www\qlthuvien
composer install
```

### 3. Tạo database và cấu hình môi trường

Bật **Apache** và **MySQL** trong Laragon. Dùng HeidiSQL hoặc phpMyAdmin tạo database trống:

```text
qlthuvien
```

Sao chép file môi trường:

```bat
copy .env.example .env
```

Cập nhật kết nối MySQL trong `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qlthuvien
DB_USERNAME=root
DB_PASSWORD=
```

Nếu MySQL của Laragon có mật khẩu, điền mật khẩu tương ứng vào `DB_PASSWORD`.

### 4. Tạo application key

```bash
php artisan key:generate
```

### 5. Tạo liên kết lưu ảnh bìa

```bash
php artisan storage:link
```

Ảnh tải lên được lưu trong `storage/app/public/book-covers` và truy cập thông qua `public/storage`.

### 6. Tạo bảng và nạp dữ liệu demo

```bash
php artisan migrate --seed
```

Nếu cần xóa toàn bộ dữ liệu hiện có và tạo lại từ đầu:

```bash
php artisan migrate:fresh --seed
```

### 7. Chạy website

```bash
php artisan serve --host=localhost --port=8000
```

Giữ Terminal hoạt động và truy cập:

```text
http://localhost:8000
```

### 8. Chạy Scheduler

Mở Terminal thứ hai tại thư mục dự án và chạy:

```bash
php artisan schedule:work
```

Giữ Terminal này hoạt động để hệ thống tự động cập nhật phiếu quá hạn.

## Tài khoản demo

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Admin | `admin@example.com` | `12345678` |
| Độc giả | `user@example.com` | `12345678` |

Seeder tạo 1 admin, 5 độc giả, 5 tác giả, 5 thể loại, 18 sách và một số phiếu mượn mẫu.

## Cấu hình nghiệp vụ

Các cấu hình nằm trong `config/library.php`:

- Thời hạn mượn: 14 ngày.
- Số đầu sách được mượn đồng thời: 3.
- Tiền phạt mặc định: 2.000 VNĐ/ngày/cuốn.

Có thể thay đổi mức phạt trong `.env`:

```env
LIBRARY_FINE_PER_DAY=2000
```

## Các lệnh hữu ích

```bash
php artisan route:list
php artisan library:mark-overdue
php artisan test
./vendor/bin/pint --test
```

## Cấu trúc route

- `/books`: trang sách phía client.
- `/login`, `/register`: xác thực.
- `/history`: lịch sử mượn của độc giả.
- `/admin/statistics`: dashboard quản trị.
- `/admin/books`: quản lý sách.
- `/admin/authors`: quản lý tác giả.
- `/admin/categories`: quản lý thể loại.
- `/admin/users`: quản lý người dùng.
- `/admin/borrow-records`: quản lý mượn – trả.
