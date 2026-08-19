<?php

namespace App\Filament\Resources\Popups\Tables;

use App\Models\Popup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PopupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('display_scope')
                    ->label('Phạm vi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Popup::DISPLAY_SCOPES[$state] ?? $state)
                    ->color(fn (string $state): string => $state === 'home' ? 'info' : 'primary'),
                ToggleColumn::make('is_active')
                    ->label('Hoạt động'),
                TextColumn::make('starts_at')
                    ->label('Bắt đầu')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Ngay lập tức')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Kết thúc')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Không giới hạn')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Ưu tiên')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('display_scope')
                    ->label('Phạm vi')
                    ->options(Popup::DISPLAY_SCOPES),
                TernaryFilter::make('is_active')
                    ->label('Hoạt động'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
