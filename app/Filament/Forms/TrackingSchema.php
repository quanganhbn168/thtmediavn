<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;

class TrackingSchema
{
    public static function make(): array
    {
        return [
            Section::make('Google Tag Manager (GTM)')->description('Dán hai đoạn mã GTM vào đúng ô. Nếu đã cài GA4, Meta hoặc TikTok trong GTM, để trống ô cài trực tiếp tương ứng bên dưới.')->schema([
                self::code('google_tag_manager_head_code', 'GTM — mã script trong head', 'Dán nguyên đoạn <script> của Google Tag Manager. Hệ thống đặt ở đầu <head>.'),
                self::code('google_tag_manager_body_code', 'GTM — mã noscript sau body', 'Dán nguyên đoạn <noscript><iframe>...</iframe></noscript>. Hệ thống đặt ngay sau <body>.'),
            ])->collapsible(),
            Section::make('Google Analytics 4 (GA4)')->schema([
                self::code('google_analytics_code', 'GA4 — mã Google tag', 'Dán cả thẻ tải gtag.js và thẻ cấu hình. Đặt ở đầu <head>. Để trống nếu đã cấu hình GA4 qua GTM.'),
            ])->collapsible(),
            Section::make('Meta / Facebook Pixel')->schema([
                self::code('meta_pixel_code', 'Meta Pixel — mã đầy đủ', 'Dán nguyên mã Meta cung cấp. Script nằm trong <head>; phần <noscript> (nếu có) tự chuyển xuống sau <body>.'),
            ])->collapsible(),
            Section::make('TikTok Pixel')->schema([
                self::code('tiktok_pixel_code', 'TikTok Pixel — mã đầy đủ', 'Dán nguyên mã cơ sở TikTok Pixel, gồm thẻ <script>. Hệ thống đặt trong <head>.'),
            ])->collapsible(),
            Section::make('Mã bổ sung')->description('Dùng cho nền tảng khác. Không dán lại mã đã nhập ở các ô phía trên. Áp dụng cho các trang công khai, không chèn vào quản trị.')->schema([
                self::code('head_code', 'Mã bổ sung trước </head>', 'Ví dụ: mã xác minh hoặc script theo dõi khác.'),
                self::code('body_open_code', 'Mã bổ sung ngay sau <body>', 'Dùng khi nhà cung cấp yêu cầu vị trí đầu body.'),
                self::code('body_close_code', 'Mã bổ sung trước </body>', 'Ví dụ: tiện ích chat hoặc script cần đặt cuối trang.'),
            ])->collapsible()->collapsed(),
        ];
    }

    private static function code(string $name, string $label, string $help): Textarea
    {
        return Textarea::make($name)->label($label)->helperText($help)->rows(6)
            ->maxLength(100000)->columnSpanFull()
            ->extraInputAttributes(['class' => 'font-mono text-sm', 'spellcheck' => 'false']);
    }
}
