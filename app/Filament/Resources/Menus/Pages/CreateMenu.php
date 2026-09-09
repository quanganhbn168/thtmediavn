<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use App\Filament\Resources\Menus\Pages\Concerns\InteractsWithMenuBuilder;
use Filament\Resources\Pages\CreateRecord;

class CreateMenu extends CreateRecord
{
    use InteractsWithMenuBuilder;

    protected static string $resource = MenuResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
