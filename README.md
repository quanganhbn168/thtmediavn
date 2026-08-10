# THT MEDIA VN

Nền tảng Laravel 12 dùng chung cho website THT MEDIA VN. Dự án giữ kiến trúc CMS, quản trị người dùng, media, nội dung, catalog và commerce theo module để có thể bật rộng dần khi nghiệp vụ được chốt.

## Yêu cầu

- PHP 8.2+
- MySQL 8+
- Composer 2

## Cài đặt local

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Database local mặc định: `thtmediavn`. Database kiểm thử: `thtmediavn_testing`.

Seeder chỉ tạo dữ liệu nền THT MEDIA VN, vai trò/quyền và tài khoản quản trị khi `ADMIN_EMAIL` cùng `ADMIN_PASSWORD` đã được cấu hình trong `.env`. Không có sản phẩm, bài viết, thương hiệu, khách hàng hay đơn hàng mẫu.

## Kiểm thử

```bash
php artisan test
```

Các thông tin doanh nghiệp thực tế như địa chỉ, số điện thoại, email, mã số thuế và mạng xã hội được để trống, cần cập nhật tại trang quản trị trước khi triển khai production.
