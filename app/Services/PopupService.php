<?php

namespace App\Services;

use App\Models\Popup;
use Illuminate\Support\Facades\Schema;

class PopupService
{
    public function activeForPage(bool $isHome): ?Popup
    {
        if (! Schema::hasTable('popups')) {
            return null;
        }

        return Popup::query()
            ->visible()
            ->forPage($isHome)
            ->with('image')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->first();
    }
}
