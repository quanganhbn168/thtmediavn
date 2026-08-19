<?php

namespace App\Filament\Resources\CompanyContents\Pages;

use App\Filament\Resources\CompanyContents\CompanyContentResource;
use App\Services\CompanyContentService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCompanyContent extends CreateRecord
{
    protected static string $resource = CompanyContentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CompanyContentService::class)->create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
