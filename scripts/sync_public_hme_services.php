<?php

declare(strict_types=1);

use App\Models\Equipment;
use App\Models\Service;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$catalog = collect((array) config('hme_public_services', []))
    ->map(function (array $entry): array {
        return [
            'code' => trim((string) ($entry['code'] ?? '')),
            'name' => trim((string) ($entry['name'] ?? '')),
        ];
    })
    ->filter(fn (array $entry): bool => $entry['code'] !== '' && $entry['name'] !== '')
    ->values();

if ($catalog->isEmpty()) {
    echo "Aucune configuration trouvée dans config/hme_public_services.php" . PHP_EOL;
    exit(1);
}

$normalize = static function (string $value): string {
    $ascii = Str::upper(Str::ascii(trim($value)));

    return str_replace([' ', '-', '_', '/'], '', $ascii);
};

DB::transaction(function () use ($catalog, $normalize): void {
    $allServices = Service::query()->get(['id', 'code', 'name', 'zone_id', 'floor_id']);
    $firstService = $allServices->first();

    $canonicalByCode = [];
    $canonicalById = [];
    $touchedServiceIds = [];

    foreach ($catalog as $entry) {
        $targetCode = $entry['code'];
        $targetName = $entry['name'];
        $codeKey = $normalize($targetCode);
        $nameKey = $normalize($targetName);

        $service = $allServices->first(function (Service $candidate) use ($normalize, $codeKey): bool {
            return $normalize((string) ($candidate->code ?? '')) === $codeKey;
        });

        if (!$service) {
            $service = $allServices->first(function (Service $candidate) use ($normalize, $nameKey): bool {
                return $normalize((string) ($candidate->name ?? '')) === $nameKey;
            });
        }

        if (!$service) {
            $attributes = [
                'code' => $targetCode,
                'name' => $targetName,
            ];

            if ($firstService) {
                $attributes['zone_id'] = $firstService->zone_id;
                $attributes['floor_id'] = $firstService->floor_id;
            }

            $service = Service::query()->create($attributes);
            $allServices->push($service);
        } else {
            $dirty = false;

            if ((string) ($service->code ?? '') !== $targetCode) {
                $service->code = $targetCode;
                $dirty = true;
            }

            if ((string) ($service->name ?? '') !== $targetName) {
                $service->name = $targetName;
                $dirty = true;
            }

            if ($dirty) {
                $service->save();
            }
        }

        $canonicalByCode[$codeKey] = $service;
        $canonicalById[(int) $service->id] = $service;
        $touchedServiceIds[] = (int) $service->id;
    }

    $touchedServiceIds = array_values(array_unique($touchedServiceIds));

    $allServices = Service::query()->get(['id', 'code', 'name']);
    $serviceIdsByCodeOrName = [];

    foreach ($catalog as $entry) {
        $codeKey = $normalize($entry['code']);
        $nameKey = $normalize($entry['name']);
        $serviceIdsByCodeOrName[$codeKey] = [];

        foreach ($allServices as $candidate) {
            $candidateCodeKey = $normalize((string) ($candidate->code ?? ''));
            $candidateNameKey = $normalize((string) ($candidate->name ?? ''));

            if ($candidateCodeKey === $codeKey || $candidateNameKey === $nameKey) {
                $serviceIdsByCodeOrName[$codeKey][] = (int) $candidate->id;
            }
        }

        $serviceIdsByCodeOrName[$codeKey] = array_values(array_unique($serviceIdsByCodeOrName[$codeKey]));
    }

    $updatedEquipments = 0;

    foreach ($catalog as $entry) {
        $codeKey = $normalize($entry['code']);
        $targetCode = $entry['code'];
        $targetName = $entry['name'];
        $canonicalService = $canonicalByCode[$codeKey] ?? null;

        if (!$canonicalService) {
            continue;
        }

        $candidateServiceIds = $serviceIdsByCodeOrName[$codeKey] ?? [];
        $nameToken = $normalize($targetName);
        $codeToken = $normalize($targetCode);

        $query = Equipment::query()->where(function ($query) use ($canonicalService, $candidateServiceIds, $nameToken, $codeToken) {
            $query->where('service_id', (int) $canonicalService->id);

            if ($candidateServiceIds !== []) {
                $query->orWhereIn('service_id', $candidateServiceIds);
            }

            if ($nameToken !== '') {
                $query->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(service_name)), ' ', ''), '-', ''), '_', ''), '/', '') like ?", ['%' . $nameToken . '%'])
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(unit_name)), ' ', ''), '-', ''), '_', ''), '/', '') like ?", ['%' . $nameToken . '%']);
            }

            if ($codeToken !== '') {
                $query->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(service_name)), ' ', ''), '-', ''), '_', ''), '/', '') like ?", ['%' . $codeToken . '%'])
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(unit_name)), ' ', ''), '-', ''), '_', ''), '/', '') like ?", ['%' . $codeToken . '%']);
            }
        });

        $chunkIds = [];
        $query->select('id')->chunkById(500, function ($rows) use (&$chunkIds): void {
            foreach ($rows as $row) {
                $chunkIds[] = (int) $row->id;
            }
        });

        $chunkIds = array_values(array_unique($chunkIds));
        if ($chunkIds === []) {
            continue;
        }

        $affected = Equipment::query()->whereIn('id', $chunkIds)->update([
            'service_id' => (int) $canonicalService->id,
            'service_name' => $targetName,
        ]);

        $updatedEquipments += (int) $affected;
    }

    echo 'Services synchronisés (catalogue): ' . count($touchedServiceIds) . PHP_EOL;
    echo 'Équipements remappés: ' . $updatedEquipments . PHP_EOL;
});

echo "OK: synchro services/réclamation terminée." . PHP_EOL;
