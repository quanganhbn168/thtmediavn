<?php

namespace App\Filament\Resources\Popups\Schemas;

use App\Models\Popup;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PopupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nội dung popup')
                ->icon('heroicon-o-document-text')
                ->schema([
                    TextInput::make('title')
                        ->label('Tiêu đề')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('subtitle')
                        ->label('Phụ đề')
                        ->maxLength(255),
                    CuratorPicker::make('image_id')
                        ->label('Ảnh popup')
                        ->relationship('image', 'id')
                        ->imageCropAspectRatio('4:3'),
                    RichEditor::make('content')
                        ->label('Nội dung')
                        ->columnSpanFull(),
                    TextInput::make('button_text')
                        ->label('Chữ trên nút')
                        ->maxLength(100),
                    TextInput::make('button_url')
                        ->label('Đường dẫn nút')
                        ->maxLength(2048)
                        ->helperText('Cho phép đường dẫn nội bộ bắt đầu bằng / hoặc liên kết https://.'),
                    Toggle::make('button_target_blank')
                        ->label('Mở liên kết ở tab mới')
                        ->default(false)
                        ->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Hiển thị')
                ->icon('heroicon-o-adjustments-horizontal')
                ->description('Thiết lập phạm vi, lịch chạy và thứ tự ưu tiên của popup.')
                ->schema([
                    Select::make('display_scope')
                        ->label('Phạm vi hiển thị')
                        ->options(Popup::DISPLAY_SCOPES)
                        ->default('all')
                        ->required(),
                    TextInput::make('display_delay')
                        ->label('Trì hoãn hiển thị (giây)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(60)
                        ->default(0)
                        ->required(),
                    DateTimePicker::make('starts_at')
                        ->label('Bắt đầu từ')
                        ->native(false)
                        ->seconds(false),
                    DateTimePicker::make('ends_at')
                        ->label('Kết thúc lúc')
                        ->native(false)
                        ->seconds(false),
                    TextInput::make('sort_order')
                        ->label('Thứ tự ưu tiên')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    Toggle::make('show_once')
                        ->label('Chỉ hiện một lần mỗi trình duyệt')
                        ->helperText('Người dùng đã thấy popup sẽ không gặp lại popup này trên cùng trình duyệt.')
                        ->default(true),
                    Toggle::make('is_active')
                        ->label('Đang hoạt động')
                        ->default(true)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
