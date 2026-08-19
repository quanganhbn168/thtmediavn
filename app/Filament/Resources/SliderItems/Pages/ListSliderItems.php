<?php

namespace App\Filament\Resources\SliderItems\Pages;

use App\Filament\Resources\SliderItems\SliderItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSliderItems extends ListRecords
{
    protected static string $resource = SliderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Thêm slide'),
        ];
    }
}
