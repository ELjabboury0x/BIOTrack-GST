<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('equipments') && !$this->indexExists('equipments', 'equipments_designation_idx')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->index('designation', 'equipments_designation_idx');
            });
        }

        if (Schema::hasTable('interventions') && !$this->indexExists('interventions', 'interventions_equipment_id_idx')) {
            Schema::table('interventions', function (Blueprint $table) {
                $table->index('equipment_id', 'interventions_equipment_id_idx');
            });
        }

        if (Schema::hasTable('complaints') && !$this->indexExists('complaints', 'complaints_equipment_id_idx')) {
            Schema::table('complaints', function (Blueprint $table) {
                $table->index('equipment_id', 'complaints_equipment_id_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('equipments') && $this->indexExists('equipments', 'equipments_designation_idx')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->dropIndex('equipments_designation_idx');
            });
        }

        if (Schema::hasTable('interventions') && $this->indexExists('interventions', 'interventions_equipment_id_idx')) {
            Schema::table('interventions', function (Blueprint $table) {
                $table->dropIndex('interventions_equipment_id_idx');
            });
        }

        if (Schema::hasTable('complaints') && $this->indexExists('complaints', 'complaints_equipment_id_idx')) {
            Schema::table('complaints', function (Blueprint $table) {
                $table->dropIndex('complaints_equipment_id_idx');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select('SHOW INDEX FROM ' . $table . ' WHERE Key_name = ?', [$indexName]);

        return !empty($rows);
    }
};
