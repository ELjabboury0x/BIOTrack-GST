<?php

declare(strict_types=1);

use App\Models\Equipment;
use App\Models\Hospital;
use App\Models\Service;
use App\Models\Structure;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$hme = Hospital::query()->where('code', 'HME')->first();

if (!$hme) {
    fwrite(STDERR, "Hôpital HME introuvable.\n");
    exit(1);
}

$normalize = static function (?string $value): string {
    $text = Str::ascii(trim((string) $value));
    $text = mb_strtolower($text);

    return preg_replace('/\s+/u', ' ', $text) ?: '';
};

$keepServiceIds = Service::query()
    ->where(function ($query) use ($hme) {
        $query->whereHas('floor.building', function ($subQuery) use ($hme) {
            $subQuery->where('hospital_id', (int) $hme->id);
        })->orWhereHas('equipments', function ($subQuery) use ($hme) {
            $subQuery->where('hospital_id', (int) $hme->id);
        });
    })
    ->pluck('id')
    ->map(fn ($id) => (int) $id)
    ->unique()
    ->values();

$keepServiceNamesNormalized = Service::query()
    ->whereIn('id', $keepServiceIds->all())
    ->pluck('name')
    ->map(fn ($name) => $normalize((string) $name))
    ->filter()
    ->unique()
    ->values();

$deleteServiceIds = Service::query()
    ->when($keepServiceIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $keepServiceIds->all()))
    ->when($keepServiceIds->isEmpty(), fn ($query) => $query)
    ->pluck('id')
    ->map(fn ($id) => (int) $id)
    ->unique()
    ->values();

$deleteEquipmentIds = Equipment::query()
    ->where(function ($query) use ($hme, $deleteServiceIds) {
        $query->where('hospital_id', '!=', (int) $hme->id);

        if ($deleteServiceIds->isNotEmpty()) {
            $query->orWhereIn('service_id', $deleteServiceIds->all());
        }
    })
    ->pluck('id')
    ->map(fn ($id) => (int) $id)
    ->unique()
    ->values();

$fallbackServiceId = (int) Service::query()
    ->whereIn('id', $keepServiceIds->all())
    ->orderBy('id')
    ->value('id');

if ($fallbackServiceId <= 0) {
    fwrite(STDERR, "Aucun service HME de secours trouvé pour conserver rapports/interventions.\n");
    exit(1);
}

$fallbackEquipmentId = (int) Equipment::query()
    ->where('hospital_id', (int) $hme->id)
    ->where('service_id', $fallbackServiceId)
    ->orderBy('id')
    ->value('id');

if ($fallbackEquipmentId <= 0) {
    $fallbackEquipmentId = (int) Equipment::query()
        ->where('hospital_id', (int) $hme->id)
        ->orderBy('id')
        ->value('id');
}

if ($fallbackEquipmentId <= 0) {
    fwrite(STDERR, "Aucun équipement HME de secours trouvé pour conserver rapports/interventions.\n");
    exit(1);
}

$chunkedWhereInDelete = static function (string $table, string $column, Collection $ids): int {
    if ($ids->isEmpty()) {
        return 0;
    }

    $deleted = 0;
    foreach ($ids->chunk(1000) as $chunk) {
        $deleted += DB::table($table)->whereIn($column, $chunk->all())->delete();
    }

    return $deleted;
};

$collectDescendants = static function (int $rootId): array {
    $childIds = Structure::query()->where('parent_id', $rootId)->pluck('id')->map(fn ($id) => (int) $id)->all();
    if ($childIds === []) {
        return [];
    }

    $allIds = $childIds;
    $cursor = $childIds;

    while ($cursor !== []) {
        $next = Structure::query()->whereIn('parent_id', $cursor)->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($next === []) {
            break;
        }

        $allIds = array_merge($allIds, $next);
        $cursor = $next;
    }

    return array_values(array_unique($allIds));
};

$stats = [
    'keep_services' => (int) $keepServiceIds->count(),
    'delete_services' => (int) $deleteServiceIds->count(),
    'delete_equipments' => (int) $deleteEquipmentIds->count(),
    'reassigned_interventions' => 0,
    'reassigned_complaints' => 0,
    'reassigned_reports' => 0,
    'deleted_equipments' => 0,
    'deleted_services' => 0,
    'deleted_rooms' => 0,
    'deleted_units' => 0,
    'deleted_structure_nodes' => 0,
    'deleted_structure_hospitals' => 0,
    'deleted_structure_services' => 0,
];

DB::transaction(function () use (
    $deleteEquipmentIds,
    $deleteServiceIds,
    $fallbackServiceId,
    $fallbackEquipmentId,
    $keepServiceNamesNormalized,
    $chunkedWhereInDelete,
    $collectDescendants,
    $normalize,
    &$stats
): void {
    if ($deleteEquipmentIds->isNotEmpty()) {
        foreach ($deleteEquipmentIds->chunk(1000) as $chunk) {
            $stats['reassigned_interventions'] += DB::table('interventions')
                ->whereIn('equipment_id', $chunk->all())
                ->update(['equipment_id' => $fallbackEquipmentId]);

            $stats['reassigned_reports'] += DB::table('maintenance_reports')
                ->whereIn('equipment_id', $chunk->all())
                ->update([
                    'equipment_id' => $fallbackEquipmentId,
                    'service_id' => $fallbackServiceId,
                ]);

            $stats['reassigned_complaints'] += DB::table('complaints')
                ->whereIn('equipment_id', $chunk->all())
                ->update([
                    'equipment_id' => $fallbackEquipmentId,
                    'service_id' => $fallbackServiceId,
                ]);
        }
    }

    if ($deleteServiceIds->isNotEmpty()) {
        foreach ($deleteServiceIds->chunk(1000) as $chunk) {
            $stats['reassigned_reports'] += DB::table('maintenance_reports')
                ->whereIn('service_id', $chunk->all())
                ->update(['service_id' => $fallbackServiceId]);

            $stats['reassigned_complaints'] += DB::table('complaints')
                ->whereIn('service_id', $chunk->all())
                ->update(['service_id' => $fallbackServiceId]);
        }

        $stats['deleted_rooms'] += $chunkedWhereInDelete('rooms', 'service_id', $deleteServiceIds);
        $stats['deleted_units'] += $chunkedWhereInDelete('units', 'service_id', $deleteServiceIds);
    }

    $stats['deleted_equipments'] += $chunkedWhereInDelete('equipments', 'id', $deleteEquipmentIds);
    $stats['deleted_services'] += $chunkedWhereInDelete('services', 'id', $deleteServiceIds);

    if (!Schema::hasTable('structures')) {
        return;
    }

    $sanitaryBranch = Structure::query()
        ->where('type', 'branche')
        ->where(function ($query) {
            $query->where('code', 'SAN')
                ->orWhereRaw('LOWER(TRIM(name)) = ?', ['branche sanitaire']);

            if (Schema::hasColumn('structures', 'nom')) {
                $query->orWhereRaw('LOWER(TRIM(nom)) = ?', ['branche sanitaire']);
            }
        })
        ->first();

    if (!$sanitaryBranch) {
        return;
    }

    $hospitalNodes = Structure::query()
        ->where('parent_id', (int) $sanitaryBranch->id)
        ->where('type', 'hopital')
        ->get(['id', 'name']);

    $hmeNode = $hospitalNodes->first(function (Structure $node) use ($normalize) {
        $name = $normalize((string) $node->name);

        return str_contains($name, 'mere') || str_contains($name, 'enfant');
    });

    foreach ($hospitalNodes as $hospitalNode) {
        if ($hmeNode && (int) $hospitalNode->id === (int) $hmeNode->id) {
            continue;
        }

        $descendants = $collectDescendants((int) $hospitalNode->id);
        if ($descendants !== []) {
            $stats['deleted_structure_nodes'] += Structure::query()->whereIn('id', array_reverse($descendants))->delete();
        }

        $stats['deleted_structure_hospitals'] += Structure::query()->where('id', (int) $hospitalNode->id)->delete();
    }

    if (!$hmeNode) {
        return;
    }

    $serviceNodes = Structure::query()
        ->where('type', 'service')
        ->whereIn('parent_id', function ($query) use ($hmeNode) {
            $query->select('id')
                ->from('structures')
                ->where('parent_id', (int) $hmeNode->id);
        })
        ->get(['id', 'name']);

    foreach ($serviceNodes as $serviceNode) {
        $serviceName = $normalize((string) $serviceNode->name);
        if ($serviceName !== '' && $keepServiceNamesNormalized->contains($serviceName)) {
            continue;
        }

        $descendants = $collectDescendants((int) $serviceNode->id);
        if ($descendants !== []) {
            $stats['deleted_structure_nodes'] += Structure::query()->whereIn('id', array_reverse($descendants))->delete();
        }

        $stats['deleted_structure_services'] += Structure::query()->where('id', (int) $serviceNode->id)->delete();
    }
});

foreach ($stats as $key => $value) {
    echo $key . '=' . $value . PHP_EOL;
}
