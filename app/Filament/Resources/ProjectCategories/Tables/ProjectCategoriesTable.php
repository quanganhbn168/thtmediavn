<?php

namespace App\Filament\Resources\ProjectCategories\Tables;

use App\Models\ProjectCategory;
use App\Services\ProjectCategoryService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class ProjectCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên danh mục')->getStateUsing(fn (ProjectCategory $record): string => $record->getTranslation('name', 'vi'))->searchable()->sortable(),
                TextColumn::make('parent.name')->label('Danh mục cha')->getStateUsing(fn (ProjectCategory $record): string => $record->parent?->getTranslation('name', 'vi') ?? '—'),
                TextColumn::make('projects_count')->label('Dự án')->counts('projects')->sortable(),
                ToggleColumn::make('is_active')->label('Hiển thị'),
                TextColumn::make('sort_order')->label('Thứ tự')->numeric()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([TernaryFilter::make('is_active')->label('Hiển thị')])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->action(fn (ProjectCategory $record): mixed => app(ProjectCategoryService::class)->delete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->action(fn (Collection $records): mixed => $records->each(fn (ProjectCategory $record): mixed => app(ProjectCategoryService::class)->delete($record))),
                ]),
            ]);
    }
}
