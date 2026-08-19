<?php

namespace App\Filament\Resources\Comments\Tables;

use App\Models\Comment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Người gửi')->searchable()->sortable(),
                TextColumn::make('content')->label('Nội dung')->limit(80)->wrap(),
                TextColumn::make('commentable_type')
                    ->label('Loại')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => class_basename($state)),
                SelectColumn::make('status')->label('Trạng thái')->options(Comment::STATUSES),
                TextColumn::make('created_at')->label('Gửi lúc')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Trạng thái')->options(Comment::STATUSES),
                SelectFilter::make('commentable_type')->label('Loại nội dung')->options([
                    'App\\Models\\Post' => 'Tin tức',
                    'App\\Models\\Service' => 'Dịch vụ',
                    'App\\Models\\Project' => 'Dự án',
                    'App\\Models\\Page' => 'Trang',
                ]),
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
