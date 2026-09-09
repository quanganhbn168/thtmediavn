<?php

namespace App\Support\Tracking;

use App\Settings\TrackingSettings;

class TrackingScripts
{
    public const FIELDS = ['google_analytics_id', 'google_analytics_code', 'google_tag_manager_id', 'google_tag_manager_head_code', 'google_tag_manager_body_code', 'meta_pixel_code', 'tiktok_pixel_code', 'head_code', 'body_open_code', 'body_close_code'];

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
        $customCode = implode("\n", [$s->head_code, $s->body_open_code, $s->body_close_code, ...array_values($page)]);
        if ($ga === '' && preg_match('/^G-[A-Z0-9]+$/', (string) $s->google_analytics_id) && ! $this->containsGoogleTag($customCode, $s->google_analytics_id)) {
            $id = $s->google_analytics_id;
            $ga = '<script async src="https://www.googletagmanager.com/gtag/js?id='.$id.'"></script>'
                ."<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','".$id."');</script>";
        }
        if ($gtm === '' && preg_match('/^GTM-[A-Z0-9]+$/', (string) $s->google_tag_manager_id) && ! $this->containsGoogleTag($customCode, $s->google_tag_manager_id)) {
            $id = $s->google_tag_manager_id;
            $gtm = "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','".$id."');</script>";
            $gtmBody = $gtmBody ?: '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id='.$id.'" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
        }

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

    private function containsGoogleTag(string $code, string $id): bool
    {
        preg_match_all('~<script\b[^>]*>.*?</script\s*>~is', $code, $scripts);
        foreach ($scripts[0] as $script) {
            if (preg_match('~'.preg_quote($id, '~').'(?![A-Z0-9])~', $script)
                && (str_contains($script, 'googletagmanager.com') || str_contains($script, 'gtag('))) {
                return true;
            }
        }

        return false;
    }
}
