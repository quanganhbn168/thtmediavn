<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use App\Models\PostCategory;
use App\Services\PostService;
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

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tiêu đề')
                    ->getStateUsing(fn (Post $record): string => $record->getTranslation('name', 'vi'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('category.name')
                    ->label('Chuyên mục')
                    ->getStateUsing(fn (Post $record): string => $record->category?->getTranslation('name', 'vi') ?? 'Chưa phân loại'),
                ToggleColumn::make('is_featured')
                    ->label('Nổi bật'),
                ToggleColumn::make('is_active')
                    ->label('Hiển thị'),
                TextColumn::make('published_at')
                    ->label('Xuất bản')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Chưa đặt lịch'),
                TextColumn::make('view_count')
                    ->label('Lượt xem')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('post_category_id')
                    ->label('Chuyên mục')
                    ->options(fn (): array => PostCategory::query()
                        ->orderBy('sort_order')
                        ->get()
                        ->mapWithKeys(fn (PostCategory $category): array => [
                            $category->getKey() => $category->getTranslation('name', 'vi'),
                        ])
                        ->all()),
                TernaryFilter::make('is_active')->label('Hiển thị'),
                TernaryFilter::make('is_featured')->label('Nổi bật'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->action(fn (Post $record): mixed => app(PostService::class)->delete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(fn (Collection $records): mixed => $records->each(
                            fn (Post $record): mixed => app(PostService::class)->delete($record)
                        )),
                ]),
            ]);
    }
}
