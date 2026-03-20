<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->addIndexIfMissing('complaints', 'equipment_id', 'complaints_equipment_id_perf_idx');
        $this->addIndexIfMissing('interventions', 'equipment_id', 'interventions_equipment_id_perf_idx');
        $this->addIndexIfMissing('maintenance_reports', 'equipment_id', 'maintenance_reports_equipment_id_perf_idx');
        $this->addIndexIfMissing('external_interventions', 'equipment_id', 'external_interventions_equipment_id_perf_idx');
        $this->addIndexIfMissing('external_interventions', 'company_id', 'external_interventions_company_id_perf_idx');
        $this->addIndexIfMissing('equipments', 'company_id', 'equipments_company_id_perf_idx');
        $this->addIndexIfMissing('external_company_plannings', 'company_id', 'external_company_plannings_company_id_perf_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('complaints', 'complaints_equipment_id_perf_idx');
        $this->dropIndexIfExists('interventions', 'interventions_equipment_id_perf_idx');
        $this->dropIndexIfExists('maintenance_reports', 'maintenance_reports_equipment_id_perf_idx');
        $this->dropIndexIfExists('external_interventions', 'external_interventions_equipment_id_perf_idx');
        $this->dropIndexIfExists('external_interventions', 'external_interventions_company_id_perf_idx');
        $this->dropIndexIfExists('equipments', 'equipments_company_id_perf_idx');
        $this->dropIndexIfExists('external_company_plannings', 'external_company_plannings_company_id_perf_idx');
    }

    private function addIndexIfMissing(string $table, string $column, string $indexName): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column) || $this->hasAnyIndexOnColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($column, $indexName) {
            $tableBlueprint->index($column, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table) || !$this->indexExistsByName($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName) {
            $tableBlueprint->dropIndex($indexName);
        });
    }

    private function hasAnyIndexOnColumn(string $table, string $column): bool
    {
        $indexes = DB::select('SHOW INDEX FROM ' . $table);

        foreach ($indexes as $index) {
            if (strcasecmp((string) ($index->Column_name ?? ''), $column) === 0) {
                return true;
            }
        }

        return false;
    }

    private function indexExistsByName(string $table, string $indexName): bool
    {
        $indexes = DB::select('SHOW INDEX FROM ' . $table . ' WHERE Key_name = ?', [$indexName]);

        return !empty($indexes);
    }
};
