<?php

namespace App\Filament\Resources\Comments\Schemas;

use App\Models\Comment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nội dung bình luận')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->schema([
                    TextInput::make('name')->label('Người gửi')->required()->maxLength(120),
                    TextInput::make('email')->label('Email')->email()->maxLength(150),
                    Textarea::make('content')->label('Nội dung')->required()->rows(6)->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Duyệt hiển thị')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    Select::make('status')
                        ->label('Trạng thái')
                        ->options(Comment::STATUSES)
                        ->required(),
                    TextInput::make('commentable_type')
                        ->label('Loại nội dung')
                        ->formatStateUsing(fn (?string $state): string => class_basename((string) $state))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('commentable_id')->label('ID nội dung')->disabled()->dehydrated(false),
                ])
                ->columns(2),
        ]);
    }
}
