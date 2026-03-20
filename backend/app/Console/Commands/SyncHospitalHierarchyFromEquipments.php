<?php

namespace App\Console\Commands;

use App\Services\HierarchyEquipmentIntegrationService;
use Illuminate\Console\Command;

class SyncHospitalHierarchyFromEquipments extends Command
{
    protected $signature = 'hierarchie:sync-hospital {--hospital=HSP : Code hôpital (HSP/HME/HO)}';

    protected $description = 'Synchronise la hiérarchie (bâtiment/étage/service) à partir des équipements déjà présents dans un hôpital';

    public function handle(HierarchyEquipmentIntegrationService $integrationService): int
    {
        $hospitalCode = strtoupper(trim((string) $this->option('hospital')));

        try {
            $stats = $integrationService->syncHospitalEquipmentsToHierarchy($hospitalCode);
        } catch (\Throwable $e) {
            $this->error('Synchronisation échouée: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Synchronisation hiérarchie terminée.');
        $this->line('Hôpital: ' . ($stats['hospital_code'] ?? $hospitalCode));
        $this->line('Équipements total: ' . (int) ($stats['equipments_total'] ?? 0));
        $this->line('Équipements traités: ' . (int) ($stats['equipments_processed'] ?? 0));
        $this->line('Équipements mis à jour: ' . (int) ($stats['equipments_updated'] ?? 0));
        $this->line('Services créés: ' . (int) ($stats['services_created'] ?? 0));
        $this->line('Nœuds hiérarchie créés: ' . (int) ($stats['structure_nodes_created'] ?? 0));

        return self::SUCCESS;
    }
}
