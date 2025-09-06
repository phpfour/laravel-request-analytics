<?php

declare(strict_types=1);

namespace MeShaon\RequestAnalytics\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use MeShaon\RequestAnalytics\Services\DashboardAnalyticsService;

class RequestAnalyticsController extends BaseController
{
    protected DashboardAnalyticsService $dashboardService;

    public function __construct(DashboardAnalyticsService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function show(Request $request)
    {
        $dateRange = $request->input('date_range', 30);
        $this->dashboardService->setDateRange($dateRange);

        $data = $this->dashboardService->getDashboardData();

        return view('request-analytics::analytics', $data);
    }
}
