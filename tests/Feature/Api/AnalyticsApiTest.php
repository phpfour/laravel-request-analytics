<?php

declare(strict_types=1);

namespace MeShaon\RequestAnalytics\Tests\Feature\Api;

use MeShaon\RequestAnalytics\Models\RequestAnalytics;
use MeShaon\RequestAnalytics\Tests\Feature\BaseFeatureTestCase;
use PHPUnit\Framework\Attributes\Test;

class AnalyticsApiTest extends BaseFeatureTestCase
{
    #[Test]
    public function it_returns_overview_data_with_default_parameters(): void
    {
        RequestAnalytics::factory()->count(10)->create();

        $response = $this->getJson(route('request-analytics.api.overview'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'summary' => [
                        'total_views',
                        'unique_visitors',
                        'unique_sessions',
                        'avg_response_time',
                    ],
                    'chart',
                    'top_pages',
                    'top_referrers',
                    'browsers',
                    'operating_systems',
                    'devices',
                    'countries',
                ],
                'date_range' => [
                    'start',
                    'end',
                    'key',
                ],
            ]);
    }

    #[Test]
    public function it_returns_overview_data_with_date_range_parameter(): void
    {
        RequestAnalytics::factory()->count(5)->create();

        $response = $this->getJson(route('request-analytics.api.overview', ['date_range' => 7]));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'date_range',
            ]);
    }

    #[Test]
    public function it_returns_overview_data_with_custom_date_range(): void
    {
        RequestAnalytics::factory()->count(5)->create();

        $startDate = now()->subDays(30)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->getJson(route('request-analytics.api.overview', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'date_range',
            ]);
    }

    #[Test]
    public function it_returns_overview_data_with_percentages(): void
    {
        RequestAnalytics::factory()->count(10)->create();

        $response = $this->getJson(route('request-analytics.api.overview', ['with_percentages' => 'true']));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'date_range',
            ]);
    }

    #[Test]
    public function it_validates_invalid_date_range(): void
    {
        $response = $this->getJson(route('request-analytics.api.overview', ['date_range' => 500]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_range']);
    }

    #[Test]
    public function it_validates_invalid_date_format(): void
    {
        $response = $this->getJson(route('request-analytics.api.overview', [
            'start_date' => 'invalid-date',
            'end_date' => '2024-12-31',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    #[Test]
    public function it_validates_end_date_before_start_date(): void
    {
        $response = $this->getJson(route('request-analytics.api.overview', [
            'start_date' => '2024-12-31',
            'end_date' => '2024-01-01',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    #[Test]
    public function it_returns_empty_overview_data(): void
    {
        $response = $this->getJson(route('request-analytics.api.overview'));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'summary' => [
                        'total_views' => 0,
                        'unique_visitors' => 0,
                    ],
                ],
            ]);
    }

    #[Test]
    public function it_returns_visitors_data(): void
    {
        RequestAnalytics::factory()->count(15)->create();

        $response = $this->getJson(route('request-analytics.api.visitors'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'current_page',
                    'data',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total',
                ],
            ]);
    }

    #[Test]
    public function it_returns_visitors_data_with_custom_per_page(): void
    {
        RequestAnalytics::factory()->count(30)->create();

        $response = $this->getJson(route('request-analytics.api.visitors', ['per_page' => 10]));

        $response->assertOk()
            ->assertJsonPath('data.per_page', 10);
    }

    #[Test]
    public function it_returns_visitors_data_with_date_filter(): void
    {
        RequestAnalytics::factory()->count(10)->create();

        $response = $this->getJson(route('request-analytics.api.visitors', ['date_range' => 7]));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
            ]);
    }

    #[Test]
    public function it_returns_page_views_data(): void
    {
        RequestAnalytics::factory()->count(15)->create();

        $response = $this->getJson(route('request-analytics.api.page-views'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'current_page',
                    'data',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total',
                ],
            ]);
    }

    #[Test]
    public function it_returns_page_views_data_with_custom_per_page(): void
    {
        RequestAnalytics::factory()->count(30)->create();

        $response = $this->getJson(route('request-analytics.api.page-views', ['per_page' => 15]));

        $response->assertOk()
            ->assertJsonPath('data.per_page', 15);
    }

    #[Test]
    public function it_returns_page_views_data_with_date_filter(): void
    {
        RequestAnalytics::factory()->count(10)->create();

        $response = $this->getJson(route('request-analytics.api.page-views', ['date_range' => 30]));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
            ]);
    }

    #[Test]
    public function it_caches_overview_data(): void
    {
        RequestAnalytics::factory()->count(5)->create();

        $response1 = $this->getJson(route('request-analytics.api.overview'));
        $response2 = $this->getJson(route('request-analytics.api.overview'));

        $response1->assertOk();
        $response2->assertOk();

        $this->assertEquals($response1->json(), $response2->json());
    }
}
