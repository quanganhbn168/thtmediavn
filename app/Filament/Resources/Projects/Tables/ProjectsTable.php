<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Project;
use App\Services\ProjectService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên dự án')
                    ->getStateUsing(fn (Project $record): string => $record->getTranslation('name', 'vi'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Khách hàng')
                    ->getStateUsing(fn (Project $record): string => $record->client?->getTranslation('name', 'vi') ?? 'Chưa gắn khách hàng')
                    ->placeholder('Chưa gắn khách hàng'),
                TextColumn::make('category.name')
                    ->label('Danh mục')
                    ->getStateUsing(fn (Project $record): string => $record->category?->getTranslation('name', 'vi') ?? 'Chưa gắn danh mục'),
                TextColumn::make('industry')
                    ->label('Lĩnh vực')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('completed_year')
                    ->label('Năm')
                    ->sortable()
                    ->placeholder('—'),
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
                TernaryFilter::make('is_active')
                    ->label('Hiển thị'),
                TernaryFilter::make('is_featured')
                    ->label('Nổi bật'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->action(fn (Project $record): mixed => app(ProjectService::class)->delete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(fn (Collection $records): mixed => $records->each(
                            fn (Project $record): mixed => app(ProjectService::class)->delete($record)
                        )),
                ]),
            ]);
    }
}
