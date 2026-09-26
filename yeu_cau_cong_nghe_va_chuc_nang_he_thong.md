# YÊU CẦU HỆ THỐNG QUẢN LÝ THƯ VIỆN

## 1. Tổng quan

Hệ thống Quản lý thư viện được xây dựng dưới dạng ứng dụng web nhằm hỗ
trợ việc quản lý sách, độc giả và nghiệp vụ mượn -- trả sách. Hệ thống
có 4 tác nhân:

-   **Khách (Guest):** chưa đăng nhập, có thể tìm kiếm/xem chi tiết
    sách, đăng ký và đăng nhập.
-   **Độc giả (User):** kế thừa các chức năng của Khách, đồng thời có
    thể mượn sách và xem lịch sử mượn của bản thân.
-   **Thủ thư/Quản trị viên (Admin):** quản lý sách, tác giả, thể loại,
    người dùng, phiếu mượn -- trả, xác nhận trả sách và xem thống kê.
-   **Hệ thống (System):** tiến trình tự động cập nhật trạng thái quá
    hạn của các phiếu mượn.

> Phạm vi hiện tại không có bước "đặt mượn -- duyệt phiếu". Khi độc giả
> thực hiện mượn và thỏa mãn các điều kiện nghiệp vụ, phiếu mượn được
> tạo ngay.

------------------------------------------------------------------------

## 2. Yêu cầu công nghệ

### 2.1. Backend

-   **Ngôn ngữ:** PHP 8.2 trở lên.
-   **Framework:** Laravel 12.
-   Sử dụng kiến trúc và các thành phần chuẩn của Laravel để tổ chức ứng
    dụng.
-   Sử dụng **Laravel Auth/Session** cho chức năng xác thực.
-   Sử dụng **Middleware** để kiểm soát quyền truy cập theo vai trò
    `user` và `admin`.
-   Sử dụng **Laravel Validation** để kiểm tra dữ liệu đầu vào.
-   Sử dụng **Laravel Scheduler** cho tiến trình tự động cập nhật phiếu
    mượn quá hạn.
-   Các nghiệp vụ làm thay đổi đồng thời phiếu mượn và số lượng sách
    phải sử dụng **transaction** để đảm bảo toàn vẹn dữ liệu.

### 2.2. Cơ sở dữ liệu

-   **Hệ quản trị CSDL:** MySQL.
-   **ORM:** Laravel Eloquent ORM.
-   Quản lý cấu trúc CSDL bằng **Migration**.
-   Có thể sử dụng **Seeder** để tạo dữ liệu ban đầu.
-   CSDL gồm 5 bảng chính:
    -   `users`
    -   `authors`
    -   `categories`
    -   `books`
    -   `borrow_records`
-   Tên bảng, tên cột và các giá trị enum phải được giữ thống nhất theo
    thiết kế, không tự ý đổi tên.

### 2.3. Frontend

-   **Template Engine:** Laravel Blade.
-   **UI Framework:** Bootstrap.
-   Giao diện cần hỗ trợ các form nhập liệu, danh sách dữ liệu, tìm
    kiếm, lọc và phân trang.
-   Các thông báo lỗi validation cần hiển thị rõ tại trường dữ liệu
    không hợp lệ.

### 2.4. Môi trường và công cụ phát triển

-   **Laragon:** môi trường cung cấp PHP, Composer và MySQL trong quá
    trình phát triển trên Windows.
-   **Visual Studio Code:** trình soạn thảo mã nguồn.
-   **Composer:** quản lý package PHP và cài đặt dependency của Laravel.
-   **Artisan CLI:** chạy migration, seeder, scheduler và các lệnh quản
    trị Laravel.
-   Công cụ UML có thể sử dụng **draw.io** hoặc **StarUML**.

> Website được chạy bằng Laravel Development Server tại
> `http://localhost:8000`; MySQL được cung cấp bởi Laragon.

------------------------------------------------------------------------

## 3. Yêu cầu chức năng

### 3.1. UC01 -- Tìm kiếm / xem chi tiết sách

**Actor:** Khách, Độc giả

Hệ thống cho phép người dùng:

-   Xem danh sách sách đang hoạt động theo dạng lưới có ảnh bìa.
-   Tìm kiếm sách theo tên sách hoặc `book_code`.
-   Lọc sách bằng dropdown tác giả và dropdown thể loại.
-   Có nút **Tìm kiếm** và **Bỏ lọc**.
-   Xem thông tin chi tiết của sách.
-   Hiển thị `book_code` thay cho ID nội bộ.
-   Chỉ hiển thị sách có `is_active = 1`.
-   Phân trang danh sách kết quả tìm kiếm.
-   Thông báo phù hợp khi không tìm thấy sách.

### 3.2. UC02 -- Đăng ký tài khoản

**Actor:** Khách

Hệ thống cho phép khách đăng ký tài khoản độc giả:

-   Nhập các thông tin tài khoản cần thiết.
-   Kiểm tra dữ liệu hợp lệ và email không bị trùng.
-   Mật khẩu được lưu dưới dạng đã băm.
-   Hệ thống tự sinh `user_code` duy nhất với tiền tố `DG`.
-   Người dùng không được tự nhập hoặc chỉnh sửa `user_code`.

### 3.3. UC03 -- Đăng nhập / Đăng xuất hệ thống

**Actor:** Khách, Độc giả, Admin

Hệ thống phải:

-   Cho phép đăng nhập bằng thông tin tài khoản hợp lệ.
-   Tạo phiên đăng nhập bằng session.
-   Phân quyền truy cập dựa trên `role`.
-   Chỉ tài khoản `admin` được truy cập các chức năng quản trị.
-   Từ chối đăng nhập với tài khoản có `status = 'locked'`.
-   Từ chối đăng nhập với tài khoản có `status = 'deleted'`.
-   Cho phép người dùng đăng xuất và hủy phiên đăng nhập.

### 3.4. UC04 -- Mượn sách

**Actor:** Độc giả

Hệ thống cho phép độc giả mượn sách khi đáp ứng đầy đủ các điều kiện:

-   Sách có `available > 0`.
-   Sách có `is_active = 1`.
-   Tài khoản không bị khóa.
-   Không có phiếu chưa trả cho cùng cuốn sách.
-   Độc giả đang có ít hơn 3 đầu sách chưa trả.
-   Thời hạn mượn mặc định là 14 ngày.
-   Phiếu được tạo ngay với `status = 'borrowing'`, không qua bước
    duyệt.
-   Sau khi mượn thành công, `available` của sách giảm 1.
-   Việc tạo phiếu và giảm số lượng khả dụng phải thực hiện trong cùng
    một transaction.

### 3.5. UC05 -- Xem lịch sử mượn sách

**Actor:** Độc giả

-   Độc giả chỉ được xem lịch sử mượn của chính mình.
-   Hiển thị mã sách `book_code`, tên sách, ngày mượn, hạn trả, ngày trả
    và trạng thái phiếu.
-   Hiển thị tiền phạt nếu có.
-   Không cho phép người dùng xem lịch sử của độc giả khác.

### 3.6. UC06 -- Trả sách

**Actor:** Admin

Admin thực hiện xác nhận trả sách:

-   Cập nhật `return_date` bằng ngày trả thực tế.
-   Chuyển `status` sang `returned`.
-   Tăng `available` của sách lên 1.
-   Nếu trả muộn, tính tiền phạt theo số ngày trễ.
-   Mức phạt mặc định: **2.000 đồng/ngày/cuốn**, được cấu hình trong hệ
    thống và không lưu trực tiếp trong CSDL.
-   Khi có tiền phạt chưa thu, `is_paid = 0`.
-   Khi Admin xác nhận đã thu tiền phạt, `is_paid = 1`.
-   Phiếu quá hạn, sách đã ẩn hoặc tài khoản bị khóa vẫn có thể được xác
    nhận trả.

### 3.7. UC07 -- Quản lý sách

**Actor:** Admin

Admin có thể:

-   Xem danh sách sách.
-   Thêm sách.
-   Xem chi tiết sách.
-   Sửa thông tin sách.
-   Tải lên hoặc thay đổi ảnh bìa JPG, JPEG, PNG, WEBP tối đa 2 MB.
-   Tìm theo từ khóa và lọc theo tác giả, thể loại, trạng thái.
-   Phân trang danh sách kết quả.
-   Nhập `book_code` khi thêm sách và hệ thống phải kiểm tra không
    trùng.
-   Quản lý tác giả và thể loại liên kết với sách.
-   Khi tạo sách mới, `available = total_quantity`.
-   Không cho phép `total_quantity` nhỏ hơn số bản đang được mượn.
-   Thao tác ẩn chỉ đặt `is_active = 0`, không xóa vật lý dữ liệu.
-   Có thể hiện lại sách bằng cách đặt `is_active = 1`.
-   Trạng thái sách chỉ thay đổi bằng nút **Ẩn sách/Hiện lại sách** tại
    danh sách quản trị; màn hình sửa chỉ cập nhật thông tin sách.

### 3.8. UC08 -- Quản lý tác giả / thể loại

**Actor:** Admin

Admin có thể:

-   Xem danh sách tác giả và thể loại.
-   Thêm tác giả/thể loại.
-   Sửa thông tin tác giả/thể loại.
-   Xóa tác giả/thể loại khi hợp lệ.
-   Không được xóa tác giả hoặc thể loại đang được ít nhất một sách tham
    chiếu, kể cả sách đã bị ẩn.
-   Tên thể loại cần được kiểm tra trùng theo ràng buộc dữ liệu của hệ
    thống.

### 3.9. UC09 -- Quản lý phiếu mượn -- trả

**Actor:** Admin

Admin có thể:

-   Xem danh sách toàn bộ phiếu mượn.
-   Xem thông tin độc giả và sách thông qua `user_code` và `book_code`.
-   Lọc phiếu theo trạng thái:
    -   `borrowing`
    -   `returned`
    -   `overdue`
-   Lọc các phiếu có tiền phạt chưa thu (`fine_amount > 0` và
    `is_paid = 0`).
-   Thực hiện thao tác xác nhận trả sách từ màn hình quản lý phiếu.

### 3.10. UC10 -- Quản lý người dùng

**Actor:** Admin

Admin có thể:

-   Xem danh sách người dùng.
-   Quản lý thông tin tài khoản.
-   Khóa tài khoản bằng `status = 'locked'`.
-   Vô hiệu hóa/xóa mềm tài khoản bằng `status = 'deleted'`.
-   Không xóa vật lý tài khoản để bảo toàn lịch sử giao dịch.
-   `user_code` được hệ thống tự sinh theo vai trò:
    -   `DGxxxx`: độc giả.
    -   `NVxxxx`: admin.
-   Không cho phép chỉnh sửa thủ công `user_code`.

### 3.11. UC11 -- Thống kê báo cáo

**Actor:** Admin

Hệ thống cung cấp các thông tin thống kê phục vụ quản lý, gồm:

-   Sách được mượn nhiều.
-   Số phiếu quá hạn.
-   Tổng tiền phạt.
-   Tiền phạt đã thu.
-   Tiền phạt chưa thu.
-   Các dữ liệu báo cáo cần sử dụng `book_code` và `user_code` để đối
    chiếu thay vì ID nội bộ.

### 3.12. UC12 -- Tự động cập nhật trạng thái quá hạn

**Actor:** Hệ thống

-   Sử dụng Laravel Scheduler.
-   Tiến trình chạy định kỳ mỗi ngày.
-   Quét các phiếu có:
    -   `status = 'borrowing'`
    -   `due_date < ngày hiện tại`
-   Tự động chuyển trạng thái các phiếu phù hợp sang `overdue`.
-   Không làm thay đổi các phiếu đã có `status = 'returned'`.

------------------------------------------------------------------------

## 4. Yêu cầu dữ liệu và quy tắc nghiệp vụ

### 4.1. Tài khoản

-   `user_code` là duy nhất và được tự động sinh.
-   `role` chỉ nhận:
    -   `user`
    -   `admin`
-   `status` chỉ nhận:
    -   `active`
    -   `locked`
    -   `deleted`
-   Mật khẩu phải được băm trước khi lưu.
-   Không hard delete tài khoản.

### 4.2. Sách

-   `book_code` là duy nhất.
-   `cover_image` lưu đường dẫn ảnh bìa và được phép để trống; khi trống
    giao diện sử dụng ảnh bìa mặc định.
-   Mỗi sách liên kết với một tác giả và một thể loại.
-   Luôn đảm bảo:

``` text
0 <= available <= total_quantity
```

-   Khi tạo mới:

``` text
available = total_quantity
```

-   Không hard delete sách; khi xóa đặt:

``` text
is_active = 0
```

### 4.3. Phiếu mượn

Trạng thái phiếu chỉ sử dụng đúng các giá trị:

``` text
borrowing
returned
overdue
```

Vòng đời chính:

``` text
borrowing -> returned
borrowing -> overdue -> returned
```

Các giá trị enum phải viết thường, đúng chính tả và không có khoảng
trắng thừa.

### 4.4. Giới hạn mượn sách

-   Một độc giả được mượn tối đa 3 đầu sách cùng lúc.
-   Không được mượn cùng một cuốn khi đã có phiếu `borrowing` hoặc
    `overdue` của cuốn đó.
-   Chỉ được mượn sách còn số lượng khả dụng.
-   Thời hạn mượn mặc định là 14 ngày.

### 4.5. Tiền phạt

Công thức:

``` text
fine_amount = số ngày trả trễ × đơn giá phạt/ngày
```

Đơn giá mặc định:

``` text
2.000 VNĐ/ngày/cuốn
```

Quy tắc `is_paid`:

``` text
fine_amount = 0                  -> is_paid = 1
fine_amount > 0 và chưa thu      -> is_paid = 0
đã thu tiền phạt                 -> is_paid = 1
```

------------------------------------------------------------------------

## 5. Yêu cầu phân quyền

  Chức năng                     Khách   Độc giả   Admin   Hệ thống
  ---------------------------- ------- --------- ------- ----------
  Tìm kiếm / xem sách             ✓        ✓        ✓    
  Đăng ký                         ✓                      
  Đăng nhập                       ✓        ✓        ✓    
  Đăng xuất                                ✓        ✓    
  Mượn sách                                ✓             
  Xem lịch sử mượn cá nhân                 ✓             
  Trả sách                                          ✓    
  Quản lý sách                                      ✓    
  Quản lý tác giả / thể loại                        ✓    
  Quản lý phiếu mượn -- trả                         ✓    
  Quản lý người dùng                                ✓    
  Thống kê báo cáo                                  ✓    
  Cập nhật phiếu quá hạn                                     ✓

------------------------------------------------------------------------

## 6. Yêu cầu Validation

Hệ thống phải kiểm tra dữ liệu phía server bằng Laravel Validation và
tuân thủ các ràng buộc CSDL, bao gồm:

-   Trường bắt buộc không được để trống.
-   Email phải đúng định dạng và không trùng.
-   `book_code` không được trùng.
-   Ảnh bìa chỉ chấp nhận JPG, JPEG, PNG hoặc WEBP và không vượt quá 2
    MB.
-   `user_code` không được chỉnh sửa thủ công.
-   Số lượng sách không được âm.
-   `available` không được vượt quá `total_quantity`.
-   Không được giảm `total_quantity` xuống thấp hơn số bản đang được
    mượn.
-   Không cho mượn quá 3 đầu sách.
-   Không cho mượn trùng sách chưa trả.
-   Không cho mượn sách hết số lượng khả dụng hoặc đã bị ẩn.
-   Không cho xóa tác giả/thể loại đang được sách tham chiếu.
-   Không cho tài khoản bị khóa hoặc đã xóa đăng nhập.

------------------------------------------------------------------------

## 7. Yêu cầu tổ chức mã nguồn

Các module được tách riêng để hạn chế xung đột khi phát triển:

``` text
Auth
├── AuthController
├── routes/auth.php
└── resources/views/auth

Books
├── BookController
├── routes/books.php
└── resources/views/books

Authors / Categories / Users
├── AuthorController
├── CategoryController
├── UserController
├── routes/authors.php
├── routes/categories.php
├── routes/users.php
└── resources/views/authors|categories|users

Borrow
├── BorrowController
├── routes/borrow.php
└── resources/views/borrow

History / Statistics
├── HistoryController
├── StatisticController
├── routes/history.php
├── routes/statistics.php
└── resources/views/history|statistics
```

Các file route module được `require` vào `routes/web.php`. Không tự ý
khai báo toàn bộ route trực tiếp trong `web.php`.

Tên route đặt theo module, ví dụ:

``` text
books.index
borrow.return
```

Các model dùng chung như `Book.php` và `User.php` cần được thống nhất
trước khi thay đổi quan hệ hoặc cấu trúc để tránh xung đột giữa các
module.

------------------------------------------------------------------------

## 8. Cài đặt và vận hành bằng Laragon

1.  Sao chép dự án vào `C:\laragon\www\qlthuvien`.
2.  Bật Apache và MySQL trong Laragon, sau đó tạo database trống tên
    `qlthuvien`.
3.  Mở Terminal tại thư mục dự án và cài dependency:

``` bash
composer install
```

4.  Sao chép `.env.example` thành `.env` và cấu hình kết nối:

``` env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qlthuvien
DB_USERNAME=root
DB_PASSWORD=
```

5.  Tạo key, liên kết thư mục ảnh, tạo bảng và nạp dữ liệu:

``` bash
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
```

6.  Chạy website bằng Terminal thứ nhất:

``` bash
php artisan serve --host=localhost --port=8000
```

Truy cập `http://localhost:8000`.

7.  Chạy tiến trình cập nhật quá hạn bằng Terminal thứ hai:

``` bash
php artisan schedule:work
```

Tài khoản demo:

-   Admin: `admin@example.com` / `12345678`.
-   Độc giả: `user@example.com` / `12345678`.
