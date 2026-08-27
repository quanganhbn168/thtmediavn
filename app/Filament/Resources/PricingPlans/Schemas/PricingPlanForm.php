<?php

namespace App\Filament\Resources\PricingPlans\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PricingPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin gói dịch vụ')
                ->icon('heroicon-o-tag')
                ->schema([
                    TextInput::make('name')
                        ->label('Tên gói')
                        ->required()
                        ->maxLength(160),
                    TextInput::make('price_amount')
                        ->label('Giá cơ sở')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('₫')
                        ->helperText('Nhập số tiền, ví dụ 250000. Để trống nếu cần liên hệ.'),
                    TextInput::make('price_unit')
                        ->label('Đơn vị tính')
                        ->maxLength(80)
                        ->placeholder('trang, gói, tháng'),
                    Textarea::make('summary')
                        ->label('Mô tả ngắn')
                        ->rows(3)
                        ->columnSpanFull(),
                    TextInput::make('price_note')
                        ->label('Ghi chú giá')
                        ->placeholder('Ví dụ: theo tháng hoặc theo phạm vi'),
                    Textarea::make('features')
                        ->label('Hạng mục bao gồm')
                        ->helperText('Mỗi hạng mục nhập trên một dòng.')
                        ->rows(6)
                        ->formatStateUsing(fn (mixed $state): string => is_array($state) ? implode(PHP_EOL, $state) : (string) $state)
                        ->dehydrateStateUsing(fn (mixed $state): array => collect(preg_split('/\r\n|\r|\n/', (string) $state))->map(fn (string $item): string => trim($item))->filter()->values()->all())
                        ->columnSpanFull(),
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
                    Toggle::make('is_featured')
                        ->label('Gói nổi bật'),
                    Toggle::make('is_price_from')
                        ->label('Hiển thị “Từ”')
                        ->helperText('Dùng cho giá khởi điểm hoặc đơn giá tham khảo.'),
                    Toggle::make('is_active')
                        ->label('Đang hiển thị')
                        ->default(true),
                ])
                ->columns(4),
        ]);
    }
}
