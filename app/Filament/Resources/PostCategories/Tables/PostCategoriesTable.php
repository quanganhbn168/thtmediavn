<?php

namespace App\Filament\Resources\PostCategories\Tables;

use App\Models\PostCategory;
use App\Services\PostCategoryService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class PostCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên chuyên mục')
                    ->getStateUsing(fn (PostCategory $record): string => $record->getTranslation('name', 'vi'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label('Chuyên mục cha')
                    ->getStateUsing(fn (PostCategory $record): string => $record->parent?->getTranslation('name', 'vi') ?? '—'),
                TextColumn::make('posts_count')
                    ->label('Tin bài')
                    ->counts('posts')
                    ->sortable(),
                ToggleColumn::make('is_home')
                    ->label('Trang chủ'),
                ToggleColumn::make('is_active')
                    ->label('Hiển thị'),
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Hiển thị'),
                TernaryFilter::make('is_home')->label('Trang chủ'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->action(fn (PostCategory $record): mixed => app(PostCategoryService::class)->delete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(fn (Collection $records): mixed => $records->each(
                            fn (PostCategory $record): mixed => app(PostCategoryService::class)->delete($record)
                        )),
                ]),
            ]);
    }
}
