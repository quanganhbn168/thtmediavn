<?php

namespace App\Filament\Resources\SliderItems\Schemas;

use App\Models\Slider;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SliderItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nội dung slide')
                ->icon('heroicon-o-rectangle-stack')
                ->schema([
                    Select::make('slider_id')
                        ->label('Slider')
                        ->options(fn (): array => Slider::query()
                            ->orderBy('id')
                            ->get()
                            ->mapWithKeys(fn (Slider $slider): array => [
                                $slider->getKey() => $slider->getTranslation('name', 'vi'),
                            ])
                            ->all())
                        ->required()
                        ->searchable()
                        ->preload(),
                    TextInput::make('sort_order')
                        ->label('Thứ tự')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    TextInput::make('title.vi')
                        ->label('Tiêu đề')
                        ->maxLength(255),
                    TextInput::make('sub_title.vi')
                        ->label('Phụ đề')
                        ->maxLength(500),
                    FileUpload::make('image')
                        ->label('Ảnh desktop')
                        ->image()
                        ->storeFiles(false)
                        ->maxSize(51200),
                    FileUpload::make('mobile_image')
                        ->label('Ảnh mobile')
                        ->image()
                        ->storeFiles(false)
                        ->maxSize(51200),
                ])
                ->columns(2),
            Section::make('Nút hành động')
                ->icon('heroicon-o-cursor-arrow-rays')
                ->schema([
                    TextInput::make('button_text_1.vi')
                        ->label('Nút 1'),
                    TextInput::make('button_link_1')
                        ->label('Liên kết nút 1')
                        ->maxLength(2048),
                    TextInput::make('button_text_2.vi')
                        ->label('Nút 2'),
                    TextInput::make('button_link_2')
                        ->label('Liên kết nút 2')
                        ->maxLength(2048),
                ])
                ->columns(2),
            Section::make('Trạng thái')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Đang hiển thị')
                        ->default(true),
                ]),
        ]);
    }
}
