<?php

namespace App\Filament\Resources\ServiceCategories\Pages;

use App\Filament\Resources\ServiceCategories\ServiceCategoryResource;
use App\Models\ServiceCategory;
use App\Services\ServiceCategoryService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditServiceCategory extends EditRecord
{
    protected static string $resource = ServiceCategoryResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(ServiceCategoryService::class)->update($record, $data);

        return $record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (ServiceCategory $record): mixed => app(ServiceCategoryService::class)->delete($record))];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
