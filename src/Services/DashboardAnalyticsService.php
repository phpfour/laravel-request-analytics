<?php

declare(strict_types=1);

namespace MeShaon\RequestAnalytics\Services;

use Illuminate\Database\Eloquent\Builder;
use Carbon\CarbonInterval;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    public function __construct(protected AnalyticsService $analyticsService, protected int $dateRange = 30, protected ?string $requestCategory = null) {}

    public function setDateRange(int $dateRange): self
    {
        $this->dateRange = $dateRange;

        return $this;
    }

    public function setRequestCategory(?string $requestCategory): self
    {
        $this->requestCategory = $requestCategory;

        return $this;
    }

    public function getDashboardData(): array
    {
        $dateRange = $this->getDateRange();
        $query = $this->getBaseQuery($dateRange);
        $cacheTtl = config('request-analytics.cache.ttl', 5);

        $cacheKeySuffix = $this->requestCategory ? "_{$this->requestCategory}" : '';

        $chartData = $this->getChartData();

        return [
            'browsers' => $this->analyticsService->getBrowsers($query, true, "analytics_browsers_{$this->dateRange}{$cacheKeySuffix}", $cacheTtl),
            'operatingSystems' => $this->analyticsService->getOperatingSystems($query, true),
            'devices' => $this->analyticsService->getDevices($query, true),
            'pages' => $this->analyticsService->getTopPages($query, true),
            'referrers' => $this->analyticsService->getTopReferrers($query, true),
            'labels' => $chartData['labels'],
            'datasets' => $chartData['datasets'],
            'average' => $this->getAverage(),
            'countries' => $this->analyticsService->getCountries($query, true, "analytics_countries_{$this->dateRange}{$cacheKeySuffix}", $cacheTtl),
            'dateRange' => $this->dateRange,
        ];
    }

    protected function getDateRange(): array
    {
        return [
            'start' => Carbon::now()->subDays($this->dateRange)->startOfDay(),
            'end' => Carbon::now()->endOfDay(),
        ];
    }

    protected function getBaseQuery(array $dateRange): Builder
    {
        return $this->analyticsService->getBaseQuery($dateRange, $this->requestCategory);
    }

    protected function getChartData(): array
    {
        $dateRange = $this->getDateRange();
        $query = $this->getBaseQuery($dateRange);
        $chartData = $this->analyticsService->getChartData($query, $dateRange);

        // Add dashboard-specific styling
        $chartData['datasets'] = collect($chartData['datasets'])->map(function (array $dataset): array {
            if ($dataset['label'] === 'Views') {
                return array_merge($dataset, [
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1,
                ]);
            }
            if ($dataset['label'] === 'Visitors') {
                return array_merge($dataset, [
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ]);
            }

            return $dataset;
        })->toArray();

        return $chartData;
    }

    protected function getAverage(): array
    {
        $dateRange = $this->getDateRange();
        $startDate = $dateRange['start'];
        $baseQuery = $this->getBaseQuery($dateRange);

        $totalViews = $baseQuery->count();
        $uniqueVisitors = $baseQuery->distinct('session_id')->count('session_id');

        // Calculate bounce rate (percentage of sessions with only one page view)
        $tableName = config('request-analytics.database.table', 'request_analytics');
        $connection = config('request-analytics.database.connection');

        $sessionsWithSinglePageView = DB::connection($connection)->table(function ($query) use ($startDate, $tableName): void {
            $query->from($tableName)
                ->select('session_id')
                ->where('visited_at', '>=', $startDate);

            if ($this->requestCategory) {
                $query->where('request_category', $this->requestCategory);
            }

            $query->groupBy('session_id')
                ->havingRaw('COUNT(*) = 1');
        }, 'single_page_sessions')->count();

        $bounceRate = $uniqueVisitors > 0
            ? round(($sessionsWithSinglePageView / $uniqueVisitors) * 100, 1)
            : 0;

        // Calculate average visit time
        $durationExpression = $this->analyticsService->getDurationExpression('visited_at');
        $sessionTimes = (clone $baseQuery)
            ->select(
                'session_id',
                DB::raw("{$durationExpression} as duration")
            )
            ->groupBy('session_id')
            ->having('duration', '>', 0)
            ->pluck('duration')
            ->toArray();

        $avgVisitTime = count($sessionTimes) > 0
            ? round(array_sum($sessionTimes) / count($sessionTimes), 1)
            : 0;

        return [
            'views' => $totalViews,
            'visitors' => $uniqueVisitors,
            'bounce-rate' => $bounceRate.'%',
            'average-visit-time' => $this->formatTimeWithCarbon($avgVisitTime),
        ];
    }

    protected function formatTimeWithCarbon($seconds): string
    {
        if ($seconds <= 0) {
            return '0s';
        }

        return CarbonInterval::seconds($seconds)
            ->cascade()
            ->forHumans([
                'short' => true,
                'minimumUnit' => 'second',
            ]);
    }
}
