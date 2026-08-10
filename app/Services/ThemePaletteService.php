<?php

namespace App\Services;

class ThemePaletteService
{
    /**
     * Bảng màu chỉ đọc từ config/frontend-theme.php để frontend có một nguồn duy nhất.
     */
    public function activePalette(): array
    {
        $palettes = config('frontend-theme.palettes', []);
        $active = config('frontend-theme.active');
        $fallback = $palettes['emerald_champagne'] ?? reset($palettes) ?: [];
        $palette = $palettes[$active] ?? $fallback;

        return collect(['primary', 'secondary', 'ink', 'muted', 'surface', 'canvas', 'line'])
            ->mapWithKeys(fn (string $key) => [$key => $this->color($palette[$key] ?? null, $fallback[$key] ?? '#FFFFFF')])
            ->all();
    }

    public function currentCssVariables(): array
    {
        return $this->cssVariablesForPalette($this->activePalette());
    }

    private function cssVariablesForPalette(array $palette): array
    {
        $primaryRgb = $this->rgb($palette['primary']);
        $secondaryRgb = $this->rgb($palette['secondary']);

        return [
            '--primary' => $palette['primary'],
            '--primary-rgb' => implode(' ', $primaryRgb),
            '--primary-hover' => $this->shade($palette['primary'], 0.16),
            '--primary-soft' => $this->tint($palette['primary'], 0.92),
            '--secondary' => $palette['secondary'],
            '--secondary-rgb' => implode(' ', $secondaryRgb),
            '--secondary-hover' => $this->shade($palette['secondary'], 0.14),
            '--secondary-soft' => $this->tint($palette['secondary'], 0.90),
            '--ink' => $palette['ink'],
            '--muted' => $palette['muted'],
            '--surface' => $palette['surface'],
            '--canvas' => $palette['canvas'],
            '--line' => $palette['line'],
            '--bs-primary' => $palette['primary'],
            '--bs-primary-rgb' => implode(', ', $primaryRgb),
            '--bs-secondary' => $palette['secondary'],
            '--bs-secondary-rgb' => implode(', ', $secondaryRgb),
        ];
    }

    private function color(mixed $value, string $fallback): string
    {
        $value = strtoupper(trim((string) $value));

        return preg_match('/^#[0-9A-F]{6}$/', $value) ? $value : strtoupper($fallback);
    }

    private function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private function tint(string $hex, float $amount): string
    {
        return $this->mix($hex, '#FFFFFF', $amount);
    }

    private function shade(string $hex, float $amount): string
    {
        return $this->mix($hex, '#000000', $amount);
    }

    private function mix(string $first, string $second, float $secondWeight): string
    {
        $firstRgb = $this->rgb($first);
        $secondRgb = $this->rgb($second);
        $firstWeight = 1 - $secondWeight;
        $channels = [];

        foreach ([0, 1, 2] as $index) {
            $channels[] = (int) round(($firstRgb[$index] * $firstWeight) + ($secondRgb[$index] * $secondWeight));
        }

        return sprintf('#%02X%02X%02X', ...$channels);
    }
}
