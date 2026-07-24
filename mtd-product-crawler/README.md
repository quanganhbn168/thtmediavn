# MTD Product Crawler

Bộ PHP độc lập để thu thập dữ liệu sản phẩm công khai từ trang danh sách và trang chi tiết của `mtd-global.com`.

## Phạm vi

Crawler thu thập:

- URL nguồn và slug.
- Tên sản phẩm.
- Thương hiệu.
- SKU/mã sản phẩm.
- Loại sản phẩm.
- Tình trạng còn/hết hàng.
- Giá bán và giá gốc khi nhận diện được.
- Meta description.
- Nội dung mô tả khi trang có dữ liệu.
- Danh sách ảnh sản phẩm.
- Tải ảnh về máy khi bật tùy chọn và xác nhận quyền sử dụng.
- Xuất JSON, CSV.
- Import vào MySQL.
- Trang HTML/CSS/JS để kiểm tra dữ liệu.

## Yêu cầu

- PHP 8.1 trở lên.
- Extension `curl`.
- Extension `dom`.
- Extension `mbstring`.
- Extension `pdo_mysql` nếu dùng MySQL.

Kiểm tra:

```bash
php -m
```

## Chạy crawler

Chỉ lấy dữ liệu và URL ảnh:

```bash
php run.php --start=1 --end=41
```

Lấy dữ liệu và tải ảnh:

```bash
php run.php --start=1 --end=41 --download-images --confirm-rights
```

`--confirm-rights` là xác nhận anh đã được chủ sở hữu cho phép dùng/tái xuất bản ảnh và nội dung.

Chạy lại từ đầu:

```bash
php run.php --start=1 --end=41 --no-resume
```

Cập nhật lại nội dung sản phẩm đã có mà không xóa liên kết ảnh cục bộ:

```bash
php run.php --start=1 --end=41 --refresh-existing
```

Xem toàn bộ lệnh:

```bash
php run.php --help
```

## Kết quả

```text
storage/
├── products.json
├── products.csv
├── errors.log
└── images/
    └── ten-san-pham/
        ├── 01.jpg
        └── 02.jpg
```

Crawler lưu tăng dần sau mỗi sản phẩm nên có thể dừng giữa chừng rồi chạy lại.

## Xem dữ liệu bằng giao diện

Chạy PHP server tại thư mục gốc:

```bash
php -S 127.0.0.1:8080 -t public
```

Mở:

```text
http://127.0.0.1:8080
```

## Import MySQL

Tạo bảng:

```bash
mysql -u root -p ten_database < schema.sql
```

Import:

Linux/macOS:

```bash
DB_DATABASE=ten_database DB_USERNAME=root DB_PASSWORD=matkhau php import_mysql.php
```

Windows CMD:

```cmd
set DB_DATABASE=ten_database
set DB_USERNAME=root
set DB_PASSWORD=matkhau
php import_mysql.php
```

Windows PowerShell:

```powershell
$env:DB_DATABASE="ten_database"
$env:DB_USERNAME="root"
$env:DB_PASSWORD="matkhau"
php import_mysql.php
```

## Lưu ý kỹ thuật

Website có thể thay giao diện hoặc tên class HTML. Crawler không phụ thuộc hoàn toàn vào một class CSS duy nhất, nhưng nếu cấu trúc thay đổi lớn thì cần cập nhật:

- `extractProductCandidates()`
- `findProductDetailContainer()`
- `extractDescriptionHtml()`
- `extractImages()`

Giá và các trường văn bản được nhận diện theo nội dung tiếng Việt trên trang. Sau khi chạy nên kiểm tra ngẫu nhiên 20-30 sản phẩm trước khi import chính thức.

## Lưu ý quyền sử dụng

Việc một ảnh hoặc nội dung truy cập công khai không đồng nghĩa với quyền sao chép và tái xuất bản. Chỉ tải và đăng lại khi:

- Website là của anh/khách hàng của anh; hoặc
- Chủ sở hữu đã cho phép; hoặc
- Anh có nguồn dữ liệu/ảnh hợp lệ từ nhà phân phối, nhà sản xuất.

Không dùng crawler để vượt đăng nhập, CAPTCHA, giới hạn kỹ thuật hoặc truy cập dữ liệu riêng tư.

## Bản sửa lỗi ảnh liên quan

Bản này chỉ lấy ảnh nằm trong gallery chính:

```html
.product-image-block .gallery-top
```

Nó không còn quét ảnh trong các khối sản phẩm liên quan/nổi bật. URL thumbnail như
`/thumb/medium/`, `/thumb/large/`, `/thumb/1024x1024/` được chuẩn hóa về một URL ảnh gốc.

Nếu đã chạy bản cũ, không resume dữ liệu cũ vì JSON đã chứa ảnh sai. Trên Windows chỉ cần chạy:

```text
reset-and-run.bat
```

Script sẽ:

1. Sao lưu JSON cũ thành `storage/products-before-fix.json`.
2. Xóa JSON/CSV và thư mục ảnh bị lấy nhầm.
3. Chạy lại toàn bộ website bằng bộ lọc đã sửa.

Bản sửa còn lấy mô tả từ đúng tab `#tab-1` hoặc schema Product, thay vì lưu cả breadcrumb và form mua hàng. JSON cũng có thêm mảng `variants` cho các sản phẩm có lựa chọn kích thước/loại.
