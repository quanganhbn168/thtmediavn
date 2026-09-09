<?php

namespace App\Filament\Resources\Intros\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class IntroForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Nội dung chính ────────────────────────────────────────
                Section::make('Nội dung')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Hidden::make('kind')->default('article'),
                        TextInput::make('title')->live(onBlur: true)->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                            if (! $get('slug') && $get('kind') === 'article') {
                                $set('slug', Str::slug($state ?? ''));
                            }
                        })
                            ->label('Tiêu đề')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('slug')->label('Đường dẫn bài giới thiệu')->helperText('Chữ thường, số và dấu gạch ngang. Bài có trang riêng, độc lập với giới thiệu trên trang chủ.')
                            ->maxLength(180)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true)
                            ->required(fn (Get $get): bool => $get('kind') === 'article')
                            ->visible(fn (Get $get): bool => $get('kind') === 'article'),
                        Textarea::make('summary')->label('Mô tả ngắn')->rows(3)->columnSpanFull(),
                        TextInput::make('subtitle')->visible(fn (Get $get): bool => $get('kind') === 'block')
                            ->label('Tiêu đề phụ')
                            ->maxLength(255),

                        TextInput::make('icon')->visible(fn (Get $get): bool => $get('kind') === 'block')
                            ->label('Icon (Font Awesome)')
                            ->placeholder('fa-solid fa-star')
                            ->maxLength(100)
                            ->helperText('Bỏ trống nếu dùng ảnh'),

                        FileUpload::make('image')
                            ->label('Ảnh minh họa')
                            ->image()
                            ->disk('public')
                            ->directory('upload/intros')
                            ->visibility('public')
                            ->imagePreviewHeight('180')
                            ->columnSpanFull(),

                        RichEditor::make('content')->fileAttachmentsDisk('public')->fileAttachmentsDirectory('intros/content')
                            ->label('Nội dung')
                            ->columnSpanFull(),    // full toolbar mặc định — không cần chỉ định
                    ])
                    ->columns(2),

                Section::make('SEO')->collapsible()->schema([
                    TextInput::make('meta_title')->label('Tiêu đề SEO')->maxLength(255),
                    Textarea::make('meta_description')->label('Mô tả SEO')->rows(3),
                    TextInput::make('keywords')->label('Từ khóa')->maxLength(255),
                ]),
                // ── Liên kết ──────────────────────────────────────────────
                Section::make('Xuất bản')
                    ->icon('heroicon-o-link')
                    ->schema([
                        TextInput::make('link')->visible(fn (Get $get): bool => $get('kind') === 'block')
                            ->label('URL')
                            ->url()
                            ->maxLength(500),

                        TextInput::make('link_text')->visible(fn (Get $get): bool => $get('kind') === 'block')
                            ->label('Nhãn nút')
                            ->placeholder('Xem thêm')
                            ->maxLength(100),

                        DateTimePicker::make('published_at')->label('Ngày xuất bản')->helperText('Để trống để đăng ngay khi bật Hiển thị; chọn ngày tương lai để hẹn giờ.'),
                        TextInput::make('sort_order')->label('Thứ tự')->numeric()->minValue(0)->default(0),
                        Toggle::make('is_active')
                            ->label('Hiển thị')
                            ->default(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

            ]);
    }
}
