<?php

namespace App\Console\Commands;

use App\Models\Equipment;
use App\Models\Service;
use Illuminate\Console\Command;

class LinkEquipmentsToServices extends Command
{
    protected $signature = 'equipements:link-services {--fallback-service-code=HUME}';

    protected $description = 'Link equipments to services using unit_name/service_name and service code/name';

    public function handle(): int
    {
        $services = Service::query()->get(['id', 'zone_id', 'code', 'name']);
        if ($services->isEmpty()) {
            $this->error('Aucun service trouvé.');
            return self::FAILURE;
        }

        $fallbackCode = mb_strtoupper(trim((string) $this->option('fallback-service-code')));
        $fallbackService = Service::query()
            ->whereRaw('UPPER(TRIM(code)) = ?', [$fallbackCode])
            ->first() ?: $services->first();

        $serviceTokens = $services->map(function (Service $service) {
            return [
                'service' => $service,
                'tokens' => $this->buildServiceTokens($service),
            ];
        });

        $servicesByZone = $services->groupBy(fn (Service $service) => (string) ($service->zone_id ?? ''));

        $updated = 0;
        $fallbackAssigned = 0;
        $zoneAssigned = 0;

        Equipment::query()
            ->whereNull('service_id')
            ->where(function ($query) {
                $query->whereNotNull('unit_name')->orWhereNotNull('service_name')->orWhereNotNull('sector_name');
            })
            ->chunkById(200, function ($equipments) use ($serviceTokens, $servicesByZone, $fallbackService, &$updated, &$fallbackAssigned, &$zoneAssigned) {
                foreach ($equipments as $equipment) {
                    $matchedService = $this->resolveServiceForEquipment($serviceTokens, $equipment);

                    if (!$matchedService) {
                        $matchedService = $this->resolveServiceByZone($servicesByZone, $equipment);
                        if ($matchedService) {
                            $zoneAssigned++;
                        }
                    }

                    if (!$matchedService) {
                        $matchedService = $fallbackService;
                        $fallbackAssigned++;
                    }

                    if (!$matchedService) {
                        continue;
                    }

                    $equipment->service_id = $matchedService->id;
                    if (!$equipment->zone_id && $matchedService->zone_id) {
                        $equipment->zone_id = $matchedService->zone_id;
                    }
                    $equipment->save();
                    $updated++;
                }
            });

        $missing = Equipment::query()->whereNull('service_id')->count();
        $linked = Equipment::query()->whereNotNull('service_id')->count();
        $total = Equipment::query()->count();

        $this->info("Équipements reliés aux services: {$updated}");
        $this->info("Affectés par fallback zone: {$zoneAssigned}");
        $this->info("Affectés par fallback service ({$fallbackService->code}): {$fallbackAssigned}");
        $this->info("Avec service_id: {$linked}/{$total}");
        $this->info("Sans service_id: {$missing}");

        return self::SUCCESS;
    }

    private function resolveServiceForEquipment($serviceTokens, Equipment $equipment): ?Service
    {
        $searchPool = $this->buildEquipmentSearchPool($equipment);
        if (empty($searchPool)) {
            return null;
        }

        foreach ($serviceTokens as $entry) {
            /** @var Service $service */
            $service = $entry['service'];
            $tokens = $entry['tokens'];

            foreach ($tokens as $token) {
                if ($token === '') {
                    continue;
                }

                foreach ($searchPool as $value) {
                    if ($value === $token) {
                        return $service;
                    }
                }
            }
        }

        foreach ($serviceTokens as $entry) {
            /** @var Service $service */
            $service = $entry['service'];
            $tokens = $entry['tokens'];

            foreach ($tokens as $token) {
                if ($token === '' || mb_strlen($token) < 3) {
                    continue;
                }

                foreach ($searchPool as $value) {
                    if (str_contains($value, $token)) {
                        return $service;
                    }
                }
            }
        }

        return null;
    }

    private function buildEquipmentSearchPool(Equipment $equipment): array
    {
        $rawValues = [
            (string) $equipment->unit_name,
            (string) $equipment->service_name,
            (string) $equipment->sector_name,
        ];

        $pool = [];
        foreach ($rawValues as $raw) {
            $normalized = $this->normalize($raw);
            if ($normalized !== '') {
                $pool[] = $normalized;
                $pool[] = str_replace(' ', '', $normalized);
            }
        }

        return array_values(array_unique(array_filter($pool)));
    }

    private function buildServiceTokens(Service $service): array
    {
        $tokens = [];

        $code = $this->normalize((string) $service->code);
        $name = $this->normalize((string) $service->name);

        if ($code !== '') {
            $tokens[] = $code;
            $tokens[] = str_replace(' ', '', $code);
        }

        if ($name !== '') {
            $tokens[] = $name;
            $tokens[] = str_replace(' ', '', $name);

            foreach (preg_split('/\s+/', $name) ?: [] as $part) {
                if (mb_strlen($part) >= 4) {
                    $tokens[] = $part;
                }
            }
        }

        return array_values(array_unique(array_filter($tokens)));
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        if ($value === '') {
            return '';
        }

        $replacements = [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c',
        ];
        $value = strtr($value, $replacements);
        $value = preg_replace('/[^a-z0-9\s]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function resolveServiceByZone($servicesByZone, Equipment $equipment): ?Service
    {
        if (!$equipment->zone_id) {
            return null;
        }

        $zoneServices = $servicesByZone->get((string) $equipment->zone_id);
        if (!$zoneServices || $zoneServices->isEmpty()) {
            return null;
        }

        if ($zoneServices->count() === 1) {
            return $zoneServices->first();
        }

        $search = $this->buildEquipmentSearchPool($equipment);
        if (empty($search)) {
            return $zoneServices->first();
        }

        $bestService = null;
        $bestScore = -1;
        foreach ($zoneServices as $service) {
            $tokens = $this->buildServiceTokens($service);
            $score = 0;
            foreach ($tokens as $token) {
                if ($token === '') {
                    continue;
                }
                foreach ($search as $value) {
                    if ($value === $token) {
                        $score += 8;
                    } elseif (mb_strlen($token) >= 4 && str_contains($value, $token)) {
                        $score += 3;
                    }
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestService = $service;
            }
        }

        return $bestService ?: $zoneServices->first();
    }
}
