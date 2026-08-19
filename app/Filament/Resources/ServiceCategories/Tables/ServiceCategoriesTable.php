<?php

namespace App\Filament\Resources\ServiceCategories\Tables;

use App\Models\ServiceCategory;
use App\Services\ServiceCategoryService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class ServiceCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên danh mục')->getStateUsing(fn (ServiceCategory $record): string => $record->getTranslation('name', 'vi'))->searchable()->sortable(),
                TextColumn::make('parent.name')->label('Danh mục cha')->getStateUsing(fn (ServiceCategory $record): string => $record->parent?->getTranslation('name', 'vi') ?? '—'),
                TextColumn::make('services_count')->label('Dịch vụ')->counts('services')->sortable(),
                ToggleColumn::make('is_active')->label('Hiển thị'),
                TextColumn::make('sort_order')->label('Thứ tự')->numeric()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([TernaryFilter::make('is_active')->label('Hiển thị')])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->action(fn (ServiceCategory $record): mixed => app(ServiceCategoryService::class)->delete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->action(fn (Collection $records): mixed => $records->each(fn (ServiceCategory $record): mixed => app(ServiceCategoryService::class)->delete($record))),
                ]),
            ]);
    }
}
