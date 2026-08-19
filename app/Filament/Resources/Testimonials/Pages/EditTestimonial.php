<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Models\Testimonial;
use App\Services\TestimonialService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTestimonial extends EditRecord
{
    protected static string $resource = TestimonialResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(TestimonialService::class)->update($record, $data);

        return $record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Testimonial $record): mixed => app(TestimonialService::class)->delete($record))];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
