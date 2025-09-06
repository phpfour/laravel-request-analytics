<?php

declare(strict_types=1);

namespace MeShaon\RequestAnalytics\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use MeShaon\RequestAnalytics\Models\RequestAnalytics;

class AnalyticsService
{
    public function getDateRange(array $params): array
    {
        if (isset($params['start_date']) && isset($params['end_date'])) {
            $startDate = Carbon::parse($params['start_date'])->startOfDay();
            $endDate = Carbon::parse($params['end_date'])->endOfDay();
            $days = $startDate->diffInDays($endDate);
        } else {
            $days = $params['date_range'] ?? 30;
            $startDate = Carbon::now()->subDays($days)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
            'days' => $days,
            'key' => $startDate->format('Y-m-d').'_'.$endDate->format('Y-m-d'),
        ];
    }

    public function getBaseQuery(array $dateRange): Builder
    {
        return RequestAnalytics::whereBetween('visited_at', [$dateRange['start'], $dateRange['end']]);
    }

    public function getSummary($query): array
    {
        $totalViews = (clone $query)->count();
        $uniqueVisitors = (clone $query)->distinct('visitor_id')->count('visitor_id');
        $uniqueSessions = (clone $query)->distinct('session_id')->count('session_id');
        $avgResponseTime = (clone $query)->avg('response_time');

        return [
            'total_views' => $totalViews,
            'unique_visitors' => $uniqueVisitors,
            'unique_sessions' => $uniqueSessions,
            'avg_response_time' => round($avgResponseTime, 2),
        ];
    }

    public function getChartData($query, array $dateRange): array
    {
        $data = (clone $query)
            ->select(
                DB::raw('DATE(visited_at) as date'),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT visitor_id) as visitors')
            )
            ->groupBy(DB::raw('DATE(visited_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $views = [];
        $visitors = [];

        $current = clone $dateRange['start'];
        while ($current <= $dateRange['end']) {
            $dateStr = $current->format('Y-m-d');
            $labels[] = $current->format('M d');

            if ($data->has($dateStr)) {
                $views[] = $data->get($dateStr)->views;
                $visitors[] = $data->get($dateStr)->visitors;
            } else {
                $views[] = 0;
                $visitors[] = 0;
            }

            $current->addDay();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Views', 'data' => $views],
                ['label' => 'Visitors', 'data' => $visitors],
            ],
        ];
    }

    public function getTopPages($query): array
    {
        return (clone $query)
            ->select('path', DB::raw('COUNT(*) as views'))
            ->groupBy('path')
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getTopReferrers($query): array
    {
        return (clone $query)
            ->select(
                DB::raw('SUBSTRING_INDEX(SUBSTRING_INDEX(referrer, "/", 3), "//", -1) as domain'),
                DB::raw('COUNT(*) as visits')
            )
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->groupBy('domain')
            ->orderBy('visits', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getBrowsers($query): array
    {
        return (clone $query)
            ->select('browser', DB::raw('COUNT(*) as count'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getDevices($query): array
    {
        return (clone $query)
            ->select('device', DB::raw('COUNT(*) as count'))
            ->whereNotNull('device')
            ->groupBy('device')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getCountries($query): array
    {
        return (clone $query)
            ->select('country', DB::raw('COUNT(*) as count'))
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getOverviewData(array $params): array
    {
        $dateRange = $this->getDateRange($params);
        $query = $this->getBaseQuery($dateRange);

        return [
            'summary' => $this->getSummary($query),
            'chart' => $this->getChartData($query, $dateRange),
            'top_pages' => $this->getTopPages($query),
            'top_referrers' => $this->getTopReferrers($query),
            'browsers' => $this->getBrowsers($query),
            'devices' => $this->getDevices($query),
            'countries' => $this->getCountries($query),
        ];
    }

    public function getVisitors(array $params, int $perPage = 50)
    {
        $dateRange = $this->getDateRange($params);

        return $this->getBaseQuery($dateRange)
            ->select(
                'visitor_id',
                DB::raw('COUNT(*) as page_views'),
                DB::raw('COUNT(DISTINCT session_id) as sessions'),
                DB::raw('MIN(visited_at) as first_visit'),
                DB::raw('MAX(visited_at) as last_visit'),
                DB::raw('COUNT(DISTINCT path) as unique_pages')
            )
            ->whereNotNull('visitor_id')
            ->groupBy('visitor_id')
            ->orderBy('last_visit', 'desc')
            ->paginate($perPage);
    }

    public function getPageViews(array $params, int $perPage = 50)
    {
        $dateRange = $this->getDateRange($params);
        $query = $this->getBaseQuery($dateRange);

        if (isset($params['path'])) {
            $query->where('path', 'like', "%{$params['path']}%");
        }

        return $query
            ->select('*')
            ->orderBy('visited_at', 'desc')
            ->paginate($perPage);
    }
}