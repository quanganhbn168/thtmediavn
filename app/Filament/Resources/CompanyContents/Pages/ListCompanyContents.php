<?php

namespace App\Filament\Resources\CompanyContents\Pages;

use App\Filament\Resources\CompanyContents\CompanyContentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanyContents extends ListRecords
{
    protected static string $resource = CompanyContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Thêm nội dung công ty'),
        ];
    }
}
