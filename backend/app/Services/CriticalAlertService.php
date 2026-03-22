<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\Intervention;
use App\Models\User;
use App\Support\ServiceAccess;
use Illuminate\Database\Eloquent\Builder;

class CriticalAlertService
{
    private const MTTR_HIGH_THRESHOLD_HOURS = 24;

    public function build(?User $user): array
    {
        $alerts = [];

        $criticalBreakdown = $this->criticalEquipmentsInBreakdown($user);
        if ($criticalBreakdown !== null) {
            $alerts[] = $criticalBreakdown;
        }

        $overduePreventive = $this->overduePreventiveMaintenances($user);
        if ($overduePreventive !== null) {
            $alerts[] = $overduePreventive;
        }

        $outOfService = $this->outOfServiceMoreThan48Hours($user);
        if ($outOfService !== null) {
            $alerts[] = $outOfService;
        }

        $highMttr = $this->highMttr($user);
        if ($highMttr !== null) {
            $alerts[] = $highMttr;
        }

        return $alerts;
    }

    private function criticalEquipmentsInBreakdown(?User $user): ?array
    {
        $query = Equipment::query()
            ->select(['id', 'inventory_number_current', 'designation'])
            ->where('operational_status', 'panne')
            ->where(function (Builder $inner): void {
                $inner
                    ->whereRaw('LOWER(COALESCE(lifecycle_status, "")) LIKE ?', ['%crit%'])
                    ->orWhereRaw('LOWER(COALESCE(category_name, "")) LIKE ?', ['%crit%']);
            });

        ServiceAccess::applyEquipmentScope($query, $user);

        $count = (clone $query)->count();
        if ($count <= 0) {
            return null;
        }

        return $this->buildAlert(
            'Equipements critiques en panne',
            'Alerte elevee: equipements critiques actuellement en panne.',
            'red',
            'fas fa-triangle-exclamation',
            $count,
            route('equipements', ['status' => 'panne']),
            $query->latest('id')->limit(3)->get()->map(fn (Equipment $equipment) => $this->equipmentLabel($equipment))->values()->all()
        );
    }

    private function overduePreventiveMaintenances(?User $user): ?array
    {
        $query = Intervention::query()
            ->select(['id', 'code', 'equipment_id', 'date_start'])
            ->with('equipment:id,inventory_number_current,designation')
            ->where('type', 'Préventive')
            ->where('status', '!=', 'termine')
            ->whereDate('date_start', '<', now()->toDateString());

        ServiceAccess::applyInterventionScope($query, $user);

        $count = (clone $query)->count();
        if ($count <= 0) {
            return null;
        }

        $samples = $query
            ->oldest('date_start')
            ->limit(3)
            ->get()
            ->map(function (Intervention $intervention): string {
                $date = $intervention->date_start?->format('d/m/Y') ?? '-';
                $label = $intervention->equipment
                    ? $this->equipmentLabel($intervention->equipment)
                    : ('OT ' . (string) $intervention->code);

                return $label . ' (prevue le ' . $date . ')';
            })
            ->values()
            ->all();

        return $this->buildAlert(
            'Maintenances preventives en retard',
            'Interventions preventives planifiees mais non terminees.',
            'orange',
            'fas fa-calendar-xmark',
            $count,
            route('interventions', ['status' => 'en_attente']),
            $samples
        );
    }

    private function outOfServiceMoreThan48Hours(?User $user): ?array
    {
        $query = Equipment::query()
            ->select(['id', 'inventory_number_current', 'designation', 'updated_at'])
            ->where('operational_status', 'hors_service')
            ->where('updated_at', '<=', now()->subHours(48));

        ServiceAccess::applyEquipmentScope($query, $user);

        $count = (clone $query)->count();
        if ($count <= 0) {
            return null;
        }

        $samples = $query
            ->oldest('updated_at')
            ->limit(3)
            ->get()
            ->map(function (Equipment $equipment): string {
                $hours = max(0, (int) floor(now()->diffInHours($equipment->updated_at)));

                return $this->equipmentLabel($equipment) . ' (' . $hours . 'h)';
            })
            ->values()
            ->all();

        return $this->buildAlert(
            'Equipements hors service > 48h',
            'Equipements immobilises depuis plus de 48 heures.',
            'red',
            'fas fa-power-off',
            $count,
            route('equipements', ['status' => 'hors_service']),
            $samples
        );
    }

    private function highMttr(?User $user): ?array
    {
        $query = Intervention::query()
            ->select(['id', 'code', 'equipment_id', 'date_start', 'date_end'])
            ->with('equipment:id,inventory_number_current,designation')
            ->where('status', 'termine')
            ->whereNotNull('date_start')
            ->whereNotNull('date_end')
            ->whereRaw('TIMESTAMPDIFF(HOUR, date_start, date_end) > ?', [self::MTTR_HIGH_THRESHOLD_HOURS]);

        ServiceAccess::applyInterventionScope($query, $user);

        $count = (clone $query)->count();
        if ($count <= 0) {
            return null;
        }

        $samples = $query
            ->latest('date_end')
            ->limit(3)
            ->get()
            ->map(function (Intervention $intervention): string {
                $hours = (int) max(0, $intervention->date_start?->diffInHours($intervention->date_end) ?? 0);
                $label = $intervention->equipment
                    ? $this->equipmentLabel($intervention->equipment)
                    : ('OT ' . (string) $intervention->code);

                return $label . ' (MTTR ' . $hours . 'h)';
            })
            ->values()
            ->all();

        return $this->buildAlert(
            'MTTR eleve',
            'Interventions terminees avec un temps moyen de reparation au-dessus de ' . self::MTTR_HIGH_THRESHOLD_HOURS . 'h.',
            'orange',
            'fas fa-stopwatch',
            $count,
            route('interventions'),
            $samples
        );
    }

    private function buildAlert(string $title, string $description, string $tone, string $icon, int $count, string $url, array $samples): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'tone' => $tone,
            'icon' => $icon,
            'count' => $count,
            'url' => $url,
            'samples' => $samples,
        ];
    }

    private function equipmentLabel(Equipment $equipment): string
    {
        $inventory = trim((string) ($equipment->inventory_number_current ?? ''));
        $designation = trim((string) ($equipment->designation ?? ''));

        if ($inventory !== '' && $designation !== '') {
            return $inventory . ' - ' . $designation;
        }

        return $designation !== '' ? $designation : ('Equipement #' . (int) $equipment->id);
    }
}
