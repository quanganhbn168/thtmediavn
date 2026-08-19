<?php

namespace App\Http\Controllers\Frontend;

use App\Models\CompanyContent;
use App\Settings\AboutSettings;
use Illuminate\View\View;

class AboutController extends FrontendController
{
    public function index(): View
    {
        $settings = app(AboutSettings::class);
        $companyContents = CompanyContent::query()
            ->visibleOnSite()
            ->with('slugs')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('frontend.about', [
            'aboutSettings' => $settings,
            'aboutPageLabel' => $this->localized($settings->about_page_label, 'Giới thiệu'),
            'aboutPageTitle' => $this->localized($settings->about_page_title, 'Năng lực, cách làm và những giá trị THT Media theo đuổi'),
            'aboutPageIntro' => $this->localized($settings->about_page_intro, ''),
            'aboutContentGroups' => $companyContents->groupBy('type'),
        ]);
    }

    /** @param array<string, string> $value */
    private function localized(array $value, string $fallback): string
    {
        return trim((string) data_get($value, 'vi', '')) ?: $fallback;
    }

}
