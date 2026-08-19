<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Components\SlugInput;
use App\Models\PostCategory;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])
                ->columnSpanFull()
                ->schema([
                    Group::make([
                        Section::make('Nội dung tin tức')
                            ->icon('heroicon-o-newspaper')
                            ->schema([
                                TextInput::make('name.vi')
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
                                    ->label('Mô tả ngắn')
                                    ->rows(4)
                                    ->columnSpanFull(),
                                RichEditor::make('content.vi')
                                    ->label('Nội dung bài viết')
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
                            ])
                            ->columns(1),
                    ])
                        ->columnSpan(['lg' => 2]),
                    Group::make([
                        Section::make('Phân loại')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Select::make('post_category_id')
                                    ->label('Chuyên mục')
                                    ->options(fn (): array => PostCategory::query()
                                        ->where('is_active', true)
                                        ->orderBy('sort_order')
                                        ->get()
                                        ->mapWithKeys(fn (PostCategory $category): array => [
                                            $category->getKey() => $category->getTranslation('name', 'vi'),
                                        ])
                                        ->all())
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ])
                            ->columns(1),
                        Section::make('Hình ảnh')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                CuratorPicker::make('image_id')
                                    ->label('Ảnh đại diện')
                                    ->relationship('image', 'id'),
                            ])
                            ->columns(1),
                        Section::make('Xuất bản')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                DateTimePicker::make('published_at')
                                    ->label('Ngày xuất bản')
                                    ->native(false)
                                    ->seconds(false)
                                    ->default(now()),
                                Toggle::make('is_active')
                                    ->label('Đang hiển thị')
                                    ->default(true),
                                Toggle::make('is_featured')
                                    ->label('Nổi bật')
                                    ->default(false),
                            ])
                            ->columns(1),
                    ])
                        ->columnSpan(['lg' => 1]),
                ]),
        ]);
    }
}
