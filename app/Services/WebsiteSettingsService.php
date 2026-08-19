<?php

namespace App\Services;

use App\Settings\CompanySettings;
use App\Settings\ContactSettings;
use App\Settings\SeoSettings;
use App\Settings\WebsiteSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WebsiteSettingsService
{
    public const CACHE_KEY = 'site:website-settings:v1';

    private ?array $data = null;

    public function all(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $name = (string) config('app.name', 'Laravel');
        $fallback = [
            'name' => $name,
            'company' => $name,
            'tagline' => '',
            'phone' => '',
            'email' => (string) config('mail.from.address', ''),
            'address' => '',
            'phones' => [],
            'branches' => [],
            'business_license' => '',
            'welcome' => 'Chào mừng bạn đến với '.$name.'!',
            'seo_title' => $name,
            'seo_description' => '',
            'seo_keywords' => '',
            'copyright' => '',
            'social' => [
                'facebook' => null,
                'instagram' => null,
                'youtube' => null,
                'tiktok' => null,
                'zalo' => null,
            ],
            'timezone' => (string) config('app.timezone', 'UTC'),
            'site_status' => true,
        ];

        if (! Schema::hasTable('settings')) {
            return $this->data = $fallback;
        }

        try {
            return $this->data = Cache::remember(self::CACHE_KEY, now()->addDay(), function () use ($fallback): array {
                $company = app(CompanySettings::class);
                $website = app(WebsiteSettings::class);
                $contact = app(ContactSettings::class);
                $seo = app(SeoSettings::class);
                $phones = $this->normalizePhones($contact);
                $primaryPhone = collect($phones)->firstWhere('is_primary', true) ?? $phones[0] ?? null;
                $name = (string) ($website->site_name['vi'] ?? $fallback['name']);
                $description = (string) ($website->site_description['vi'] ?? '');

                return [
                    'name' => $name,
                    'company' => $company->company_name,
                    'tagline' => $description,
                    'phone' => $primaryPhone['number'] ?? $contact->phone,
                    'phones' => $phones,
                    'branches' => $this->activeBranches($contact),
                    'email' => $contact->email,
                    'address' => $contact->address,
                    'business_license' => $company->tax_code,
                    'welcome' => 'Chào mừng bạn đến với '.$name.'!',
                    'seo_title' => (string) ($seo->seo_title['vi'] ?? $name),
                    'seo_description' => (string) ($seo->seo_description['vi'] ?? $description),
                    'seo_keywords' => (string) ($seo->seo_keywords['vi'] ?? ''),
                    'copyright' => (string) ($website->copyright['vi'] ?? ''),
                    'social' => [
                        'facebook' => $contact->facebook,
                        'instagram' => $contact->instagram,
                        'youtube' => $contact->youtube,
                        'tiktok' => $contact->tiktok,
                        'zalo' => $contact->zalo,
                    ],
                    'timezone' => $website->timezone,
                    'site_status' => $website->site_status,
                ];
            });
        } catch (Throwable) {
            return $this->data = $fallback;
        }
    }

    public function refresh(): void
    {
        $this->data = null;
        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<int, array{label: string, number: string, is_primary: bool}> */
    private function normalizePhones(ContactSettings $contact): array
    {
        if ($contact->phones !== []) {
            return $contact->phones;
        }

        return filled($contact->phone)
            ? [[
                'label' => 'Hotline chính',
                'number' => $contact->phone,
                'is_primary' => true,
            ]]
            : [];
    }

    /** @return array<int, array{name: string, address: string, is_active: bool}> */
    private function activeBranches(ContactSettings $contact): array
    {
        return collect($contact->branches)
            ->filter(fn (mixed $branch): bool => is_array($branch) && ($branch['is_active'] ?? true))
            ->values()
            ->all();
    }
}
