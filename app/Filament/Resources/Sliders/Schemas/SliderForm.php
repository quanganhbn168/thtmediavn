<?php

namespace App\Filament\Resources\Sliders\Schemas;

use App\Enums\SliderType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SliderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin slider')
                ->icon('heroicon-o-photo')
                ->schema([
                    TextInput::make('name.vi')
                        ->label('Tên slider')
                        ->required()
                        ->maxLength(255),
                    Select::make('key')
                        ->label('Vị trí hiển thị')
                        ->options(SliderType::options())
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->searchable(),
                    Toggle::make('is_active')
                        ->label('Đang hoạt động')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }
}
