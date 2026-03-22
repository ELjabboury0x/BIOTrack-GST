<?php

namespace App\Services;

use App\Models\Complaint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RealtimeMetricsBroadcaster
{
    public function broadcastDashboardMetrics(array $metrics): void
    {
        if (!Cache::add('dashboard_metrics:broadcast:throttle', 1, now()->addSeconds(3))) {
            return;
        }

        $this->broadcast('dashboard.metrics', [
            'kpi' => $metrics['kpi'] ?? [],
            'charts' => $metrics['charts'] ?? [],
            'hasData' => $metrics['hasData'] ?? false,
            'updated_at' => now()->toDateTimeString(),
        ]);
    }

    public function broadcastComplaintCreated(Complaint $complaint): void
    {
        $this->broadcast('complaint.created', [
            'id' => $complaint->id,
            'service_id' => $complaint->service_id,
            'service_code' => $complaint->service?->code,
            'service_name' => $complaint->service?->name,
            'equipment_id' => $complaint->equipment_id,
            'equipment_label' => trim((string) (($complaint->equipment?->inventory_number_current ?: '') . ' - ' . ($complaint->equipment?->designation ?: ''))),
            'priority' => $complaint->priority,
            'status' => $complaint->status,
            'created_at' => optional($complaint->created_at)->toDateTimeString(),
        ]);
    }

    public function broadcastGlobalChange(string $entity, string $action): void
    {
        if (!Cache::add('gmao_global_change:broadcast:throttle', 1, now()->addSeconds(1))) {
            return;
        }

        $this->broadcast('gmao.changed', [
            'entity' => $entity,
            'action' => $action,
            'updated_at' => now()->toDateTimeString(),
        ]);
    }

    private function broadcast(string $channel, array $payload): void
    {
        try {
            Cache::increment('dashboard_metrics:version');
        } catch (Throwable $e) {
            Cache::forever('dashboard_metrics:version', (int) Cache::get('dashboard_metrics:version', 1) + 1);
        }

        if (!filter_var(env('REALTIME_ENABLED', false), FILTER_VALIDATE_BOOL)) {
            return;
        }

        $url = env('REALTIME_SERVER_URL', 'http://127.0.0.1:6001/broadcast');
        $token = env('REALTIME_SECRET', 'gmao-realtime-secret');

        try {
            Http::timeout(1)->post($url, [
                'token' => $token,
                'channel' => $channel,
                'payload' => $payload,
            ]);
        } catch (Throwable $e) {
            Log::warning('realtime_broadcast_failed', [
                'channel' => $channel,
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
