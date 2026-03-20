<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!$this->indexExists('equipments', 'equipments_service_id_index')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->index('service_id', 'equipments_service_id_index');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('equipments', 'equipments_service_id_index')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->dropIndex('equipments_service_id_index');
            });
        }
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::selectOne(
            'SELECT COUNT(1) AS total FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $tableName, $indexName]
        );

        return (int) ($result->total ?? 0) > 0;
    }
};
