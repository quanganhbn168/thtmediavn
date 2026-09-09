# Bộ chuẩn cài đặt website, bài giới thiệu và menu

## Quyền sở hữu nội dung

- Cài đặt website là nơi chỉnh nhận diện, doanh nghiệp/liên hệ, trang chủ, trang Giới thiệu, SEO và tracking. Các tab phụ theo tính năng của website được giữ lại.
- Trang chủ và trang Giới thiệu có nội dung, hình ảnh và chỉ số riêng. Không lấy nội dung giới thiệu từ mô tả SEO hoặc từ nhóm của trang còn lại.
- `intros` chứa bài giới thiệu có tiêu đề, slug, mô tả ngắn, ảnh, nội dung, SEO, trạng thái và ngày xuất bản. `kind=article` là bài có URL `/bai-gioi-thieu/{slug}`. Bản nháp và bài hẹn giờ chưa đến ngày không có trong menu/sitemap và trả 404 ở frontend.
- `truyenthongtht` giữ `kind=block` cho các USP cũ; migration sao chép điểm nổi bật sang HomepageSettings một lần. Nội dung công ty cũ của `thtmediavn` giữ nguyên bảng và URL.
- Ảnh/chỉ số từng dùng chung được sao chép một lần bởi migration, không liên kết động trở lại trang chủ.

## Bộ dựng menu

Lấy MenuResource, các page/concern và view nguồn menu của thtmedia-laravel làm chuẩn giao diện. Nguồn nội dung bên trái; cấu trúc kéo thả bên phải; nút vào trong/ra ngoài đổi cấp. Có thể quản lý đến 5 cấp, giữ cả cấp con khi sửa cha. Nguồn được kiểm tra lại trên server; mỗi nhóm trả tối đa 50 kết quả và ô tìm kiếm tìm trong toàn bộ dữ liệu.

Giữ nguyên bảng và khóa ngoại của website khi áp dụng:

- thtmedia-laravel: Menu/MenuItem native, `label`, `position`, `linked_source_type/id`.
- thtmediavn: Menu/MenuItem đa ngôn ngữ, `title`, `sort_order`; thêm tham chiếu nội dung, giữ URL/route cũ và cache SiteChromeCache.
- truyenthongtht: ManagedMenu/ManagedMenuItem là adapter trên bảng của plugin; giữ menu_locations, morph references và thứ tự cũ. Không chép schema native đè lên bảng plugin.

## Lưu cài đặt

Dùng Form/Actions chuẩn Filament, nút lưu cố định, Ctrl/Cmd+S và Hủy thay đổi. PreservesUnchangedSettings giữ nguyên nội dung không được sửa và các ngôn ngữ không xuất hiện trong form. Các trường upload có dehydration riêng phải được khai báo trong customDehydratedSettingsFields để giữ đúng quy trình upload.

## Áp dụng cho website tiếp theo

1. Đối chiếu bảng/route/media/settings của website đích trước khi chép module.
2. Chép Intro model, migration tạo mới (không dùng migration mở rộng bảng cũ), resource, controller, view, route và phần bổ sung sitemap.
3. Chép UI menu và viết adapter nguồn/quan hệ theo schema đích. Giữ nguyên nội dung và URL đang hoạt động.
4. Chép concern lưu cài đặt, tách HomepageSettings và AboutSettings; dùng migration sao chép một lần nếu dữ liệu đang dùng chung.
5. Chạy regression WebsiteStandardTest với database local có migration và tài khoản quản trị. Test dùng transaction và rollback; không dùng migrate:fresh trên dữ liệu thật.

## Triển khai

Sau khi kéo code: `php artisan migrate --force`, `php artisan filament:clear-cached-components`, `php artisan optimize:clear`, build asset bằng package manager của repo rồi `php artisan view:cache`. Tạo lại sitemap tĩnh nếu server đang phục vụ file sitemap.xml. Không chạy seed ghi đè nội dung.

Kiểm tra local: `php vendor/bin/phpunit --no-configuration --bootstrap vendor/autoload.php tests/Feature/WebsiteStandardTest.php`.
