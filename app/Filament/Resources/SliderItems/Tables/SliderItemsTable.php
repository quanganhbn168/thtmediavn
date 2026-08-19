<?php

namespace App\Filament\Resources\SliderItems\Tables;

use App\Models\SliderItem;
use App\Services\SliderItemService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class SliderItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slider.name')
                    ->label('Slider')
                    ->getStateUsing(fn (SliderItem $record): string => $record->slider?->getTranslation('name', 'vi') ?? '—'),
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->getStateUsing(fn (SliderItem $record): string => $record->getTranslation('title', 'vi') ?: 'Không có tiêu đề')
                    ->wrap(),
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Hiển thị'),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('slider_id')
                    ->label('Slider')
                    ->relationship('slider', 'name'),
                TernaryFilter::make('is_active')->label('Hiển thị'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->action(fn (SliderItem $record): mixed => app(SliderItemService::class)->delete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(fn (Collection $records): mixed => $records->each(
                            fn (SliderItem $record): mixed => app(SliderItemService::class)->delete($record)
                        )),
                ]),
            ]);
    }
}
