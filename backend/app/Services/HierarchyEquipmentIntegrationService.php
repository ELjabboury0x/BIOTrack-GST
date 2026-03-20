<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Building;
use App\Models\Equipment;
use App\Models\Floor;
use App\Models\Hospital;
use App\Models\Room;
use App\Models\Service;
use App\Models\Structure;
use App\Models\Unit;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HierarchyEquipmentIntegrationService
{
    private ?bool $structureHasNomColumn = null;
    private ?bool $structureHasOrdreColumn = null;

    /**
     * Importe un fichier Excel d'équipements et intègre les données dans la hiérarchie.
     *
     * Colonnes attendues (tolérance sur accents/casse):
     * - Nom Equipement
     * - Service
     * - Bâtiment
     * - Étage
     * - Numéro Inventaire
     * - État
     */
    public function importFromExcel(string $filePath, array $options = []): array
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '1024M');

        DB::connection()->disableQueryLog();

        if (!is_file($filePath)) {
            throw new \RuntimeException('Fichier introuvable: ' . $filePath);
        }

        $hospitalCode = mb_strtoupper(trim((string) ($options['hospital_code'] ?? '')));
        $replaceHospitalStructure = (bool) ($options['replace_hospital_structure'] ?? false);
        $detachOldHierarchyEquipments = (bool) ($options['detach_old_hierarchy_equipments'] ?? false);
        $reassignComplaints = (bool) ($options['reassign_complaints'] ?? false);

        $forcedHospital = null;
        if ($hospitalCode !== '') {
            $forcedHospital = Hospital::query()->firstOrCreate(
                ['code' => $hospitalCode],
                ['name' => $hospitalCode === 'HME' ? 'Hôpital Mère-Enfants' : ('Hôpital ' . $hospitalCode)]
            );
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheet(0);

        [$headerRow, $columnMap] = $this->detectHeaderRowAndColumns($sheet);

        $required = ['hospital', 'equipment_name', 'service', 'floor', 'unit', 'sector', 'local'];
        $missing = array_values(array_filter($required, fn (string $key) => !isset($columnMap[$key])));
        if ($missing !== []) {
            throw new \RuntimeException('Colonnes manquantes: ' . implode(', ', $missing));
        }

        $this->ensureHierarchyRoots();

        $highestDataRow = (int) $sheet->getHighestDataRow();
        $seenInventories = [];
        $importedInventoriesByHospital = [];
        $structurePathsByHospital = [];

        $stats = [
            'rows_total' => max(0, $highestDataRow - $headerRow),
            'rows_processed' => 0,
            'rows_skipped' => 0,
            'duplicates_skipped' => 0,
            'equipments_created' => 0,
            'equipments_updated' => 0,
            'services_created' => 0,
            'structure_nodes_created' => 0,
        ];

        for ($row = $headerRow + 1; $row <= $highestDataRow; $row++) {
            $equipmentName = $this->cellString($sheet, $columnMap['equipment_name'], $row);
            $serviceName = $this->cellString($sheet, $columnMap['service'], $row);
            $hospitalRaw = isset($columnMap['hospital'])
                ? $this->cellString($sheet, $columnMap['hospital'], $row)
                : '';
            $floorRaw = $this->cellString($sheet, $columnMap['floor'], $row);
            $unitRaw = isset($columnMap['unit'])
                ? $this->cellString($sheet, $columnMap['unit'], $row)
                : '';
            $sectorRaw = isset($columnMap['sector'])
                ? $this->cellString($sheet, $columnMap['sector'], $row)
                : '';
            $localRaw = isset($columnMap['local'])
                ? $this->cellString($sheet, $columnMap['local'], $row)
                : '';
            $inventory = isset($columnMap['inventory'])
                ? $this->cellString($sheet, $columnMap['inventory'], $row)
                : '';
            $stateRaw = isset($columnMap['state'])
                ? $this->cellString($sheet, $columnMap['state'], $row)
                : '';

            if ($inventory === '') {
                $inventory = 'AUTO-' . strtoupper(substr(sha1(implode('|', [
                    $hospitalRaw,
                    $floorRaw,
                    $unitRaw,
                    $sectorRaw,
                    $serviceName,
                    $localRaw,
                    $equipmentName,
                    (string) $row,
                ])), 0, 12));
            }

            if ($equipmentName === '' || $serviceName === '' || $floorRaw === '') {
                $stats['rows_skipped']++;
                continue;
            }

            $inventoryKey = mb_strtolower(trim($inventory));
            if (isset($seenInventories[$inventoryKey])) {
                $stats['duplicates_skipped']++;
                continue;
            }
            $seenInventories[$inventoryKey] = true;

            (function () use (
                $forcedHospital,
                $equipmentName,
                $serviceName,
                $hospitalRaw,
                $floorRaw,
                $unitRaw,
                $sectorRaw,
                $localRaw,
                $inventory,
                $stateRaw,
                &$importedInventoriesByHospital,
                &$structurePathsByHospital,
                &$stats
            ) {
                $hospital = $forcedHospital ?: $this->resolveHospitalFromBuilding($hospitalRaw !== '' ? $hospitalRaw : $serviceName);
                $floorName = $this->normalizeFloorLabel($floorRaw);
                $specialityName = trim($serviceName) !== '' ? trim($serviceName) : 'Spécialité non renseignée';
                if (mb_strlen($specialityName) > 120) {
                    $specialityName = mb_substr($specialityName, 0, 120);
                }
                $unitName = trim($unitRaw) !== '' ? trim($unitRaw) : 'Unité non renseignée';
                if (mb_strlen($unitName) > 120) {
                    $unitName = mb_substr($unitName, 0, 120);
                }

                $sectorName = trim($sectorRaw) !== '' ? trim($sectorRaw) : 'Secteur non renseigné';
                if (mb_strlen($sectorName) > 120) {
                    $sectorName = mb_substr($sectorName, 0, 120);
                }

                $localName = trim($localRaw) !== '' ? trim($localRaw) : 'Local non renseigné';
                if (mb_strlen($localName) > 60) {
                    $localName = mb_substr($localName, 0, 60);
                }

                $zoneName = $specialityName . ' - ' . $floorName;
                if ($sectorName !== '') {
                    $zoneName .= ' - ' . $sectorName;
                }
                if (mb_strlen($zoneName) > 120) {
                    $zoneName = mb_substr($zoneName, 0, 120);
                }

                $hospitalCodeKey = mb_strtoupper(trim((string) ($hospital->code ?? '')));
                $inventoryKeyLower = mb_strtolower(trim($inventory));
                $importedInventoriesByHospital[$hospitalCodeKey][$inventoryKeyLower] = true;

                if (!isset($structurePathsByHospital[$hospitalCodeKey])) {
                    $structurePathsByHospital[$hospitalCodeKey] = [];
                }

                if (!isset($structurePathsByHospital[$hospitalCodeKey][$specialityName])) {
                    $structurePathsByHospital[$hospitalCodeKey][$specialityName] = [
                        'code' => '',
                        'floors' => [],
                    ];
                }

                if (!isset($structurePathsByHospital[$hospitalCodeKey][$specialityName]['floors'][$floorName])) {
                    $structurePathsByHospital[$hospitalCodeKey][$specialityName]['floors'][$floorName] = [];
                }

                $zone = Zone::query()->firstOrCreate(
                    ['name' => $zoneName],
                    ['description' => 'Zone importée automatiquement depuis la hiérarchie']
                );

                $serviceCreated = false;
                $floorId = $this->resolveFloorIdForHospital($hospital, $floorName);
                $service = $this->resolveOrCreateService($serviceName, (int) $zone->id, $serviceCreated, $floorId);
                if ($serviceCreated) {
                    $stats['services_created']++;
                }

                $unit = Unit::query()->firstOrCreate([
                    'service_id' => (int) $service->id,
                    'name' => $unitName,
                ]);

                $room = Room::query()->firstOrCreate([
                    'service_id' => (int) $service->id,
                    'room_number' => $localName,
                ]);

                $structurePathsByHospital[$hospitalCodeKey][$specialityName]['code'] = (string) ($service->code ?? '');

                if (!isset($structurePathsByHospital[$hospitalCodeKey][$specialityName]['floors'][$floorName]['units'][$unitName])) {
                    $structurePathsByHospital[$hospitalCodeKey][$specialityName]['floors'][$floorName]['units'][$unitName] = [
                        'sectors' => [],
                    ];
                }

                if (!isset($structurePathsByHospital[$hospitalCodeKey][$specialityName]['floors'][$floorName]['units'][$unitName]['sectors'][$sectorName])) {
                    $structurePathsByHospital[$hospitalCodeKey][$specialityName]['floors'][$floorName]['units'][$unitName]['sectors'][$sectorName] = [
                        'rooms' => [],
                    ];
                }

                if (!isset($structurePathsByHospital[$hospitalCodeKey][$specialityName]['floors'][$floorName]['units'][$unitName]['sectors'][$sectorName]['rooms'][$localName])) {
                    $structurePathsByHospital[$hospitalCodeKey][$specialityName]['floors'][$floorName]['units'][$unitName]['sectors'][$sectorName]['rooms'][$localName] = [
                        'equipments' => [],
                    ];
                }

                $structurePathsByHospital[$hospitalCodeKey][$specialityName]['floors'][$floorName]['units'][$unitName]['sectors'][$sectorName]['rooms'][$localName]['equipments'][$inventory] = [
                    'designation' => $equipmentName,
                ];

                $createdNodes = 0;
                $this->ensureEquipmentPathInStructure(
                    hospitalName: (string) $hospital->name,
                    specialityName: (string) $service->name,
                    floorName: $floorName,
                    serviceCode: (string) $service->code,
                    unitName: $unitName,
                    sectorName: $sectorName,
                    localName: $localName,
                    equipmentName: $equipmentName,
                    inventoryNumber: $inventory,
                    createdCounter: $createdNodes
                );
                $stats['structure_nodes_created'] += $createdNodes;

                $status = $this->normalizeOperationalStatus($stateRaw);
                $existing = Equipment::query()->where('inventory_number_current', $inventory)->first();

                if ($existing) {
                    $existing->fill([
                        'designation' => $equipmentName,
                        'unit_name' => $unitName,
                        'sector_name' => $sectorName,
                        'sector_description' => $localName,
                        'service_name' => $service->name,
                        'service_id' => $service->id,
                        'zone_id' => $zone->id,
                        'room_id' => $room->id,
                        'hospital_id' => $hospital->id,
                        'exact_location' => $floorName . ' / ' . $sectorName . ' / ' . $localName,
                        'operational_status' => $status,
                    ]);
                    $existing->save();
                    $stats['equipments_updated']++;
                } else {
                    Equipment::query()->create([
                        'inventory_number_current' => $inventory,
                        'designation' => $equipmentName,
                        'unit_name' => $unitName,
                        'sector_name' => $sectorName,
                        'sector_description' => $localName,
                        'service_name' => $service->name,
                        'service_id' => $service->id,
                        'zone_id' => $zone->id,
                        'room_id' => $room->id,
                        'hospital_id' => $hospital->id,
                        'exact_location' => $floorName . ' / ' . $sectorName . ' / ' . $localName,
                        'operational_status' => $status,
                    ]);
                    $stats['equipments_created']++;
                }

                $stats['rows_processed']++;
            })();
        }

        if ($replaceHospitalStructure && $forcedHospital) {
            $hospitalCodeKey = mb_strtoupper(trim((string) ($forcedHospital->code ?? '')));
            $paths = $structurePathsByHospital[$hospitalCodeKey] ?? [];
            $this->replaceHospitalStructure($this->resolveHierarchyHospitalName($forcedHospital), $paths, $stats['structure_nodes_created']);
            $this->removeUnexpectedSanitaryHospitals();
        }

        if ($detachOldHierarchyEquipments && $forcedHospital) {
            $hospitalCodeKey = mb_strtoupper(trim((string) ($forcedHospital->code ?? '')));
            $importedInventoryKeys = array_keys($importedInventoriesByHospital[$hospitalCodeKey] ?? []);
            $detached = $this->detachOldHierarchyEquipments((int) $forcedHospital->id, $importedInventoryKeys);
            $stats['equipments_detached'] = $detached;
        }

        if ($reassignComplaints && $forcedHospital) {
            $hospitalCodeKey = mb_strtoupper(trim((string) ($forcedHospital->code ?? '')));
            $importedInventoryKeys = array_keys($importedInventoriesByHospital[$hospitalCodeKey] ?? []);
            $reassigned = $this->reassignComplaintsToImportedEquipments((int) $forcedHospital->id, $importedInventoryKeys);
            $stats['complaints_reassigned'] = $reassigned;
        }

        return $stats;
    }

    private function replaceHospitalStructure(string $hospitalName, array $paths, int &$createdCounter): void
    {
        $sanitaryBranch = Structure::query()
            ->where('type', 'branche')
            ->where(function ($query) {
                $query->where('code', 'SAN');
                $query->orWhere('name', 'Branche Sanitaire');
                if ($this->hasStructureNomColumn()) {
                    $query->orWhere('nom', 'Branche Sanitaire');
                }
            })
            ->firstOrFail();

        $hospitalNode = $this->firstOrCreateStructureNode(
            parentId: (int) $sanitaryBranch->id,
            type: 'hopital',
            name: $hospitalName,
            code: null,
            createdCounter: $createdCounter
        );

        $childIds = Structure::query()->where('parent_id', (int) $hospitalNode->id)->pluck('id')->all();
        if ($childIds !== []) {
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
        }

        $specialityOrder = 10;
        foreach ($paths as $specialityName => $specialityPayload) {
            if (!is_array($specialityPayload)) {
                $specialityPayload = [
                    'code' => '',
                    'floors' => [],
                ];
            }

            $specialityNode = $this->createStructureNode([
                'parent_id' => (int) $hospitalNode->id,
                'name' => (string) $specialityName,
                'type' => 'service',
                'code' => (string) ($specialityPayload['code'] ?? ''),
                'order' => $specialityOrder,
                'responsable' => 'Chef de spécialité',
            ]);
            $createdCounter++;
            $specialityOrder += 10;

            $floorOrder = 10;
            foreach (($specialityPayload['floors'] ?? []) as $floorName => $floorPayload) {
                $floorNode = $this->createStructureNode([
                    'parent_id' => (int) $specialityNode->id,
                    'name' => (string) $floorName,
                    'type' => 'etage',
                    'code' => null,
                    'order' => $floorOrder,
                ]);
                $createdCounter++;
                $floorOrder += 10;

                $unitOrder = 10;
                foreach (($floorPayload['units'] ?? []) as $unitName => $unitPayload) {
                    $unitNode = $this->createStructureNode([
                        'parent_id' => (int) $floorNode->id,
                        'name' => (string) $unitName,
                        'type' => 'unite',
                        'order' => $unitOrder,
                    ]);
                    $createdCounter++;
                    $unitOrder += 10;

                    $sectorOrder = 10;
                    foreach (($unitPayload['sectors'] ?? []) as $sectorName => $sectorPayload) {
                        $sectorNode = $this->createStructureNode([
                            'parent_id' => (int) $unitNode->id,
                            'name' => (string) $sectorName,
                            'type' => 'secteur',
                            'order' => $sectorOrder,
                        ]);
                        $createdCounter++;
                        $sectorOrder += 10;

                        $roomOrder = 10;
                        foreach (($sectorPayload['rooms'] ?? []) as $roomName => $roomPayload) {
                            $roomNode = $this->createStructureNode([
                                'parent_id' => (int) $sectorNode->id,
                                'name' => (string) $roomName,
                                'type' => 'local',
                                'order' => $roomOrder,
                            ]);
                            $createdCounter++;
                            $roomOrder += 10;

                            $equipmentOrder = 10;
                            foreach (($roomPayload['equipments'] ?? []) as $inventoryNumber => $equipmentPayload) {
                                $designation = trim((string) ($equipmentPayload['designation'] ?? 'Équipement'));
                                $displayName = trim($inventoryNumber . ' - ' . $designation, ' -');

                                $this->createStructureNode([
                                    'parent_id' => (int) $roomNode->id,
                                    'name' => $displayName,
                                    'type' => 'equipement',
                                    'code' => (string) $inventoryNumber,
                                    'order' => $equipmentOrder,
                                ]);
                                $createdCounter++;
                                $equipmentOrder += 10;
                            }
                        }
                    }
                }
            }
        }
    }

    private function detachOldHierarchyEquipments(int $hospitalId, array $importedInventoryKeys): int
    {
        $query = Equipment::query()
            ->where('hospital_id', $hospitalId)
            ->whereNotNull('service_id');

        if ($importedInventoryKeys !== []) {
            $query->whereNotIn(DB::raw('LOWER(TRIM(inventory_number_current))'), $importedInventoryKeys);
        }

        return $query->update([
            'service_id' => null,
            'zone_id' => null,
            'room_id' => null,
            'service_name' => null,
            'exact_location' => null,
        ]);
    }

    private function reassignComplaintsToImportedEquipments(int $hospitalId, array $importedInventoryKeys): int
    {
        if ($importedInventoryKeys === []) {
            return 0;
        }

        $equipmentByInventory = Equipment::query()
            ->where('hospital_id', $hospitalId)
            ->whereIn(DB::raw('LOWER(TRIM(inventory_number_current))'), $importedInventoryKeys)
            ->get(['id', 'service_id', 'inventory_number_current'])
            ->mapWithKeys(function (Equipment $equipment) {
                $key = mb_strtolower(trim((string) $equipment->inventory_number_current));

                return [$key => $equipment];
            });

        if ($equipmentByInventory->isEmpty()) {
            return 0;
        }

        $reassigned = 0;

        Complaint::query()
            ->with(['equipment:id,inventory_number_current'])
            ->whereNotNull('equipment_id')
            ->chunkById(300, function ($chunk) use ($equipmentByInventory, &$reassigned) {
                foreach ($chunk as $complaint) {
                    $inventory = mb_strtolower(trim((string) ($complaint->equipment?->inventory_number_current ?? '')));
                    if ($inventory === '') {
                        continue;
                    }

                    $targetEquipment = $equipmentByInventory->get($inventory);
                    if (!$targetEquipment) {
                        continue;
                    }

                    if ((int) $complaint->equipment_id !== (int) $targetEquipment->id || (int) $complaint->service_id !== (int) ($targetEquipment->service_id ?? 0)) {
                        $complaint->equipment_id = (int) $targetEquipment->id;
                        $complaint->service_id = (int) ($targetEquipment->service_id ?? $complaint->service_id);
                        $complaint->save();
                        $reassigned++;
                    }
                }
            });

        return $reassigned;
    }

    private function removeUnexpectedSanitaryHospitals(): void
    {
        $sanitaryBranch = Structure::query()
            ->where('type', 'branche')
            ->where(function ($query) {
                $query->where('code', 'SAN');
                $query->orWhere('name', 'Branche Sanitaire');
                if ($this->hasStructureNomColumn()) {
                    $query->orWhere('nom', 'Branche Sanitaire');
                }
            })
            ->first();

        if (!$sanitaryBranch) {
            return;
        }

        $allowed = collect([
            'hopital mere enfants',
            'hopital des specialites',
            'hopitaux de proximite',
            'districts sanitaires',
        ]);

        $hospitalNodes = Structure::query()
            ->where('parent_id', (int) $sanitaryBranch->id)
            ->where('type', 'hopital')
            ->get(['id', 'name']);

        foreach ($hospitalNodes as $hospitalNode) {
            $normalized = $this->normalizeHeader((string) $hospitalNode->name);
            if ($allowed->contains($normalized)) {
                continue;
            }

            $childIds = Structure::query()->where('parent_id', (int) $hospitalNode->id)->pluck('id')->all();
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

            if ($allIds !== []) {
                Structure::query()->whereIn('id', array_reverse(array_values(array_unique($allIds))))->delete();
            }

            Structure::query()->where('id', (int) $hospitalNode->id)->delete();
        }
    }

    public function syncHospitalEquipmentsToHierarchy(string $hospitalCode = 'HSP'): array
    {
        $hospital = Hospital::query()->where('code', $hospitalCode)->first();
        if (!$hospital) {
            throw new \RuntimeException('Hôpital introuvable pour le code: ' . $hospitalCode);
        }

        $this->ensureHierarchyRoots();

        $stats = [
            'hospital_code' => $hospitalCode,
            'equipments_total' => (int) Equipment::query()->where('hospital_id', $hospital->id)->count(),
            'equipments_processed' => 0,
            'equipments_updated' => 0,
            'services_created' => 0,
            'structure_nodes_created' => 0,
            'skipped' => 0,
        ];

        Equipment::query()
            ->with(['service:id,name,code,zone_id', 'zone:id,name'])
            ->where('hospital_id', $hospital->id)
            ->orderBy('id')
            ->chunkById(300, function ($chunk) use (&$stats, $hospital) {
                $hierarchyHospitalName = $this->resolveHierarchyHospitalName($hospital);

                foreach ($chunk as $equipment) {
                    $stats['equipments_processed']++;

                    [$buildingName, $floorName] = $this->inferBuildingAndFloorFromEquipment($equipment, (string) $hospital->name);
                    $zoneName = $buildingName . ' - ' . $floorName;

                    $zone = Zone::query()->firstOrCreate(
                        ['name' => $zoneName],
                        ['description' => 'Zone synchronisée automatiquement depuis équipements']
                    );

                    $serviceName = trim((string) ($equipment->service?->name ?: $equipment->service_name ?: 'Service non renseigné'));
                    if ($serviceName === '') {
                        $serviceName = 'Service non renseigné';
                    }

                    $serviceCreated = false;
                    $service = $equipment->service;

                    if (!$service || mb_strtolower(trim((string) $service->name)) !== mb_strtolower($serviceName)) {
                        $service = $this->resolveOrCreateService($serviceName, (int) $zone->id, $serviceCreated);
                        if ($serviceCreated) {
                            $stats['services_created']++;
                        }
                    }

                    if ($service && (int) $service->zone_id !== (int) $zone->id) {
                        $service->zone_id = (int) $zone->id;
                        $service->save();
                    }

                    $createdNodes = 0;
                    $this->ensureServicePathInStructure(
                        hospitalName: $hierarchyHospitalName,
                        buildingName: $buildingName,
                        floorName: $floorName,
                        serviceName: (string) $service->name,
                        serviceCode: (string) $service->code,
                        createdCounter: $createdNodes
                    );
                    $stats['structure_nodes_created'] += $createdNodes;

                    $needsUpdate = false;

                    if ((int) ($equipment->service_id ?? 0) !== (int) $service->id) {
                        $equipment->service_id = (int) $service->id;
                        $needsUpdate = true;
                    }

                    if ((int) ($equipment->zone_id ?? 0) !== (int) $zone->id) {
                        $equipment->zone_id = (int) $zone->id;
                        $needsUpdate = true;
                    }

                    if ((int) ($equipment->hospital_id ?? 0) !== (int) $hospital->id) {
                        $equipment->hospital_id = (int) $hospital->id;
                        $needsUpdate = true;
                    }

                    if (trim((string) ($equipment->service_name ?? '')) !== (string) $service->name) {
                        $equipment->service_name = (string) $service->name;
                        $needsUpdate = true;
                    }

                    if (trim((string) ($equipment->exact_location ?? '')) !== $zoneName) {
                        $equipment->exact_location = $zoneName;
                        $needsUpdate = true;
                    }

                    if ($needsUpdate) {
                        $equipment->save();
                        $stats['equipments_updated']++;
                    }
                }
            });

        return $stats;
    }

    private function ensureHierarchyRoots(): void
    {
        $gst = Structure::query()
            ->where('type', 'gst')
            ->where(function ($query) {
                $query->where('code', 'GST');
                $query->orWhereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower('Groupement Sanitaire Territorial (GST)')]);
                if ($this->hasStructureNomColumn()) {
                    $query->orWhereRaw('LOWER(TRIM(nom)) = ?', [mb_strtolower('Groupement Sanitaire Territorial (GST)')]);
                }
            })
            ->first();

        if (!$gst) {
            $gst = $this->createStructureNode([
                'parent_id' => null,
                'name' => 'Groupement Sanitaire Territorial (GST)',
                'type' => 'gst',
                'code' => 'GST',
                'order' => 1,
            ]);
        }

        $san = Structure::query()
            ->where('type', 'branche')
            ->where('parent_id', $gst->id)
            ->where(function ($query) {
                $query->where('code', 'SAN');
                $query->orWhereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower('Branche Sanitaire')]);
                if ($this->hasStructureNomColumn()) {
                    $query->orWhereRaw('LOWER(TRIM(nom)) = ?', [mb_strtolower('Branche Sanitaire')]);
                }
            })
            ->first();

        if (!$san) {
            $this->createStructureNode([
                'parent_id' => (int) $gst->id,
                'name' => 'Branche Sanitaire',
                'type' => 'branche',
                'code' => 'SAN',
                'order' => 20,
            ]);
        }
    }

    private function ensureServicePathInStructure(
        string $hospitalName,
        string $buildingName,
        string $floorName,
        string $serviceName,
        string $serviceCode,
        int &$createdCounter
    ): void {
        $sanitaryBranch = Structure::query()
            ->where('type', 'branche')
            ->where(function ($query) {
                $query->where('code', 'SAN');
                $query->orWhere('name', 'Branche Sanitaire');
                if ($this->hasStructureNomColumn()) {
                    $query->orWhere('nom', 'Branche Sanitaire');
                }
            })
            ->firstOrFail();

        $hospitalNode = $this->firstOrCreateStructureNode(
            parentId: (int) $sanitaryBranch->id,
            type: 'hopital',
            name: $hospitalName,
            code: null,
            createdCounter: $createdCounter
        );

        $buildingNode = $this->firstOrCreateStructureNode(
            parentId: (int) $hospitalNode->id,
            type: 'batiment',
            name: $buildingName,
            code: null,
            createdCounter: $createdCounter
        );

        $floorNode = $this->firstOrCreateStructureNode(
            parentId: (int) $buildingNode->id,
            type: 'etage',
            name: $floorName,
            code: null,
            createdCounter: $createdCounter
        );

        $this->firstOrCreateStructureNode(
            parentId: (int) $floorNode->id,
            type: 'service',
            name: $serviceName,
            code: $serviceCode,
            createdCounter: $createdCounter
        );
    }

    private function ensureEquipmentPathInStructure(
        string $hospitalName,
        string $specialityName,
        string $floorName,
        string $serviceCode,
        string $unitName,
        string $sectorName,
        string $localName,
        string $equipmentName,
        string $inventoryNumber,
        int &$createdCounter
    ): void {
        $sanitaryBranch = Structure::query()
            ->where('type', 'branche')
            ->where(function ($query) {
                $query->where('code', 'SAN');
                $query->orWhere('name', 'Branche Sanitaire');
                if ($this->hasStructureNomColumn()) {
                    $query->orWhere('nom', 'Branche Sanitaire');
                }
            })
            ->firstOrFail();

        $hospitalNode = $this->firstOrCreateStructureNode((int) $sanitaryBranch->id, 'hopital', $hospitalName, null, $createdCounter);
        $specialityNode = $this->firstOrCreateStructureNode((int) $hospitalNode->id, 'service', $specialityName, $serviceCode, $createdCounter);
        $floorNode = $this->firstOrCreateStructureNode((int) $specialityNode->id, 'etage', $floorName, null, $createdCounter);
        $unitNode = $this->firstOrCreateStructureNode((int) $floorNode->id, 'unite', $unitName, null, $createdCounter);
        $sectorNode = $this->firstOrCreateStructureNode((int) $unitNode->id, 'secteur', $sectorName, null, $createdCounter);
        $localNode = $this->firstOrCreateStructureNode((int) $sectorNode->id, 'local', $localName, null, $createdCounter);

        $displayName = trim($inventoryNumber . ' - ' . $equipmentName, ' -');
        $this->firstOrCreateStructureNode((int) $localNode->id, 'equipement', $displayName, $inventoryNumber, $createdCounter);
    }

    private function firstOrCreateStructureNode(
        int $parentId,
        string $type,
        string $name,
        ?string $code,
        int &$createdCounter
    ): Structure {
        $normalizedName = mb_strtolower(trim($name));

        $node = Structure::query()
            ->where('parent_id', $parentId)
            ->where('type', $type)
            ->where(function ($query) use ($normalizedName) {
                $query->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName]);
                if ($this->hasStructureNomColumn()) {
                    $query->orWhereRaw('LOWER(TRIM(nom)) = ?', [$normalizedName]);
                }
            })
            ->first();

        if ($node) {
            if ($type === 'service' && $code && $node->code !== $code) {
                $node->code = $code;
                if ($this->hasStructureNomColumn() && (!isset($node->nom) || trim((string) $node->nom) === '')) {
                    $node->setAttribute('nom', $name);
                }
                $node->save();
            }

            return $node;
        }

        $maxOrder = (int) Structure::query()->where('parent_id', $parentId)->max('order');

        $createdCounter++;

        return $this->createStructureNode([
            'parent_id' => $parentId,
            'name' => $name,
            'type' => $type,
            'code' => $code,
            'order' => $maxOrder + 10,
        ]);
    }

    private function createStructureNode(array $attributes): Structure
    {
        $payload = [
            'parent_id' => $attributes['parent_id'] ?? null,
            'name' => (string) ($attributes['name'] ?? ''),
            'type' => (string) ($attributes['type'] ?? 'structure'),
            'code' => $attributes['code'] ?? null,
            'responsable' => $attributes['responsable'] ?? null,
            'order' => (int) ($attributes['order'] ?? 0),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($this->hasStructureNomColumn()) {
            $payload['nom'] = $payload['name'];
        }

        if ($this->hasStructureOrdreColumn()) {
            $payload['ordre'] = $payload['order'];
        }

        $id = (int) DB::table('structures')->insertGetId($payload);

        return Structure::query()->findOrFail($id);
    }

    private function hasStructureNomColumn(): bool
    {
        if ($this->structureHasNomColumn === null) {
            $this->structureHasNomColumn = Schema::hasColumn('structures', 'nom');
        }

        return $this->structureHasNomColumn;
    }

    private function hasStructureOrdreColumn(): bool
    {
        if ($this->structureHasOrdreColumn === null) {
            $this->structureHasOrdreColumn = Schema::hasColumn('structures', 'ordre');
        }

        return $this->structureHasOrdreColumn;
    }

    private function resolveOrCreateService(string $serviceName, int $zoneId, bool &$created, ?int $floorId = null): Service
    {
        $serviceName = trim($serviceName);
        if ($serviceName === '') {
            $serviceName = 'Service non renseigné';
        }

        if (mb_strlen($serviceName) > 120) {
            $serviceName = mb_substr($serviceName, 0, 120);
        }

        $normalizedName = mb_strtolower($serviceName);

        $existing = Service::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
            ->first();

        if ($existing) {
            $created = false;

            if (!$existing->zone_id) {
                $existing->zone_id = $zoneId;
            }

            if ($floorId !== null && (int) ($existing->floor_id ?? 0) !== (int) $floorId) {
                $existing->floor_id = $floorId;
            }

            if ($existing->isDirty()) {
                $existing->save();
            }

            return $existing;
        }

        $created = true;

        return Service::query()->create([
            'zone_id' => $zoneId,
            'floor_id' => $floorId,
            'name' => $serviceName,
            'code' => $this->generateUniqueServiceCode($serviceName),
        ]);
    }

    private function resolveFloorIdForHospital(Hospital $hospital, string $floorName): ?int
    {
        $normalizedFloor = $this->normalizeHeader($floorName);
        if ($normalizedFloor === '') {
            return null;
        }

        $building = Building::query()
            ->where('hospital_id', (int) $hospital->id)
            ->first();

        if (!$building) {
            return null;
        }

        $floor = Floor::query()
            ->where('building_id', (int) $building->id)
            ->get(['id', 'name'])
            ->first(function (Floor $candidate) use ($normalizedFloor) {
                return $this->normalizeHeader((string) $candidate->name) === $normalizedFloor;
            });

        return $floor ? (int) $floor->id : null;
    }

    private function generateUniqueServiceCode(string $serviceName): string
    {
        $base = strtoupper(Str::substr(Str::slug(Str::ascii($serviceName), ''), 0, 24));
        if ($base === '') {
            $base = 'SERVICE';
        }

        $base = 'SRV-' . $base;
        $code = Str::substr($base, 0, 40);

        $i = 2;
        while (Service::query()->where('code', $code)->exists()) {
            $suffix = '-' . $i;
            $code = Str::substr($base, 0, 40 - strlen($suffix)) . $suffix;
            $i++;
        }

        return $code;
    }

    private function resolveHospitalFromBuilding(string $building): Hospital
    {
        $normalized = $this->normalizeHeader($building);

        if (str_contains($normalized, 'mere') || str_contains($normalized, 'enfant')) {
            return Hospital::query()->firstOrCreate(
                ['code' => 'HME'],
                ['name' => 'Hôpital Mère-Enfants']
            );
        }

        if (str_contains($normalized, 'specialit') || str_contains($normalized, 'special')) {
            return Hospital::query()->firstOrCreate(
                ['code' => 'HSP'],
                ['name' => 'Hôpital des Spécialités']
            );
        }

        return Hospital::query()->firstOrCreate(
            ['code' => 'HO'],
            ['name' => 'Hôpitaux de Proximité']
        );
    }

    private function resolveHierarchyHospitalName(Hospital $hospital): string
    {
        return match ((string) $hospital->code) {
            'HSP' => 'Hôpital des Spécialités',
            'HME' => 'Hôpital Mère-Enfants',
            'HO' => 'Hôpitaux de Proximité',
            default => (string) $hospital->name,
        };
    }

    private function normalizeBuildingLabel(string $building): string
    {
        $value = trim($building);
        if ($value === '') {
            return 'Bâtiment non renseigné';
        }

        $normalized = $this->normalizeHeader($value);
        if (str_contains($normalized, 'mere') || str_contains($normalized, 'enfant')) {
            return 'Bâtiment Mère-Enfant';
        }

        if (str_contains($normalized, 'specialit') || str_contains($normalized, 'special')) {
            return 'Bâtiment des Spécialités';
        }

        return Str::of($value)->trim()->title()->toString();
    }

    private function normalizeFloorLabel(string $floor): string
    {
        $value = trim($floor);
        if ($value === '') {
            return 'Étage non renseigné';
        }

        $normalized = $this->normalizeHeader($value);
        if (str_contains($normalized, 'rdc') || str_contains($normalized, 'rez de chaussee') || str_contains($normalized, 'rez de chausse')) {
            return 'Étage 0';
        }

        if (str_contains($normalized, 'sous sol') || str_contains($normalized, 'ssol')) {
            return 'Étage -1';
        }

        if (preg_match('/-?\d+/', $value, $m)) {
            return 'Étage ' . $m[0];
        }

        return 'Étage ' . $value;
    }

    private function inferBuildingAndFloorFromEquipment(Equipment $equipment, string $hospitalName): array
    {
        $zoneName = trim((string) ($equipment->zone?->name ?? ''));
        $location = trim((string) ($equipment->exact_location ?? ''));
        $serviceName = trim((string) ($equipment->service_name ?? ''));
        $source = trim($zoneName . ' ' . $location . ' ' . $serviceName);

        $building = $this->normalizeBuildingLabel($hospitalName);

        if (preg_match('/b[âa]timent\s*([a-z0-9\-]+)/iu', $source, $m)) {
            $building = 'Bâtiment ' . Str::upper(trim((string) $m[1]));
        } else {
            $normalizedSource = $this->normalizeHeader($source);
            if (str_contains($normalizedSource, 'mere') || str_contains($normalizedSource, 'enfant')) {
                $building = 'Bâtiment Mère-Enfant';
            } elseif (str_contains($normalizedSource, 'specialit') || str_contains($normalizedSource, 'special')) {
                $building = 'Bâtiment des Spécialités';
            }
        }

        $floor = 'Étage 0';

        if (preg_match('/(?:etage|étage|niveau|floor)\s*([\-]?\d+)/iu', $source, $m)) {
            $floor = 'Étage ' . trim((string) $m[1]);
        } elseif (preg_match('/\b(rdc|rez de chaussee|rez-de-chaussee)\b/iu', $source)) {
            $floor = 'Étage 0';
        } elseif (preg_match('/\b(sous\s*sol|ssol)\b/iu', $source)) {
            $floor = 'Étage -1';
        } elseif (preg_match('/\b([34])\b/u', $source, $m)) {
            $floor = 'Étage ' . trim((string) $m[1]);
        }

        return [$building, $floor];
    }

    private function normalizeOperationalStatus(string $raw): string
    {
        $value = $this->normalizeHeader($raw);

        if (str_contains($value, 'hors') && str_contains($value, 'service')) {
            return 'hors_service';
        }

        if (str_contains($value, 'panne')) {
            return 'panne';
        }

        if (str_contains($value, 'reserv')) {
            return 'reserve';
        }

        return 'fonctionnel';
    }

    private function detectHeaderRowAndColumns(Worksheet $sheet): array
    {
        $aliases = [
            'equipment_name' => ['nom equipement', 'nom équipement', 'designation', 'désignation', 'equipement', 'équipement'],
            'service' => ['specialite', 'spécialité', 'service', 'specialiste', 'spécialiste'],
            'hospital' => ['hopital', 'hôpital'],
            'building' => ['batiment', 'bâtiment', 'bloc', 'site'],
            'floor' => ['etage', 'étage', 'niveau', 'etg'],
            'unit' => ['unite', 'unité'],
            'sector' => ['secteur', 'section'],
            'local' => ['local', 'salle', 'piece', 'pièce'],
            'inventory' => ['numero inventaire', 'numéro inventaire', 'n inventaire', 'n° inventaire'],
            'state' => ['etat', 'état', 'status', 'statut'],
        ];

        $highestColumn = $sheet->getHighestColumn();

        for ($row = 1; $row <= 10; $row++) {
            $map = [];

            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $header = $this->normalizeHeader($this->cellString($sheet, $col, $row));
                if ($header === '') {
                    continue;
                }

                foreach ($aliases as $key => $possible) {
                    foreach ($possible as $alias) {
                        if ($header === $this->normalizeHeader($alias)) {
                            $map[$key] = $col;
                            break 2;
                        }
                    }
                }
            }

            if (count($map) >= 5) {
                return [$row, $map];
            }
        }

        return [1, []];
    }

    private function cellString(Worksheet $sheet, string $column, int $row): string
    {
        return trim((string) $sheet->getCell($column . $row)->getFormattedValue());
    }

    private function normalizeHeader(string $value): string
    {
        $clean = Str::of($value)
            ->ascii()
            ->lower()
            ->replace(['_', '-', '.', ',', ';', ':', '/', '\\', '(', ')'], ' ')
            ->replace('°', ' ')
            ->squish()
            ->toString();

        return $clean;
    }
}
