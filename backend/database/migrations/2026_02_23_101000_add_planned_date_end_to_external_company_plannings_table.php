<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('external_company_plannings', function (Blueprint $table) {
            if (!Schema::hasColumn('external_company_plannings', 'planned_date_end')) {
                $table->date('planned_date_end')->nullable()->after('planned_date');
                $table->index(['planned_date', 'planned_date_end'], 'ecp_planned_date_range_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('external_company_plannings', function (Blueprint $table) {
            if (Schema::hasColumn('external_company_plannings', 'planned_date_end')) {
                $table->dropIndex('ecp_planned_date_range_idx');
                $table->dropColumn('planned_date_end');
            }
        });
    }
};
