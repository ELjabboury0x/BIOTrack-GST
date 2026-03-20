<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Hospital;
use App\Models\Service;
use App\Models\Zone;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EquipmentsHierarchyImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;
    private ?int $forcedServiceId = null;
    private ?int $forcedHospitalId = null;

    public function __construct(?int $forcedServiceId = null, ?int $forcedHospitalId = null)
    {
        $this->forcedServiceId = $forcedServiceId;
        $this->forcedHospitalId = $forcedHospitalId;
    }

    public function collection(Collection $rows): void
    {
        $fallbackZoneId = (int) Zone::query()->value('id');
        if ($fallbackZoneId <= 0) {
            $zone = Zone::query()->create(['name' => 'Zone generale']);
            $fallbackZoneId = (int) $zone->id;
        }

        $forcedService = null;
        $forcedHospital = null;

        if (($this->forcedServiceId ?? 0) > 0) {
            $forcedService = Service::query()->with('hospital:id,code,name')->find($this->forcedServiceId);
            $forcedHospital = $forcedService?->hospital;
        } elseif (($this->forcedHospitalId ?? 0) > 0) {
            $forcedHospital = Hospital::query()->find($this->forcedHospitalId);
        }

        foreach ($rows as $row) {
            $designation = trim((string) ($row['designation'] ?? ''));
            $serialNumber = trim((string) ($row['serial_number'] ?? ''));
            $inventoryNumber = trim((string) ($row['inventory_number'] ?? ''));
            $hospitalName = trim((string) ($row['hospital'] ?? ''));
            $serviceName = trim((string) ($row['service'] ?? ''));
            $categoryName = trim((string) ($row['category'] ?? ''));

            if ($designation === '' || $inventoryNumber === '') {
                $this->skipped++;
                continue;
            }

            $hospital = $forcedHospital ?: $this->resolveHospital($hospitalName);
            if (!$hospital) {
                $this->skipped++;
                continue;
            }

            $service = $forcedService;
            if (!$service && $serviceName !== '') {
                $service = Service::query()
                    ->where('hospital_id', (int) $hospital->id)
                    ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($serviceName)])
                    ->first();

                if (!$service) {
                    $service = Service::query()->create([
                        'zone_id' => $fallbackZoneId,
                        'hospital_id' => (int) $hospital->id,
                        'name' => $serviceName,
                        'code' => mb_strtoupper(mb_substr(preg_replace('/\s+/', '', $serviceName), 0, 12)),
                    ]);
                }
            }

            $category = null;
            if ($service && $categoryName !== '') {
                $category = Category::query()->firstOrCreate([
                    'service_id' => (int) $service->id,
                    'name' => $categoryName,
                ]);
            }

            $existing = Equipment::query()->where('inventory_number_current', $inventoryNumber)->first();

            $payload = [
                'designation' => $designation,
                'serial_number' => $serialNumber !== '' ? $serialNumber : null,
                'hospital_id' => (int) $hospital->id,
                'service_id' => $service?->id,
                'category_id' => $category?->id,
                'service_name' => $service?->name,
                'category_name' => $category?->name,
                'unit_name' => $service?->name,
                'operational_status' => $existing?->operational_status ?: 'fonctionnel',
            ];

            if ($existing) {
                $existing->update($payload);
                $this->updated++;
            } else {
                Equipment::query()->create(array_merge($payload, [
                    'inventory_number_current' => $inventoryNumber,
                ]));
                $this->created++;
            }
        }
    }

    public function summary(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
        ];
    }

    private function resolveHospital(string $hospitalName): ?Hospital
    {
        $value = mb_strtolower(trim($hospitalName));

        if ($value === '' || str_contains($value, 'mere') || str_contains($value, 'enfant')) {
            return Hospital::query()->firstOrCreate(
                ['code' => 'HME'],
                ['name' => 'Hopital Mere-Enfants']
            );
        }

        if (str_contains($value, 'specialit')) {
            return Hospital::query()->firstOrCreate(
                ['code' => 'HO'],
                ['name' => 'Hopital des Specialites']
            );
        }

        return Hospital::query()->whereRaw('LOWER(TRIM(name)) = ?', [$value])->first();
    }
}
