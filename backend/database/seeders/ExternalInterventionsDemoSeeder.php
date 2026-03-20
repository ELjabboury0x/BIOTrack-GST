<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\Company;
use App\Models\ExternalIntervention;
use App\Models\Intervention;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ExternalInterventionsDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('external_interventions') || !Schema::hasTable('interventions') || !Schema::hasTable('equipments')) {
            return;
        }

        if (!Schema::hasTable('companies')) {
            return;
        }

        $companies = Company::query()->inRandomOrder()->limit(10)->get(['id']);

        if ($companies->isEmpty()) {
            $demoNames = [
                'Société Externe Alpha',
                'Société Externe Beta',
                'Société Externe Gamma',
            ];

            foreach ($demoNames as $name) {
                Company::query()->firstOrCreate(['name' => $name]);
            }

            $companies = Company::query()->inRandomOrder()->limit(10)->get(['id']);
        }

        if ($companies->isEmpty()) {
            return;
        }

        $equipments = Equipment::query()
            ->inRandomOrder()
            ->limit(30)
            ->get(['id']);

        if ($equipments->isEmpty()) {
            return;
        }

        foreach ($equipments as $index => $equipment) {
            $companyId = (int) $companies->random()->id;

            $start = Carbon::now()->subDays(random_int(2, 120))->setTime(random_int(8, 18), random_int(0, 59));
            $callAt = $start->copy()->addMinutes(random_int(5, 90));
            $arrivalAt = $callAt->copy()->addMinutes(random_int(20, 240));

            $isResolved = random_int(1, 100) <= 85;
            $resolvedAt = $isResolved ? $arrivalAt->copy()->addMinutes(random_int(30, 480)) : null;

            $status = $isResolved ? 'termine' : 'en_cours';

            $intervention = Intervention::query()->create([
                'code' => 'EXT-' . now()->format('Y') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT) . '-' . $equipment->id,
                'equipment_id' => (int) $equipment->id,
                'technician_name' => 'Technicien société externe',
                'type' => 'Curative',
                'maintenance_scope' => 'externe',
                'status' => $status,
                'date_start' => $start->toDateString(),
                'date_end' => $resolvedAt ? $resolvedAt->toDateString() : null,
                'closure_type' => $isResolved ? 'real' : null,
                'failure_cause' => $isResolved ? 'Panne matérielle corrigée' : null,
                'closure_note' => $isResolved ? 'Résolution après intervention de la société externe.' : 'Intervention en attente de résolution.',
                'closed_by_name' => $isResolved ? 'Société externe' : null,
                'closed_at' => $resolvedAt,
            ]);

            ExternalIntervention::query()->updateOrCreate(
                ['intervention_id' => $intervention->id],
                [
                    'equipment_id' => (int) $equipment->id,
                    'company_id' => $companyId,
                    'first_call_datetime' => $callAt,
                    'technician_arrival_datetime' => $arrivalAt,
                    'resolution_datetime' => $resolvedAt,
                    'status' => $isResolved ? 'resolved' : 'in_progress',
                    'notes' => 'Donnée de démonstration seed #' . ($index + 1),
                ]
            );
        }
    }
}
