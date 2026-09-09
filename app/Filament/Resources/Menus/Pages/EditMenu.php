<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use App\Filament\Resources\Menus\Pages\Concerns\InteractsWithMenuBuilder;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMenu extends EditRecord
{
    use InteractsWithMenuBuilder;

    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
