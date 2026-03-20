<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('maintenance_reports', 'intervention_scope')) {
            Schema::table('maintenance_reports', function (Blueprint $table) {
                $table->enum('intervention_scope', ['interne', 'externe'])
                    ->default('interne')
                    ->after('intervention_type');
                $table->index('intervention_scope');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('maintenance_reports', 'intervention_scope')) {
            Schema::table('maintenance_reports', function (Blueprint $table) {
                $table->dropIndex(['intervention_scope']);
                $table->dropColumn('intervention_scope');
            });
        }
    }
};
