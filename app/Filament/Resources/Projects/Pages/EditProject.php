<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Project $project */
        $project = $this->getRecord();

        return [
            ...$data,
            ...app(ProjectService::class)->structuredFormValues($project),
            'slug' => $project->getSlug('vi'),
            'service_ids' => $project->services()->pluck('services.id')->all(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(ProjectService::class)->update($record, $data);

        return $record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(fn (Project $record): mixed => app(ProjectService::class)->delete($record)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
