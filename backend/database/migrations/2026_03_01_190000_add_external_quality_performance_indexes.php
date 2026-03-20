<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('equipments') && !$this->indexExists('equipments', 'equipments_company_id_idx')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->index('company_id', 'equipments_company_id_idx');
            });
        }

        if (Schema::hasTable('external_company_plannings') && !$this->indexExists('external_company_plannings', 'ecp_planned_date_idx')) {
            Schema::table('external_company_plannings', function (Blueprint $table) {
                $table->index('planned_date', 'ecp_planned_date_idx');
            });
        }

        if (Schema::hasTable('maintenance_reports') && !$this->indexExists('maintenance_reports', 'maintenance_reports_intervention_date_idx')) {
            Schema::table('maintenance_reports', function (Blueprint $table) {
                $table->index('intervention_date', 'maintenance_reports_intervention_date_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('equipments') && $this->indexExists('equipments', 'equipments_company_id_idx')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->dropIndex('equipments_company_id_idx');
            });
        }

        if (Schema::hasTable('external_company_plannings') && $this->indexExists('external_company_plannings', 'ecp_planned_date_idx')) {
            Schema::table('external_company_plannings', function (Blueprint $table) {
                $table->dropIndex('ecp_planned_date_idx');
            });
        }

        if (Schema::hasTable('maintenance_reports') && $this->indexExists('maintenance_reports', 'maintenance_reports_intervention_date_idx')) {
            Schema::table('maintenance_reports', function (Blueprint $table) {
                $table->dropIndex('maintenance_reports_intervention_date_idx');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select('SHOW INDEX FROM ' . $table . ' WHERE Key_name = ?', [$indexName]);

        return !empty($rows);
    }
};
