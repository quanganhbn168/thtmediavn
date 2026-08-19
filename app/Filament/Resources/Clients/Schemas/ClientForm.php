<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin khách hàng')
                ->icon('heroicon-o-building-office-2')
                ->schema([
                    TextInput::make('name.vi')
                        ->label('Tên khách hàng / đối tác')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('industry')
                        ->label('Lĩnh vực')
                        ->maxLength(255),
                    TextInput::make('website_url')
                        ->label('Website')
                        ->url()
                        ->maxLength(2048),
                    RichEditor::make('description.vi')
                        ->label('Giới thiệu')
                        ->columnSpanFull(),
                    Textarea::make('quote.vi')
                        ->label('Trích dẫn khách hàng')
                        ->rows(4),
                    TextInput::make('quote_author')
                        ->label('Người trích dẫn')
                        ->maxLength(255),
                ])
                ->columns(2),
            Section::make('Hình ảnh')
                ->icon('heroicon-o-photo')
                ->schema([
                    FileUpload::make('logo')
                        ->label('Logo')
                        ->image()
                        ->storeFiles(false)
                        ->maxSize(10240),
                    FileUpload::make('cover')
                        ->label('Ảnh cover')
                        ->image()
                        ->storeFiles(false)
                        ->maxSize(15360),
                ])
                ->columns(2),
            Section::make('Hiển thị')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    TextInput::make('sort_order')
                        ->label('Thứ tự')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Đang hiển thị')
                        ->default(true),
                    Toggle::make('is_featured')
                        ->label('Nổi bật')
                        ->default(false),
                ])
                ->columns(3),
        ]);
    }
}
