<?php

namespace App\Filament\Resources\Testimonials\Tables;

use App\Models\Testimonial;
use App\Services\TestimonialService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Người gửi')->searchable()->sortable(),
                TextColumn::make('label')->label('Đơn vị')->placeholder('—')->limit(40),
                TextColumn::make('rating')->label('Sao')->formatStateUsing(fn (int $state): string => str_repeat('★', $state)),
                ToggleColumn::make('is_active')->label('Hiển thị'),
                TextColumn::make('sort_order')->label('Thứ tự')->numeric()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([TernaryFilter::make('is_active')->label('Hiển thị')])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->action(fn (Testimonial $record): mixed => app(TestimonialService::class)->delete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->action(fn (Collection $records): mixed => $records->each(fn (Testimonial $record): mixed => app(TestimonialService::class)->delete($record))),
                ]),
            ]);
    }
}
