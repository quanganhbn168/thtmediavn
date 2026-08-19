<?php

namespace App\Filament\Resources\SliderItems\Pages;

use App\Filament\Resources\SliderItems\SliderItemResource;
use App\Models\SliderItem;
use App\Services\SliderItemService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSliderItem extends EditRecord
{
    protected static string $resource = SliderItemResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var SliderItem $item */
        $item = $this->getRecord();
        $buttons = is_array($item->buttons) ? $item->buttons : [];

        return [
            ...$data,
            'button_text_1' => $buttons[0]['text'] ?? [],
            'button_link_1' => $buttons[0]['link'] ?? null,
            'button_text_2' => $buttons[1]['text'] ?? [],
            'button_link_2' => $buttons[1]['link'] ?? null,
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(SliderItemService::class)->update($record, $data);

        return $record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(fn (SliderItem $record): mixed => app(SliderItemService::class)->delete($record)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
