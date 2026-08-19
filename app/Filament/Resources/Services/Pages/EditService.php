<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
use App\Services\ServiceService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Service $service */
        $service = $this->getRecord();

        return [
            ...$data,
            ...app(ServiceService::class)->structuredFormValues($service),
            'slug' => $service->getSlug('vi'),
            'related_service_ids' => $service->relatedServices()->pluck('services.id')->all(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(ServiceService::class)->update($record, $data);

        return $record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(fn (Service $record): mixed => app(ServiceService::class)->delete($record)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
