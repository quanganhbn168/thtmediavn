<?php

namespace App\Filament\Resources\CompanyContents\Schemas;

use App\Filament\Components\SlugInput;
use App\Models\CompanyContent;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CompanyContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->columnSpanFull()
                    ->schema([
                        Group::make([
                            Section::make('Nội dung công ty')
                                ->icon('heroicon-o-building-office-2')
                                ->schema([
                                    TextInput::make('title.vi')
                                        ->label('Tiêu đề')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpanFull()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (?string $state, $set, $get): void {
                                            if ((bool) $get('slug_editable')) {
                                                return;
                                            }

                                            $set('slug', Str::slug((string) $state));
                                        }),
                                    Hidden::make('slug_editable')
                                        ->default(false)
                                        ->dehydrated(false),
                                    SlugInput::make('slug'),
                                    Textarea::make('summary.vi')
                                        ->label('Mô tả')
                                        ->rows(4)
                                        ->columnSpanFull(),
                                    RichEditor::make('content.vi')
                                        ->label('Nội dung')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                            Section::make('SEO')
                                ->icon('heroicon-o-magnifying-glass')
                                ->schema([
                                    TextInput::make('seo_title.vi')
                                        ->label('SEO title')
                                        ->maxLength(255),
                                    Textarea::make('seo_description.vi')
                                        ->label('SEO description')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    CuratorPicker::make('share_image_id')
                                        ->label('Ảnh chia sẻ')
                                        ->relationship('shareImage', 'id'),
                                ])
                                ->columns(1),
                        ])
                            ->columnSpan(['lg' => 2]),
                        Group::make([
                            Section::make('Hình ảnh')
                                ->icon('heroicon-o-photo')
                                ->schema([
                                    CuratorPicker::make('image_id')
                                        ->label('Ảnh')
                                        ->relationship('image', 'id'),
                                    CuratorPicker::make('banner_id')
                                        ->label('Banner')
                                        ->relationship('banner', 'id'),
                                ])
                                ->columns(1),
                            Section::make('Xuất bản')
                                ->icon('heroicon-o-adjustments-horizontal')
                                ->schema([
                                    Toggle::make('is_active')
                                        ->label('Trạng thái')
                                        ->default(true)
                                        ->onColor('success'),
                                    Toggle::make('is_featured')
                                        ->label('Nổi bật')
                                        ->default(false),
                                    TextInput::make('sort_order')
                                        ->label('Thứ tự')
                                        ->numeric()
                                        ->minValue(0)
                                        ->default(fn (): int => CompanyContent::nextSortOrder())
                                        ->required(),
                                    DateTimePicker::make('published_at')
                                        ->label('Xuất bản từ')
                                        ->native(false)
                                        ->seconds(false),
                                ])
                                ->columns(1),
                        ])
                            ->columnSpan(['lg' => 1]),
                    ]),
            ]);
    }
}
