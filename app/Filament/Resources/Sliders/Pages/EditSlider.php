<?php

namespace App\Filament\Resources\Sliders\Pages;

use App\Filament\Resources\Sliders\SliderResource;
use App\Models\Slider;
use App\Services\SliderService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSlider extends EditRecord
{
    protected static string $resource = SliderResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(SliderService::class)->update($record, $data);

        return $record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(fn (Slider $record): mixed => app(SliderService::class)->delete($record)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
