<?php

declare(strict_types=1);

namespace MeShaon\RequestAnalytics\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use MeShaon\RequestAnalytics\Services\DashboardAnalyticsService;

class RequestAnalyticsController extends BaseController
{
    public function __construct(protected DashboardAnalyticsService $dashboardService) {}

    public function show(Request $request)
    {
        $dateRangeInput = $request->input('date_range', 30);
        $dateRange = is_numeric($dateRangeInput) && (int) $dateRangeInput > 0
            ? (int) $dateRangeInput
            : 30;

        $requestCategory = $request->input('request_category');

        $this->dashboardService->setDateRange($dateRange);
        if ($requestCategory) {
            $this->dashboardService->setRequestCategory($requestCategory);
        }

        $data = $this->dashboardService->getDashboardData();

        return view('request-analytics::analytics', $data);
    }
}
