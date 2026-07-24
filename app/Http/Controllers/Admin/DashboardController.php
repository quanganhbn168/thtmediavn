<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    /**
     * Create a new controller instance.
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the admin dashboard page.
     */
    public function index(): View
    {
        $stats = $this->dashboardService->getDashboardStats(auth()->user());

        return view('admin.dashboard', $stats);
    }
}
