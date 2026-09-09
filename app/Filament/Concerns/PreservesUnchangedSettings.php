<?php

namespace App\Filament\Concerns;

use Livewire\Attributes\Locked;

trait PreservesUnchangedSettings
{
    #[Locked]
    public array $originalSettingsData = [];

    #[Locked]
    public array $initialSettingsForm = [];

    protected function fillSettingsForm(array $data): void
    {
        $this->originalSettingsData = $data;
        $this->form->fill($data);
        $this->initialSettingsForm = $this->data ?? [];
    }

    protected function settingsFormData(): array
    {
        $current = $this->data ?? [];
        $validated = $this->form->getState();
        foreach ($this->originalSettingsData as $key => $original) {
            if (in_array($key, $this->customDehydratedSettingsFields(), true)) {
                continue;
            }
            if ($this->comparableSetting($current[$key] ?? null) === $this->comparableSetting($this->initialSettingsForm[$key] ?? null)) {
                $validated[$key] = $original;

                continue;
            }
            // Preserve languages that are not represented in the active form.
            if (is_array($original) && $original !== [] && collect(array_keys($original))->every(fn ($locale): bool => is_string($locale) && (bool) preg_match('/^[a-z]{2}(?:[-_][A-Za-z]{2})?$/', $locale))) {
                $validated[$key] = array_replace($original, $validated[$key] ?? []);
            }
        }

        return $validated;
    }

    protected function customDehydratedSettingsFields(): array
    {
        return [];
    }

    private function comparableSetting(mixed $value): mixed
    {
        if ($value === '') {
            return null;
        }
        if (! is_array($value)) {
            return $value;
        }
        $normalized = array_map(fn ($item) => $this->comparableSetting($item), $value);
        if (collect(array_keys($value))->every(fn ($key): bool => is_int($key) || (is_string($key) && (str_starts_with($key, 'record-') || preg_match('/^[0-9a-f-]{36}$/i', $key))))) {
            return array_values($normalized);
        }

        return $normalized;
    }
}
