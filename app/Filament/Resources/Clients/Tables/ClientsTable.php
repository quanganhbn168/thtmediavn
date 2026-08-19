<?php

namespace App\Filament\Resources\Clients\Tables;

use App\Models\Client;
use App\Services\ClientService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên khách hàng')
                    ->getStateUsing(fn (Client $record): string => $record->getTranslation('name', 'vi'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('industry')
                    ->label('Lĩnh vực')
                    ->placeholder('—'),
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
                TernaryFilter::make('is_active')
                    ->label('Hiển thị'),
                TernaryFilter::make('is_featured')
                    ->label('Nổi bật'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->action(fn (Client $record): mixed => app(ClientService::class)->delete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(fn (Collection $records): mixed => $records->each(
                            fn (Client $record): mixed => app(ClientService::class)->delete($record)
                        )),
                ]),
            ]);
    }
}
