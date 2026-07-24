<?php

namespace App\Services;

use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\SeoSettings;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WebsiteSettingsService
{
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
            'business_license' => '',
            'welcome' => 'Chào mừng bạn đến với '.$name.'!',
            'seo_title' => $name,
            'seo_description' => '',
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
            $general = app(GeneralSettings::class);
            $contact = app(ContactSettings::class);
            $seo = app(SeoSettings::class);
            $name = (string) ($general->site_name['vi'] ?? $fallback['name']);
            $description = (string) ($general->site_description['vi'] ?? '');

            return $this->data = [
                'name' => $name,
                'company' => $contact->company_name,
                'tagline' => $description,
                'phone' => $contact->phone,
                'email' => $contact->email,
                'address' => $contact->address,
                'business_license' => $contact->tax_code,
                'welcome' => 'Chào mừng bạn đến với '.$name.'!',
                'seo_title' => (string) ($seo->seo_title['vi'] ?? $name),
                'seo_description' => (string) ($seo->seo_description['vi'] ?? $description),
                'copyright' => (string) ($general->copyright['vi'] ?? ''),
                'social' => [
                    'facebook' => $contact->facebook,
                    'instagram' => $contact->instagram,
                    'youtube' => $contact->youtube,
                    'tiktok' => $contact->tiktok,
                    'zalo' => $contact->zalo,
                ],
                'timezone' => $general->timezone,
                'site_status' => $general->site_status,
            ];
        } catch (Throwable) {
            return $this->data = $fallback;
        }
    }

    public function refresh(): void
    {
        $this->data = null;
    }
}
