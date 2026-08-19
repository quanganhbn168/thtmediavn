<?php

namespace App\Filament\Resources\PostCategories\Schemas;

use App\Models\PostCategory;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin chuyên mục')
                ->icon('heroicon-o-folder-open')
                ->schema([
                    TextInput::make('name.vi')
                        ->label('Tên chuyên mục')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Select::make('parent_id')
                        ->label('Chuyên mục cha')
                        ->options(fn (): array => PostCategory::query()
                            ->orderBy('sort_order')
                            ->get()
                            ->mapWithKeys(fn (PostCategory $category): array => [
                                $category->getKey() => $category->getTranslation('name', 'vi'),
                            ])
                            ->all())
                        ->searchable()
                        ->preload()
                        ->placeholder('Không có chuyên mục cha'),
                    TextInput::make('sort_order')
                        ->label('Thứ tự')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    RichEditor::make('description.vi')
                        ->label('Mô tả')
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
                ->columns(2),
            Section::make('Hiển thị')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Đang hiển thị')
                        ->default(true),
                    Toggle::make('is_home')
                        ->label('Hiển thị trên trang chủ')
                        ->default(false),
                ])
                ->columns(2),
        ]);
    }
}
