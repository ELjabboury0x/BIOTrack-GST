<?php

namespace App\Console\Commands;

use App\Http\Controllers\HierarchieController;
use App\Services\HierarchyEquipmentIntegrationService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class ImportHierarchyEquipmentsExcel extends Command
{
    protected $signature = 'hierarchie:import-equipements-excel
                            {file : Chemin du fichier Excel}
                            {--export-json= : Chemin de sortie du JSON final}
                            {--floor= : Filtre étage pour export JSON}
                            {--service= : Filtre service pour export JSON}';

    protected $description = 'Importe des équipements Excel dans la hiérarchie CHU et exporte optionnellement un JSON final';

    public function handle(HierarchyEquipmentIntegrationService $integrationService): int
    {
        $filePath = (string) $this->argument('file');

        try {
            $stats = $integrationService->importFromExcel($filePath);
        } catch (\Throwable $e) {
            $this->error('Import échoué: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Import terminé.');
        $this->line('Lignes lues: ' . (int) ($stats['rows_total'] ?? 0));
        $this->line('Lignes traitées: ' . (int) ($stats['rows_processed'] ?? 0));
        $this->line('Lignes ignorées: ' . (int) ($stats['rows_skipped'] ?? 0));
        $this->line('Doublons ignorés (fichier): ' . (int) ($stats['duplicates_skipped'] ?? 0));
        $this->line('Équipements créés: ' . (int) ($stats['equipments_created'] ?? 0));
        $this->line('Équipements mis à jour: ' . (int) ($stats['equipments_updated'] ?? 0));
        $this->line('Services créés: ' . (int) ($stats['services_created'] ?? 0));
        $this->line('Nœuds hiérarchie créés: ' . (int) ($stats['structure_nodes_created'] ?? 0));

        $exportPath = trim((string) $this->option('export-json'));
        if ($exportPath !== '') {
            $request = Request::create('/cli/hierarchie/export', 'GET', [
                'floor' => trim((string) $this->option('floor')),
                'service' => trim((string) $this->option('service')),
                'with_equipments' => '1',
            ]);

            $controller = app(HierarchieController::class);
            $payload = $controller->exportPayloadForCommand($request);

            $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                $this->error('Export JSON impossible.');
                return self::FAILURE;
            }

            $dir = dirname($exportPath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            file_put_contents($exportPath, $json);
            $this->info('JSON exporté: ' . $exportPath);
        }

        return self::SUCCESS;
    }
}
