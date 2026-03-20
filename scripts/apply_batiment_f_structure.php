<?php

declare(strict_types=1);

use App\Models\Building;
use App\Models\Floor;
use App\Models\Hospital;
use App\Models\Service;
use App\Models\Structure;
use App\Models\Zone;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

DB::transaction(function (): void {
    $hasNom = Schema::hasColumn('structures', 'nom');
    $hasOrdre = Schema::hasColumn('structures', 'ordre');

    $createStructure = static function (array $attributes) use ($hasNom, $hasOrdre): Structure {
        if ($hasNom && !array_key_exists('nom', $attributes)) {
            $attributes['nom'] = (string) ($attributes['name'] ?? '');
        }

        if ($hasOrdre && !array_key_exists('ordre', $attributes)) {
            $attributes['ordre'] = (int) ($attributes['order'] ?? 0);
        }

        return Structure::query()->create($attributes);
    };

    $firstOrCreateStructure = static function (
        ?int $parentId,
        string $type,
        string $name,
        ?string $code,
        int $order,
        ?string $responsable = null
    ) use ($createStructure, $hasNom, $hasOrdre): Structure {
        $normalizedName = mb_strtolower(trim($name));

        $node = Structure::query()
            ->where('parent_id', $parentId)
            ->where('type', $type)
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
            ->first();

        if (!$node) {
            return $createStructure([
                'parent_id' => $parentId,
                'name' => $name,
                'type' => $type,
                'code' => $code,
                'responsable' => $responsable,
                'order' => $order,
            ]);
        }

        $dirty = false;

        if ((string) ($node->code ?? '') !== (string) ($code ?? '')) {
            $node->code = $code;
            $dirty = true;
        }

        if ((int) ($node->order ?? 0) !== $order) {
            $node->order = $order;
            $dirty = true;
        }

        if ($responsable !== null && (string) ($node->responsable ?? '') !== $responsable) {
            $node->responsable = $responsable;
            $dirty = true;
        }

        if ($hasNom && trim((string) ($node->nom ?? '')) === '') {
            $node->nom = $name;
            $dirty = true;
        }

        if ($hasOrdre && (int) ($node->ordre ?? 0) !== $order) {
            $node->ordre = $order;
            $dirty = true;
        }

        if ($dirty) {
            $node->save();
        }

        return $node;
    };

    $deleteChildrenRecursively = static function (int $parentId): void {
        $childIds = Structure::query()->where('parent_id', $parentId)->pluck('id')->all();

        if ($childIds === []) {
            return;
        }

        $allIds = $childIds;
        $cursor = $childIds;

        while ($cursor !== []) {
            $next = Structure::query()->whereIn('parent_id', $cursor)->pluck('id')->all();
            if ($next === []) {
                break;
            }

            $allIds = array_merge($allIds, $next);
            $cursor = $next;
        }

        Structure::query()->whereIn('id', array_reverse(array_values(array_unique($allIds))))->delete();
    };

    $gst = $firstOrCreateStructure(null, 'gst', 'Groupement Sanitaire Territorial (GST)', 'GST', 1, 'Direction Générale GST');
    $sanitary = $firstOrCreateStructure((int) $gst->id, 'branche', 'Branche Sanitaire', 'SAN', 20);
    $hme = $firstOrCreateStructure((int) $sanitary->id, 'hopital', 'Hôpital Mère-Enfants', null, 10);
    $buildingStructure = $firstOrCreateStructure((int) $hme->id, 'batiment', 'Bâtiment F', 'F', 60);

    $deleteChildrenRecursively((int) $buildingStructure->id);

    $hospital = Hospital::query()->firstOrCreate(
        ['code' => 'HME'],
        ['name' => 'Hôpital Mère-Enfants']
    );

    $building = Building::query()->firstOrCreate(
        ['hospital_id' => (int) $hospital->id, 'name' => 'Bâtiment F'],
        ['code' => 'F']
    );

    $map = [
        'ETG4' => ['Gynécologie', 'Obstétrique'],
        'ETG3' => ['Pédiatrie', 'Unité Oncologie pédiatrique', 'CH. INF. Viscérale', 'CH. INF. Traumatologie'],
        'ETG2' => ['Direction générale'],
        'ETG1' => ['U.T.A', 'Réa Pédiatrique', 'Unité Réa Néonatale', 'U.S.I.C', 'Unité Rééducation cardiaque'],
        'ETG0' => ['SAUV Adultes 1 et 2', 'UHTCD Adultes 1 et 2', 'SAUV Enfants', 'UHTCD Enfants', 'Radiologie des urgences', 'Bloc porte'],
        'ETG-1' => ['Locaux Techniques', 'PMA'],
    ];

    $floorOrder = [
        'ETG4' => 60,
        'ETG3' => 50,
        'ETG2' => 40,
        'ETG1' => 30,
        'ETG0' => 20,
        'ETG-1' => 10,
    ];

    $floorTableName = [
        'ETG4' => 'Étage 4',
        'ETG3' => 'Étage 3',
        'ETG2' => 'Étage 2',
        'ETG1' => 'Étage 1',
        'ETG0' => 'Étage 0',
        'ETG-1' => 'Étage -1',
    ];

    foreach ($map as $floorLabel => $serviceNames) {
        $floorNode = $firstOrCreateStructure(
            (int) $buildingStructure->id,
            'etage',
            $floorLabel,
            null,
            (int) ($floorOrder[$floorLabel] ?? 0)
        );

        $floor = Floor::query()->firstOrCreate(
            ['building_id' => (int) $building->id, 'name' => $floorTableName[$floorLabel]],
            [
                'display_order' => (int) ($floorOrder[$floorLabel] ?? 0),
                'hospital_id' => (int) $hospital->id,
            ]
        );

        $floorDirty = false;
        if ((int) ($floor->display_order ?? 0) !== (int) ($floorOrder[$floorLabel] ?? 0)) {
            $floor->display_order = (int) ($floorOrder[$floorLabel] ?? 0);
            $floorDirty = true;
        }

        if ((int) ($floor->hospital_id ?? 0) !== (int) $hospital->id) {
            $floor->hospital_id = (int) $hospital->id;
            $floorDirty = true;
        }

        if ($floorDirty) {
            $floor->save();
        }

        $serviceOrder = 10;
        foreach ($serviceNames as $serviceName) {
            $service = Service::query()
                ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($serviceName))])
                ->first();

            if (!$service) {
                $zoneName = mb_substr('Bâtiment F - ' . $floorLabel . ' - ' . $serviceName, 0, 120);
                $zone = Zone::query()->firstOrCreate(
                    ['name' => $zoneName],
                    ['description' => 'Zone créée automatiquement pour Bâtiment F']
                );

                $base = 'SRV-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $serviceName) ?: $serviceName), 0, 20));
                if ($base === 'SRV-') {
                    $base = 'SRV-SERVICE';
                }

                $code = $base;
                $counter = 2;
                while (Service::query()->where('code', $code)->exists()) {
                    $suffix = '-' . $counter;
                    $code = substr($base, 0, max(1, 40 - strlen($suffix))) . $suffix;
                    $counter++;
                }

                $service = Service::query()->create([
                    'name' => $serviceName,
                    'code' => $code,
                    'zone_id' => (int) $zone->id,
                    'floor_id' => (int) $floor->id,
                ]);
            } else {
                $serviceDirty = false;

                if ((int) ($service->floor_id ?? 0) !== (int) $floor->id) {
                    $service->floor_id = (int) $floor->id;
                    $serviceDirty = true;
                }

                if ((int) ($service->zone_id ?? 0) === 0) {
                    $zoneName = mb_substr('Bâtiment F - ' . $floorLabel . ' - ' . $serviceName, 0, 120);
                    $zone = Zone::query()->firstOrCreate(
                        ['name' => $zoneName],
                        ['description' => 'Zone créée automatiquement pour Bâtiment F']
                    );
                    $service->zone_id = (int) $zone->id;
                    $serviceDirty = true;
                }

                if ($serviceDirty) {
                    $service->save();
                }
            }

            $firstOrCreateStructure(
                (int) $floorNode->id,
                'service',
                $serviceName,
                (string) ($service->code ?? ''),
                $serviceOrder,
                'Chef de service'
            );

            $serviceOrder += 10;
        }
    }
});

echo "OK: structure Bâtiment F appliquée avec succès." . PHP_EOL;
