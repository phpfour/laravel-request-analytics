<?php

declare(strict_types=1);

namespace MeShaon\RequestAnalytics\Tests\Feature\Web;

use MeShaon\RequestAnalytics\Models\RequestAnalytics;
use MeShaon\RequestAnalytics\Tests\Feature\BaseFeatureTestCase;
use PHPUnit\Framework\Attributes\Test;

class DashboardTest extends BaseFeatureTestCase
{
    #[Test]
    public function it_returns_dashboard_view(): void
    {
        RequestAnalytics::factory()->count(10)->create();

        $response = $this->get(route('request-analytics.dashboard'));

        $response->assertOk()
            ->assertViewIs('request-analytics::analytics');
    }

    #[Test]
    public function it_returns_dashboard_view_with_empty_data(): void
    {
        $response = $this->get(route('request-analytics.dashboard'));

        $response->assertOk()
            ->assertViewIs('request-analytics::analytics');
    }

    #[Test]
    public function it_accepts_date_range_parameter(): void
    {
        RequestAnalytics::factory()->count(15)->create();

        $response = $this->get(route('request-analytics.dashboard', ['date_range' => 7]));

        $response->assertOk()
            ->assertViewIs('request-analytics::analytics');
    }

    #[Test]
    public function it_handles_invalid_date_range_parameter(): void
    {
        RequestAnalytics::factory()->count(5)->create();

        $response = $this->get(route('request-analytics.dashboard', ['date_range' => 'invalid']));

        $response->assertOk()
            ->assertViewIs('request-analytics::analytics');
    }

    #[Test]
    public function it_handles_negative_date_range_parameter(): void
    {
        RequestAnalytics::factory()->count(5)->create();

        $response = $this->get(route('request-analytics.dashboard', ['date_range' => -10]));

        $response->assertOk()
            ->assertViewIs('request-analytics::analytics');
    }

    #[Test]
    public function it_handles_zero_date_range_parameter(): void
    {
        RequestAnalytics::factory()->count(5)->create();

        $response = $this->get(route('request-analytics.dashboard', ['date_range' => 0]));

        $response->assertOk()
            ->assertViewIs('request-analytics::analytics');
    }

    #[Test]
    public function it_handles_string_numeric_date_range(): void
    {
        RequestAnalytics::factory()->count(5)->create();

        $response = $this->get(route('request-analytics.dashboard', ['date_range' => '60']));

        $response->assertOk()
            ->assertViewIs('request-analytics::analytics');
    }

    #[Test]
    public function it_displays_analytics_data_with_different_date_ranges(): void
    {
        RequestAnalytics::factory()->count(20)->create();

        foreach ([1, 7, 30, 90] as $dateRange) {
            $response = $this->get(route('request-analytics.dashboard', ['date_range' => $dateRange]));

            $response->assertOk();
        }
    }
}
