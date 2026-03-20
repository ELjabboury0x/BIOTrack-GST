<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('structures')) {
            return;
        }

        Schema::table('structures', function (Blueprint $table) {
            if (!Schema::hasColumn('structures', 'name')) {
                $table->string('name')->nullable()->after('parent_id');
            }

            if (!Schema::hasColumn('structures', 'code')) {
                $table->string('code')->nullable()->after('type');
            }

            if (!Schema::hasColumn('structures', 'order')) {
                $table->integer('order')->default(0)->after('responsable');
            }
        });

        if (Schema::hasColumn('structures', 'nom') && Schema::hasColumn('structures', 'name')) {
            DB::table('structures')
                ->whereNull('name')
                ->update(['name' => DB::raw('nom')]);
        }

        if (Schema::hasColumn('structures', 'ordre') && Schema::hasColumn('structures', 'order')) {
            DB::table('structures')
                ->where('order', 0)
                ->update(['order' => DB::raw('ordre')]);
        }

        if (!$this->indexExists('structures', 'structures_parent_id_order_index')) {
            DB::statement('CREATE INDEX structures_parent_id_order_index ON structures (parent_id, `order`)');
        }

        if (!$this->indexExists('structures', 'structures_code_index')) {
            DB::statement('CREATE INDEX structures_code_index ON structures (code)');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('structures')) {
            return;
        }

        if ($this->indexExists('structures', 'structures_parent_id_order_index')) {
            DB::statement('DROP INDEX structures_parent_id_order_index ON structures');
        }

        if ($this->indexExists('structures', 'structures_code_index')) {
            DB::statement('DROP INDEX structures_code_index ON structures');
        }
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $databaseName = DB::getDatabaseName();

        if (!is_string($databaseName) || trim($databaseName) === '') {
            return false;
        }

        return DB::table('information_schema.statistics')
            ->where('table_schema', $databaseName)
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
};
