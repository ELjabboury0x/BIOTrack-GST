<?php

namespace App\Console\Commands;

use App\Models\Equipment;
use App\Models\MaintenanceReport;
use App\Models\Service;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncCorrectiveBilanToMaintenanceReports extends Command
{
    protected $signature = 'gmao:sync-corrective-bilan-reports
        {--default-duration=120 : Durée par défaut (minutes) quand absente}
        {--status=validated : Statut cible (validated|closed)}';

    protected $description = 'Synchronise bilan_maintenance_correctives vers maintenance_reports (curative)';

    public function handle(): int
    {
        if (!Schema::hasTable('bilan_maintenance_correctives')) {
            $this->error('Table bilan_maintenance_correctives introuvable.');
            return self::FAILURE;
        }

        $status = trim((string) $this->option('status'));
        if (!in_array($status, [MaintenanceReport::STATUS_VALIDATED, MaintenanceReport::STATUS_CLOSED], true)) {
            $this->error('Option --status invalide. Valeurs acceptées: validated|closed');
            return self::FAILURE;
        }

        $defaultDuration = (int) $this->option('default-duration');
        if ($defaultDuration <= 0) {
            $defaultDuration = 120;
        }

        $technician = User::query()
            ->where('is_active', true)
            ->whereIn('role', ['technician', 'technicien', 'ingenieur', 'admin'])
            ->orderBy('id')
            ->first();

        if (!$technician) {
            $this->error('Aucun utilisateur actif (technicien/ingénieur/admin) trouvé pour créer les rapports.');
            return self::FAILURE;
        }

        $engineer = User::query()
            ->where('is_active', true)
            ->where('role', 'major')
            ->orderBy('id')
            ->first();

        $rows = DB::table('bilan_maintenance_correctives')
            ->orderBy('id')
            ->get([
                'id',
                'row_hash',
                'company_name',
                'equipment_designation',
                'brand_name',
                'model_name',
                'serial_number',
                'failure_details',
                'observations',
                'intervention_date_text',
            ]);

        $created = 0;
        $skippedExisting = 0;
        $skippedNoEquipment = 0;
        $skippedNoService = 0;

        $fallbackServiceId = (int) ($technician->service_id ?? 0);
        if ($fallbackServiceId <= 0) {
            $fallbackServiceId = (int) (Service::query()->orderBy('id')->value('id') ?? 0);
        }

        $hasInterventionScope = Schema::hasColumn('maintenance_reports', 'intervention_scope');

        foreach ($rows as $row) {
            $syncMarker = '[BILAN_HASH:' . (string) $row->row_hash . ']';

            $alreadyExists = MaintenanceReport::query()
                ->where('intervention_type', MaintenanceReport::TYPE_CURATIVE)
                ->where(function ($query) use ($syncMarker) {
                    $query
                        ->where('operations_performed', 'like', '%' . $syncMarker . '%')
                        ->orWhere('problem_description', 'like', '%' . $syncMarker . '%');
                })
                ->exists();

            if ($alreadyExists) {
                $skippedExisting++;
                continue;
            }

            $equipment = $this->resolveEquipment($row);
            if (!$equipment) {
                $skippedNoEquipment++;
                continue;
            }

            $serviceId = (int) ($equipment->service_id ?? 0);
            if ($serviceId <= 0) {
                $serviceId = $fallbackServiceId;
            }
            if ($serviceId <= 0) {
                $skippedNoService++;
                continue;
            }

            $interventionDate = $this->parseDate((string) ($row->intervention_date_text ?? ''));
            if ($interventionDate === null) {
                $interventionDate = now()->startOfDay();
            }

            $startedAt = $interventionDate->copy()->setTime(8, 0, 0);
            $endedAt = $startedAt->copy()->addMinutes($defaultDuration);

            $problemDescription = trim((string) ($row->failure_details ?? ''));
            if ($problemDescription === '') {
                $problemDescription = 'Panne importée depuis bilan corrective.';
            }
            $problemDescription .= "\n" . $syncMarker;

            $operations = trim((string) ($row->observations ?? ''));
            if ($operations === '') {
                $operations = 'Intervention externe importée depuis bilan corrective.';
            }
            $operations .= "\nDurée estimée: {$defaultDuration} min.";

            $payload = [
                'intervention_type' => MaintenanceReport::TYPE_CURATIVE,
                'status' => $status,
                'intervention_date' => $interventionDate->toDateString(),
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'equipment_id' => $equipment->id,
                'service_id' => $serviceId,
                'user_id' => $technician->id,
                'engineer_user_id' => $engineer?->id,
                'hospital_name' => 'CHU Tanger',
                'unit_code' => $equipment->service?->name,
                'equipment_designation' => $equipment->designation,
                'equipment_serial_number' => $equipment->serial_number,
                'equipment_inventory_number' => $equipment->inventory_number_current,
                'supplier_name' => trim((string) ($row->company_name ?? '')),
                'brand_name' => trim((string) ($row->brand_name ?? '')),
                'model_name' => trim((string) ($row->model_name ?? '')),
                'problem_description' => $problemDescription,
                'operations_performed' => $operations,
                'validated_at' => $status === MaintenanceReport::STATUS_VALIDATED || $status === MaintenanceReport::STATUS_CLOSED ? now() : null,
                'closed_at' => $status === MaintenanceReport::STATUS_CLOSED ? now() : null,
            ];

            if ($hasInterventionScope) {
                $payload['intervention_scope'] = MaintenanceReport::SCOPE_EXTERNE;
            }

            MaintenanceReport::query()->create($payload);
            $created++;
        }

        $this->info('Synchronisation corrective -> rapports terminée.');
        $this->line('Rapports créés: ' . $created);
        $this->line('Ignorés (déjà synchronisés): ' . $skippedExisting);
        $this->line('Ignorés (équipement non trouvé): ' . $skippedNoEquipment);
        $this->line('Ignorés (service indisponible): ' . $skippedNoService);

        return self::SUCCESS;
    }

    private function resolveEquipment(object $row): ?Equipment
    {
        $serial = $this->firstSerialToken((string) ($row->serial_number ?? ''));
        if ($serial !== null) {
            $bySerial = Equipment::query()
                ->whereNotNull('serial_number')
                ->where('serial_number', 'like', '%' . $serial . '%')
                ->orderByDesc('id')
                ->first();

            if ($bySerial) {
                return $bySerial;
            }
        }

        $designation = trim((string) ($row->equipment_designation ?? ''));
        if ($designation === '') {
            return null;
        }

        $exact = Equipment::query()
            ->where('designation', $designation)
            ->orderByDesc('id')
            ->first();

        if ($exact) {
            return $exact;
        }

        return Equipment::query()
            ->where('designation', 'like', '%' . $designation . '%')
            ->orderByDesc('id')
            ->first();
    }

    private function firstSerialToken(string $value): ?string
    {
        $cleaned = trim($value);
        if ($cleaned === '') {
            return null;
        }

        $parts = preg_split('/[\s,;|\n\r]+/', $cleaned) ?: [];
        foreach ($parts as $part) {
            $token = trim((string) $part);
            if ($token !== '' && mb_strlen($token) >= 4) {
                return $token;
            }
        }

        return null;
    }

    private function parseDate(string $value): ?Carbon
    {
        $text = trim($value);
        if ($text === '') {
            return null;
        }

        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'Y/m/d'];
        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $text);
                if ($parsed !== false) {
                    return $parsed->startOfDay();
                }
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($text)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
