<?php

declare(strict_types=1);

namespace MeShaon\RequestAnalytics\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
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
        $dateExpression = $this->getDateExpression('visited_at');
        
        $data = (clone $query)
            ->select(
                DB::raw("{$dateExpression} as date"),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT visitor_id) as visitors')
            )
            ->groupBy(DB::raw($dateExpression))
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

    public function getTopPages($query, bool $withPercentages = false): array
    {
        $pages = (clone $query)
            ->select('path', DB::raw('COUNT(*) as views'))
            ->groupBy('path')
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        if (!$withPercentages) {
            return $pages;
        }

        $totalViews = array_sum(array_column($pages, 'views'));
        if ($totalViews === 0) {
            return [];
        }

        return collect($pages)->map(function ($page) use ($totalViews) {
            $percentage = round(($page['views'] / $totalViews) * 100, 1);
            return [
                'path' => $page['path'],
                'views' => $page['views'],
                'percentage' => $percentage,
            ];
        })->toArray();
    }

    public function getTopReferrers($query, bool $withPercentages = false): array
    {
        $domainExpression = $this->getDomainExpression('referrer');
        
        $referrers = (clone $query)
            ->select(
                DB::raw("{$domainExpression} as domain"),
                DB::raw('COUNT(*) as visits')
            )
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->groupBy(DB::raw($domainExpression))
            ->orderBy('visits', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        if (!$withPercentages) {
            return $referrers;
        }

        $totalVisits = array_sum(array_column($referrers, 'visits'));
        if ($totalVisits === 0) {
            return [];
        }

        return collect($referrers)->map(function ($referrer) use ($totalVisits) {
            $percentage = round(($referrer['visits'] / $totalVisits) * 100, 1);
            return [
                'domain' => $referrer['domain'] ?: '(direct)',
                'visits' => $referrer['visits'],
                'percentage' => $percentage,
            ];
        })->toArray();
    }

    public function getBrowsers($query, bool $withPercentages = false, string $cacheKey = null, int $cacheTtl = null): array
    {
        if ($cacheKey && $cacheTtl) {
            return Cache::remember($cacheKey, now()->addMinutes($cacheTtl), function () use ($query, $withPercentages) {
                return $this->getBrowsersData($query, $withPercentages);
            });
        }

        return $this->getBrowsersData($query, $withPercentages);
    }

    protected function getBrowsersData($query, bool $withPercentages): array
    {
        $browsers = (clone $query)
            ->select('browser', DB::raw('COUNT(*) as count'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        if (!$withPercentages) {
            return $browsers;
        }

        $totalCount = array_sum(array_column($browsers, 'count'));
        if ($totalCount === 0) {
            return [];
        }

        return collect($browsers)->map(function ($browser) use ($totalCount) {
            $percentage = round(($browser['count'] / $totalCount) * 100, 1);
            return [
                'browser' => $browser['browser'],
                'count' => $browser['count'],
                'percentage' => $percentage,
            ];
        })->toArray();
    }

    public function getDevices($query, bool $withPercentages = false): array
    {
        $devices = (clone $query)
            ->select('device', DB::raw('COUNT(*) as count'))
            ->whereNotNull('device')
            ->groupBy('device')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        if (!$withPercentages) {
            return $devices;
        }

        $totalCount = array_sum(array_column($devices, 'count'));
        if ($totalCount === 0) {
            return [];
        }

        return collect($devices)->map(function ($device) use ($totalCount) {
            $percentage = round(($device['count'] / $totalCount) * 100, 1);
            return [
                'name' => $device['device'],
                'count' => $device['count'],
                'percentage' => $percentage,
            ];
        })->toArray();
    }

    public function getCountries($query, bool $withPercentages = false, string $cacheKey = null, int $cacheTtl = null): array
    {
        if ($cacheKey && $cacheTtl) {
            return Cache::remember($cacheKey, now()->addMinutes($cacheTtl), function () use ($query, $withPercentages) {
                return $this->getCountriesData($query, $withPercentages);
            });
        }

        return $this->getCountriesData($query, $withPercentages);
    }

    protected function getCountriesData($query, bool $withPercentages): array
    {
        $countries = (clone $query)
            ->select('country', DB::raw('COUNT(*) as count'))
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        if (!$withPercentages) {
            return $countries;
        }

        $totalCount = array_sum(array_column($countries, 'count'));
        if ($totalCount === 0) {
            return [];
        }

        return collect($countries)->map(function ($country) use ($totalCount) {
            $percentage = round(($country['count'] / $totalCount) * 100, 1);
            return [
                'name' => $country['country'],
                'count' => $country['count'],
                'percentage' => $percentage,
                'code' => strtolower($country['country']),
            ];
        })->toArray();
    }

    public function getOperatingSystems($query, bool $withPercentages = false): array
    {
        $totalVisitors = (clone $query)->distinct('session_id')->count('session_id');
        if ($totalVisitors === 0) {
            return [];
        }

        $operatingSystems = (clone $query)
            ->select('operating_system as name', DB::raw('COUNT(DISTINCT session_id) as count'))
            ->whereNotNull('operating_system')
            ->groupBy('operating_system')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        if (!$withPercentages) {
            return $operatingSystems;
        }

        return collect($operatingSystems)->map(function ($os) use ($totalVisitors) {
            $percentage = round(($os['count'] / $totalVisitors) * 100, 1);
            return [
                'name' => $os['name'],
                'count' => $os['count'],
                'percentage' => $percentage,
            ];
        })->toArray();
    }

    public function getOverviewData(array $params): array
    {
        $dateRange = $this->getDateRange($params);
        $query = $this->getBaseQuery($dateRange);
        $withPercentages = $params['with_percentages'] ?? false;

        return [
            'summary' => $this->getSummary($query),
            'chart' => $this->getChartData($query, $dateRange),
            'top_pages' => $this->getTopPages($query, $withPercentages),
            'top_referrers' => $this->getTopReferrers($query, $withPercentages),
            'browsers' => $this->getBrowsers($query, $withPercentages),
            'devices' => $this->getDevices($query, $withPercentages),
            'countries' => $this->getCountries($query, $withPercentages),
            'operating_systems' => $this->getOperatingSystems($query, $withPercentages),
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

    public function getDateExpression(string $column): string
    {
        $driver = DB::connection()->getDriverName();
        
        return match ($driver) {
            'mysql' => "DATE({$column})",
            'pgsql' => "DATE({$column})",
            'sqlite' => "DATE({$column})",
            default => "DATE({$column})"
        };
    }

    public function getDomainExpression(string $column): string
    {
        $driver = DB::connection()->getDriverName();
        
        return match ($driver) {
            'mysql' => "SUBSTRING_INDEX(SUBSTRING_INDEX({$column}, '/', 3), '//', -1)",
            'pgsql' => "SPLIT_PART(SPLIT_PART({$column}, '/', 3), '//', 2)",
            'sqlite' => "CASE 
                WHEN {$column} LIKE '%://%' THEN 
                    REPLACE(
                        REPLACE(
                            SUBSTR({$column}, INSTR({$column}, '://') + 3),
                            SUBSTR(SUBSTR({$column}, INSTR({$column}, '://') + 3), INSTR(SUBSTR({$column}, INSTR({$column}, '://') + 3), '/'))
                            , ''
                        ), 
                        'www.', ''
                    )
                ELSE {$column}
                END",
            default => "SUBSTRING_INDEX(SUBSTRING_INDEX({$column}, '/', 3), '//', -1)"
        };
    }

    public function getDurationExpression(string $column): string
    {
        $driver = DB::connection()->getDriverName();
        
        return match ($driver) {
            'mysql' => "TIMESTAMPDIFF(SECOND, MIN({$column}), MAX({$column}))",
            'pgsql' => "EXTRACT(EPOCH FROM (MAX({$column}) - MIN({$column})))",
            'sqlite' => "CAST((julianday(MAX({$column})) - julianday(MIN({$column}))) * 86400 AS INTEGER)",
            default => "TIMESTAMPDIFF(SECOND, MIN({$column}), MAX({$column}))"
        };
    }

}