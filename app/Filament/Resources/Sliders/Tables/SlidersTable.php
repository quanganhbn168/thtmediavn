<?php

namespace App\Filament\Resources\Sliders\Tables;

use App\Enums\SliderType;
use App\Models\Slider;
use App\Services\SliderService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class SlidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên slider')
                    ->getStateUsing(fn (Slider $record): string => $record->getTranslation('name', 'vi'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('key')
                    ->label('Vị trí')
                    ->formatStateUsing(fn (string $state): string => SliderType::tryFrom($state)?->label() ?? $state)
                    ->wrap(),
                TextColumn::make('items_count')
                    ->label('Số slide')
                    ->counts('items')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Hoạt động'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                TernaryFilter::make('is_active')->label('Hoạt động'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->action(fn (Slider $record): mixed => app(SliderService::class)->delete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(fn (Collection $records): mixed => $records->each(
                            fn (Slider $record): mixed => app(SliderService::class)->delete($record)
                        )),
                ]),
            ]);
    }
}
