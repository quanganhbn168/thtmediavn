<?php

namespace App\Filament\Resources\CompanyContents\Tables;

use App\Models\CompanyContent;
use App\Services\CompanyContentService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class CompanyContentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->getStateUsing(fn (CompanyContent $record): string => $record->getTranslation('title', 'vi'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                ToggleColumn::make('is_active')
                    ->label('Hiển thị'),
                ToggleColumn::make('is_featured')
                    ->label('Nổi bật'),
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Xuất bản')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Ngay'),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Hiển thị'),
                TernaryFilter::make('is_featured')->label('Nổi bật'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->action(fn (CompanyContent $record): mixed => app(CompanyContentService::class)->delete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(fn (Collection $records): mixed => $records->each(
                            fn (CompanyContent $record): mixed => app(CompanyContentService::class)->delete($record)
                        )),
                ]),
            ]);
    }
}
