<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('external_interventions')) {
            return;
        }

        if (!Schema::hasColumn('external_interventions', 'intervention_status')) {
            Schema::table('external_interventions', function (Blueprint $table) {
                $table->string('intervention_status', 40)->nullable()->after('status');
            });
        }

        if (Schema::hasColumn('external_interventions', 'status') && Schema::hasColumn('external_interventions', 'intervention_status')) {
            DB::table('external_interventions')
                ->whereNull('intervention_status')
                ->update([
                    'intervention_status' => DB::raw('COALESCE(status, "ouvert")'),
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('external_interventions')) {
            return;
        }

        if (Schema::hasColumn('external_interventions', 'intervention_status')) {
            Schema::table('external_interventions', function (Blueprint $table) {
                $table->dropColumn('intervention_status');
            });
        }
    }
};