<?php

namespace Tests\Unit;

use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_returns_expected_kpi_and_external_company_structure(): void
    {
        $service = app(DashboardMetricsService::class);

        $metrics = $service->build();

        $this->assertArrayHasKey('kpi', $metrics);
        $this->assertArrayHasKey('charts', $metrics);
        $this->assertArrayHasKey('external_companies', $metrics['charts']);
        $this->assertArrayHasKey('top5', $metrics['charts']['external_companies']);
        $this->assertArrayHasKey('planning_societes_a_venir', $metrics['kpi']);
    }

    public function test_invalidate_cache_increments_metrics_version(): void
    {
        $service = app(DashboardMetricsService::class);

        Cache::forget('dashboard_metrics:version');
        $before = (int) Cache::get('dashboard_metrics:version', 1);

        $service->invalidateCache();

        $after = (int) Cache::get('dashboard_metrics:version', 1);

        $this->assertTrue($after > $before);
    }
}
