<?php

namespace App\Filament\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class SlugInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Đường dẫn (slug)')
            ->readOnly(fn (Get $get): bool => ! (bool) $get('slug_editable'))
            ->suffixAction(
                Action::make('toggleSlugEdit')
                    ->label('Chỉnh sửa slug')
                    ->icon(fn (Get $get): string => $get('slug_editable') ? 'heroicon-m-check' : 'heroicon-m-pencil')
                    ->tooltip(fn (Get $get): string => $get('slug_editable') ? 'Khóa slug' : 'Chỉnh sửa slug')
                    ->action(function (Get $get, Set $set): void {
                        $set('slug_editable', ! (bool) $get('slug_editable'));
                    }),
            )
            ->columnSpanFull();
    }
}
