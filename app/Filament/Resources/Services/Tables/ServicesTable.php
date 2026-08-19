<?php

namespace App\Filament\Resources\Services\Tables;

use App\Models\Service;
use App\Services\ServiceService;
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

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên dịch vụ')
                    ->getStateUsing(fn (Service $record): string => $record->getTranslation('name', 'vi'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('group')
                    ->label('Nhóm năng lực')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Service::GROUPS[$state] ?? $state),
                TextColumn::make('category.name')
                    ->label('Danh mục')
                    ->getStateUsing(fn (Service $record): string => $record->category?->getTranslation('name', 'vi') ?? 'Chưa gắn danh mục'),
                TextColumn::make('projects_count')
                    ->label('Dự án')
                    ->counts('projects')
                    ->sortable(),
                ToggleColumn::make('is_featured')
                    ->label('Nổi bật'),
                ToggleColumn::make('is_active')
                    ->label('Hiển thị'),
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('group')
                    ->label('Nhóm')
                    ->options(Service::GROUPS),
                TernaryFilter::make('is_active')
                    ->label('Hiển thị'),
                TernaryFilter::make('is_featured')
                    ->label('Nổi bật'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->action(fn (Service $record): mixed => app(ServiceService::class)->delete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(fn (Collection $records): mixed => $records->each(
                            fn (Service $record): mixed => app(ServiceService::class)->delete($record)
                        )),
                ]),
            ]);
    }
}
