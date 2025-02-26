<?php

namespace MeShaon\RequestAnalytics;

use MeShaon\RequestAnalytics\Services\RequestAnalyticsService;

class RequestAnalytics
{
    public function __construct(private RequestAnalyticsService $requestAnalyticsService) {}

    public function getPages() {}

    public function getMostVisitedPages() {}

    public function getVisitors() {}

    public function getVisits() {}

    public function getReferrers() {}

    public function getOperatingSystems() {}

    public function getBrowsers() {}

    public function getDevices() {}

    public function getAverageVisitTime() {}

    public function getAverageBounceRate() {}

    public function getBarChartData() {}
}
