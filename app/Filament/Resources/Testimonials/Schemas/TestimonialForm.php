<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nội dung đánh giá')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->schema([
                    TextInput::make('name')->label('Tên người đại diện')->required()->maxLength(120),
                    TextInput::make('label')->label('Chức danh / doanh nghiệp')->maxLength(160),
                    RichEditor::make('content')->label('Nội dung phản hồi')->required()->columnSpanFull(),
                    TextInput::make('rating')->label('Số sao')->numeric()->minValue(1)->maxValue(5)->default(5)->required(),
                ])
                ->columns(2),
            Section::make('Ảnh và video')
                ->icon('heroicon-o-photo')
                ->schema([
                    FileUpload::make('avatar')->label('Ảnh đại diện')->image()->storeFiles(false)->maxSize(10240),
                    FileUpload::make('video')->label('Video phản hồi')->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime'])->storeFiles(false)->maxSize(51200),
                ])
                ->columns(2),
            Section::make('Hiển thị')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    TextInput::make('sort_order')->label('Thứ tự')->numeric()->minValue(0)->default(0)->required(),
                    Toggle::make('is_active')->label('Đang hiển thị')->default(true),
                ])
                ->columns(2),
        ]);
    }
}
