<?php

namespace App\Filament\Resources\PricingPlans\Tables;

use App\Models\PricingPlan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class PricingPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên gói')->searchable()->sortable(),
                TextColumn::make('price')->label('Mức giá')->placeholder('Liên hệ'),
                ToggleColumn::make('is_featured')->label('Nổi bật'),
                ToggleColumn::make('is_active')->label('Hiển thị'),
                TextColumn::make('sort_order')->label('Thứ tự')->numeric()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Hiển thị'),
                TernaryFilter::make('is_featured')->label('Nổi bật'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->action(fn (Collection $records): mixed => $records->each->delete()),
                ]),
            ]);
    }
}
