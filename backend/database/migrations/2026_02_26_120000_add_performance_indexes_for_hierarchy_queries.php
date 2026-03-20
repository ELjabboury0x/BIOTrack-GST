<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!$this->indexExists('equipments', 'equipments_service_id_hospital_id_index')) {
            DB::statement('CREATE INDEX equipments_service_id_hospital_id_index ON equipments (service_id, hospital_id)');
        }

        if (!$this->indexExists('complaints', 'complaints_service_id_status_index')) {
            DB::statement('CREATE INDEX complaints_service_id_status_index ON complaints (service_id, status)');
        }
    }

    public function down(): void
    {
        if ($this->indexExists('equipments', 'equipments_service_id_hospital_id_index')) {
            DB::statement('DROP INDEX equipments_service_id_hospital_id_index ON equipments');
        }

        if ($this->indexExists('complaints', 'complaints_service_id_status_index')) {
            DB::statement('DROP INDEX complaints_service_id_status_index ON complaints');
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();

        $exists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();

        return (bool) $exists;
    }
};
