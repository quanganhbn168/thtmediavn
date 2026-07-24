# RHEA SKINLAB

Website thương mại điện tử bằng Laravel 12, Blade và Bootstrap 5. Hệ thống chỉ sử dụng tiếng Việt.

## Chức năng chính

- Giao diện responsive: trang chủ, catalog có lọc, chi tiết sản phẩm, tin tức, giới thiệu và liên hệ.
- Sản phẩm, danh mục cha/con, thương hiệu, thuộc tính, giá trị và biến thể theo SKU/tồn kho.
- Flash Sale, coupon theo điều kiện, giỏ hàng khách/session, wishlist và review đã mua hàng.
- Đăng ký/đăng nhập khách hàng, sổ địa chỉ, checkout COD/chuyển khoản và lịch sử đơn hàng.
- Admin quản lý catalog, khuyến mãi, đơn hàng, review, CMS, media và phân quyền.

## Cài đặt

```bash
composer install
npm install
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
```

Tài khoản quản trị mẫu: `admin@example.com` (mật khẩu factory mặc định: `password`).

## Kiểm thử

Tạo database MySQL `bonglab_testing`, sau đó chạy:

```bash
php artisan test
```

Các asset giao diện chính nằm tại `public/assets/css/style.css`, `public/assets/js/app.js` và `public/assets/images`.

## Đồng bộ sản phẩm MTD

Dữ liệu nguồn nằm tại `mtd-product-crawler/storage/products.json`. Luôn phân tích trước:

```bash
php artisan mtd:import --dry-run
```

Nhập sản phẩm và ảnh, đồng thời liên kết các sản phẩm đã có cùng slug:

```bash
php artisan mtd:import --with-images --adopt-existing
```

Các sản phẩm tạo mới luôn ở trạng thái `draft`, không hoạt động, tồn kho bằng `0` và không cho đặt trước. Importer không ghi đè tồn kho hoặc ảnh thủ công của sản phẩm được liên kết. Có thể chạy lại cùng lệnh để cập nhật giá/nguồn/ảnh mà không sinh bản ghi trùng.

Tùy chọn hữu ích:

```bash
php artisan mtd:import --limit=10 --with-images
php artisan mtd:import --only=slug-san-pham-1,slug-san-pham-2
php artisan mtd:import --refresh-content
```

Crawler có thể cập nhật lại các URL đã lưu bằng:

```bash
php mtd-product-crawler/run.php --start=1 --end=41 --refresh-existing
```
