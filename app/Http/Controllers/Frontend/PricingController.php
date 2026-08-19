<?php

namespace App\Http\Controllers\Frontend;

use App\Models\PricingPlan;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PricingController extends FrontendController
{
    public function index(): View
    {
        return view('frontend.pricing', [
            'pricingPlans' => Schema::hasTable('pricing_plans')
                ? PricingPlan::query()
                    ->where('is_active', true)
                    ->orderByDesc('is_featured')
                    ->orderBy('sort_order')
                    ->get()
                : collect(),
        ]);
    }
}
