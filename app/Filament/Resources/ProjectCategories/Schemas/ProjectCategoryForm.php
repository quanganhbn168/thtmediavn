<?php

namespace App\Filament\Resources\ProjectCategories\Schemas;

use App\Models\ProjectCategory;
use App\Services\CategoryHierarchyService;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Danh mục dự án')
                ->icon('heroicon-o-tag')
                ->schema([
                    TextInput::make('name.vi')
                        ->label('Tên danh mục')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Select::make('parent_id')
                        ->label('Danh mục cha')
                        ->options(fn (): array => collect(app(CategoryHierarchyService::class)->selectOptions(
                            ProjectCategory::query()->orderBy('sort_order')->get(),
                            parentMode: true,
                        ))->mapWithKeys(fn (array $option): array => [$option['id'] => $option['label']])->all())
                        ->searchable()
                        ->placeholder('Không có danh mục cha'),
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
                    TextInput::make('seo_title.vi')->label('SEO title')->maxLength(255),
                    Textarea::make('seo_description.vi')->label('SEO description')->rows(3)->columnSpanFull(),
                ])
                ->columns(2),
            Toggle::make('is_active')->label('Đang hiển thị')->default(true),
        ]);
    }
}
