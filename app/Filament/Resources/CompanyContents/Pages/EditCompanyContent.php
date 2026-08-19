<?php

namespace App\Filament\Resources\CompanyContents\Pages;

use App\Filament\Resources\CompanyContents\CompanyContentResource;
use App\Models\CompanyContent;
use App\Services\CompanyContentService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCompanyContent extends EditRecord
{
    protected static string $resource = CompanyContentResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(CompanyContentService::class)->update($record, $data);

        return $record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(fn (CompanyContent $record): mixed => app(CompanyContentService::class)->delete($record)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
