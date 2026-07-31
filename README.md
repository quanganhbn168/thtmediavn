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
php artisan key:generate
php artisan migrate:fresh --seed
```

Tài khoản quản trị mẫu: `admin@example.com` (mật khẩu factory mặc định: `password`).

## Kiểm thử

Tạo database MySQL `bonglab_testing`, sau đó chạy:

```bash
php artisan test
```

Các asset giao diện chính nằm tại `public/assets/css/style.css`, `public/assets/js/app.js` và `public/assets/images`.
