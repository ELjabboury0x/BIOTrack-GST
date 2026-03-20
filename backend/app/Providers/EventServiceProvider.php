<?php

namespace App\Providers;

use App\Events\ComplaintCreated;
use App\Listeners\CreateInterventionFromComplaint;
use App\Listeners\SendComplaintCreatedNotification;
use App\Models\Complaint;
use App\Models\Equipment;
use App\Models\ExternalCompanyPlanning;
use App\Models\Intervention;
use App\Models\Market;
use App\Models\PreventiveMaintenance;
use App\Models\SparePart;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Throwable;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ComplaintCreated::class => [
            SendComplaintCreatedNotification::class,
            CreateInterventionFromComplaint::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();

        $models = [
            Equipment::class,
            Intervention::class,
            Complaint::class,
            PreventiveMaintenance::class,
            SparePart::class,
            ExternalCompanyPlanning::class,
            Market::class,
        ];

        foreach ($models as $modelClass) {
            $modelClass::saved(function () {
                $this->broadcastDashboardRealtimeUpdate();
            });

            $modelClass::deleted(function () {
                $this->broadcastDashboardRealtimeUpdate();
            });
        }
    }

    private function broadcastDashboardRealtimeUpdate(): void
    {
        try {
            app(DashboardMetricsService::class)->invalidateCache();
        } catch (Throwable $e) {
        }
    }
}
