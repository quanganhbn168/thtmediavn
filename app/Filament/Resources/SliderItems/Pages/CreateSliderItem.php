<?php

namespace App\Filament\Resources\SliderItems\Pages;

use App\Filament\Resources\SliderItems\SliderItemResource;
use App\Services\SliderItemService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSliderItem extends CreateRecord
{
    protected static string $resource = SliderItemResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(SliderItemService::class)->create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
