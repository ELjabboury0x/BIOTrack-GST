<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            if (!Schema::hasColumn('interventions', 'maintenance_scope')) {
                $table->enum('maintenance_scope', ['interne', 'externe'])
                    ->default('interne')
                    ->after('type');
            }
        });

        DB::statement("ALTER TABLE interventions MODIFY type ENUM('Préventive','Curative','Corrective','Prédictive','Améliorative','Systématique','Urgente') NOT NULL DEFAULT 'Préventive'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE interventions MODIFY type ENUM('Préventive','Curative','Urgente') NOT NULL DEFAULT 'Préventive'");

        Schema::table('interventions', function (Blueprint $table) {
            if (Schema::hasColumn('interventions', 'maintenance_scope')) {
                $table->dropColumn('maintenance_scope');
            }
        });
    }
};
