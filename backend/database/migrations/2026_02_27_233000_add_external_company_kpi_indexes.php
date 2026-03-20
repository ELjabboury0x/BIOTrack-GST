<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('external_company_plannings') && !$this->indexExists('external_company_plannings', 'ecp_company_planned_idx')) {
            Schema::table('external_company_plannings', function (Blueprint $table) {
                $table->index(['company_id', 'planned_date'], 'ecp_company_planned_idx');
            });
        }

        if (Schema::hasTable('interventions') && !$this->indexExists('interventions', 'interventions_date_start_idx')) {
            Schema::table('interventions', function (Blueprint $table) {
                $table->index('date_start', 'interventions_date_start_idx');
            });
        }

        if (Schema::hasTable('equipments') && !$this->indexExists('equipments', 'equipments_company_service_idx')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->index(['company_id', 'service_id'], 'equipments_company_service_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('external_company_plannings') && $this->indexExists('external_company_plannings', 'ecp_company_planned_idx')) {
            Schema::table('external_company_plannings', function (Blueprint $table) {
                $table->dropIndex('ecp_company_planned_idx');
            });
        }

        if (Schema::hasTable('interventions') && $this->indexExists('interventions', 'interventions_date_start_idx')) {
            Schema::table('interventions', function (Blueprint $table) {
                $table->dropIndex('interventions_date_start_idx');
            });
        }

        if (Schema::hasTable('equipments') && $this->indexExists('equipments', 'equipments_company_service_idx')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->dropIndex('equipments_company_service_idx');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select('SHOW INDEX FROM ' . $table . ' WHERE Key_name = ?', [$indexName]);

        return !empty($rows);
    }
};
