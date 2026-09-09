<?php

namespace App\Support\Tracking;

use App\Settings\TrackingSettings;

class TrackingScripts
{
    public const FIELDS = ['google_analytics_code', 'google_tag_manager_head_code', 'google_tag_manager_body_code', 'meta_pixel_code', 'tiktok_pixel_code', 'head_code', 'body_open_code', 'body_close_code'];

    public function __construct(private readonly TrackingSettings $settings) {}

    public function formData(): array
    {
        $data = [];
        foreach (self::FIELDS as $field) {
            $data[$field] = $this->settings->{$field};
        }

        return $data;
    }

    public function save(array $data): void
    {
        foreach (self::FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $this->settings->{$field} = filled($data[$field]) ? trim($data[$field]) : null;
            }
        }
        $this->settings->save();
    }

    public function render(array $page = []): array
    {
        $s = $this->settings;
        $ga = trim((string) $s->google_analytics_code);
        $gtm = trim((string) $s->google_tag_manager_head_code);
        $gtmBody = trim((string) $s->google_tag_manager_body_code);
        $slots = [
            'head_start' => [$gtm, $ga],
            'head' => [$s->meta_pixel_code, $s->tiktok_pixel_code, $s->head_code, $page['head'] ?? null],
            'body' => [$gtmBody, $s->body_open_code, $page['body'] ?? null],
            'footer' => [$s->body_close_code, $page['footer'] ?? null],
        ];
        $output = array_fill_keys(array_keys($slots), []);
        $seen = [];
        $headNoscripts = [];
        foreach ($slots as $slot => $snippets) {
            foreach ($snippets as $snippet) {
                // Keep provider code intact; split only complete script/noscript elements.
                $parts = preg_split('~(<script\b[^>]*>.*?</script\s*>|<noscript\b[^>]*>.*?</noscript\s*>)~is', (string) $snippet, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                foreach ($parts as $part) {
                    $part = trim(str_replace(["\r\n", "\r"], "\n", $part));
                    if ($part === '' || isset($seen[$part])) {
                        continue;
                    }
                    $seen[$part] = true;
                    $destination = in_array($slot, ['head_start', 'head'], true) && preg_match('~^<noscript\b~i', $part) ? 'body' : $slot;
                    if ($destination === 'body' && $destination !== $slot) {
                        $headNoscripts[] = $part;
                    } else {
                        $output[$destination][] = $part;
                    }
                }
            }
        }

        $output['body'] = array_merge($output['body'], $headNoscripts);

        return array_map(fn (array $parts): string => implode("\n", $parts), $output);
    }
}
