<?php

namespace App\Console\Commands;

use App\Models\Equipment;
use App\Models\User;
use App\Support\ServiceAccess;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeEquipmentsNotOnPage extends Command
{
    protected $signature = 'equipements:purge-not-on-page
                            {--user_id= : User ID used for service scope (like current page)}
                            {--service_id= : Optional service filter like page filter}
                            {--q= : Optional search filter like page search}
                            {--sort=desc : Sort direction (asc|desc), for parity with page}
                            {--apply=0 : Set to 1 to execute deletions}';

    protected $description = 'Delete data linked to equipments not visible in the current equipments page scope.';

    public function handle(): int
    {
        $apply = (int) $this->option('apply') === 1;
        $userId = $this->option('user_id');
        $serviceId = (int) $this->option('service_id');
        $search = trim((string) $this->option('q'));
        $sortDirection = strtolower((string) $this->option('sort')) === 'asc' ? 'asc' : 'desc';

        $user = null;
        if ($userId !== null && $userId !== '') {
            $user = User::query()->find((int) $userId);
            if (!$user) {
                $this->error('Utilisateur introuvable pour --user_id=' . $userId);
                return 1;
            }
        }

        $keepQuery = Equipment::query()
            ->select('id')
            ->when($serviceId > 0, fn ($q) => $q->where('service_id', $serviceId))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(function ($inner) use ($like) {
                    $inner
                        ->where('inventory_number_current', 'like', $like)
                        ->orWhere('designation', 'like', $like)
                        ->orWhere('serial_number', 'like', $like)
                        ->orWhere('unit_name', 'like', $like)
                        ->orWhere('sector_name', 'like', $like)
                        ->orWhere('sector_description', 'like', $like)
                        ->orWhere('brand_name', 'like', $like)
                        ->orWhere('model_name', 'like', $like)
                        ->orWhere('market_label', 'like', $like)
                        ->orWhere('lot_number', 'like', $like);
                });
            })
            ->orderBy('id', $sortDirection);

        ServiceAccess::applyEquipmentScope($keepQuery, $user);

        $keepIds = $keepQuery->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $keepCount = count($keepIds);

        $this->info('Equipements conservés (scope page): ' . $keepCount);
        if ($user) {
            $this->line('Scope utilisateur: #' . $user->id . ' (' . ($user->role ?? '-') . ')');
        } else {
            $this->line('Scope utilisateur: global (aucun filtre utilisateur)');
        }

        $tables = collect(DB::select(
            "SELECT table_name FROM information_schema.columns WHERE table_schema = DATABASE() AND column_name = 'equipment_id' ORDER BY table_name"
        ))->map(fn ($row) => (string) $row->table_name)->values();

        $summary = [];
        $totalDeleted = 0;

        DB::beginTransaction();
        try {
            foreach ($tables as $table) {
                if ($table === 'equipments') {
                    continue;
                }

                $query = DB::table($table)->whereNotNull('equipment_id');
                if ($keepCount > 0) {
                    $query->whereNotIn('equipment_id', $keepIds);
                }

                $toDelete = (int) $query->count();

                if ($apply && $toDelete > 0) {
                    $deleteQuery = DB::table($table)->whereNotNull('equipment_id');
                    if ($keepCount > 0) {
                        $deleteQuery->whereNotIn('equipment_id', $keepIds);
                    }
                    $deleted = (int) $deleteQuery->delete();
                    $totalDeleted += $deleted;
                }

                $summary[] = [
                    'table' => $table,
                    'rows' => $toDelete,
                ];
            }

            $equipmentsDeleteQuery = Equipment::query();
            if ($keepCount > 0) {
                $equipmentsDeleteQuery->whereNotIn('id', $keepIds);
            }
            $equipmentsToDelete = (int) $equipmentsDeleteQuery->count();

            if ($apply && $equipmentsToDelete > 0) {
                $deletedEquipments = (int) (clone $equipmentsDeleteQuery)->delete();
                $totalDeleted += $deletedEquipments;
            }

            $summary[] = [
                'table' => 'equipments',
                'rows' => $equipmentsToDelete,
            ];

            if ($apply) {
                DB::commit();
                $this->info('Suppression appliquée. Total lignes supprimées: ' . $totalDeleted);
            } else {
                DB::rollBack();
                $this->warn('DRY-RUN uniquement (aucune suppression appliquée). Ajoutez --apply=1 pour exécuter.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Erreur pendant la purge: ' . $e->getMessage());
            return 1;
        }

        $this->line('--- Impact par table ---');
        foreach ($summary as $row) {
            $this->line(str_pad($row['table'], 35) . ' : ' . $row['rows']);
        }

        return 0;
    }
}
