<?php

namespace App\Listeners;

use App\Events\ComplaintCreated;
use App\Models\ExternalIntervention;
use App\Models\ExternalInterventionLog;
use App\Models\Intervention;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateInterventionFromComplaint
{
    public function handle(ComplaintCreated $event): void
    {
        try {
            $this->createIntervention($event->complaint);
        } catch (\Throwable $e) {
            Log::warning('CreateInterventionFromComplaint failed (non-blocking)', [
                'complaint_id' => (int) $event->complaint->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function createIntervention(\App\Models\Complaint $complaint): void
    {
        $complaint->loadMissing(['equipment:id,company_id,service_id', 'service:id,name']);

        $equipment = $complaint->equipment;
        if (!$equipment) {
            return;
        }

        DB::transaction(function () use ($complaint, $equipment) {
            $existing = Intervention::query()
                ->where('complaint_id', (int) $complaint->id)
                ->first();

            if ($existing) {
                return;
            }

            $intervention = Intervention::query()->create([
                'code' => $this->generateInterventionCode(),
                'equipment_id' => (int) $equipment->id,
                'complaint_id' => (int) $complaint->id,
                'type' => 'Curative',
                'maintenance_scope' => (int) ($equipment->company_id ?? 0) > 0 ? 'externe' : 'interne',
                'status' => 'en_attente',
                'date_start' => optional($complaint->created_at)->toDateString() ?: now()->toDateString(),
            ]);

            if ((int) ($equipment->company_id ?? 0) <= 0 || !Schema::hasTable('external_interventions')) {
                return;
            }

            $externalIntervention = ExternalIntervention::query()->create([
                'ticket_number' => $this->generateExternalTicketNumber($intervention->id),
                'intervention_id' => (int) $intervention->id,
                'equipment_id' => (int) $equipment->id,
                'company_id' => (int) $equipment->company_id,
                'service_name' => $complaint->service?->name,
                'failure_datetime' => $complaint->created_at,
                'status' => 'ouvert',
                'intervention_status' => 'ouvert',
            ]);

            if (Schema::hasTable('external_intervention_logs')) {
                ExternalInterventionLog::query()->create([
                    'external_intervention_id' => (int) $externalIntervention->id,
                    'user_id' => null,
                    'action_type' => 'ticket_created',
                    'from_status' => null,
                    'to_status' => 'ouvert',
                    'payload' => [
                        'complaint_id' => (int) $complaint->id,
                        'intervention_id' => (int) $intervention->id,
                        'auto_created' => true,
                    ],
                    'logged_at' => now(),
                ]);
            }
        });
    }

    private function generateInterventionCode(): string
    {
        $year = now()->format('Y');

        // No nested transaction — caller already wraps in DB::transaction.
        $latestCode = Intervention::query()
            ->where('code', 'like', 'INT-' . $year . '-%')
            ->orderByDesc('code')
            ->lockForUpdate()
            ->value('code');

        $sequence = 1;
        if (is_string($latestCode) && preg_match('/^INT-' . $year . '-(\d{4})$/', $latestCode, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return sprintf('INT-%s-%04d', $year, $sequence);
    }

    private function generateExternalTicketNumber(int $interventionId): string
    {
        return sprintf('SAV-%s-%05d', now()->format('Y'), $interventionId);
    }
}
