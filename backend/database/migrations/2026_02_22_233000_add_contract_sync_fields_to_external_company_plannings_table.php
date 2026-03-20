<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('external_company_plannings', function (Blueprint $table) {
            if (!Schema::hasColumn('external_company_plannings', 'source_file')) {
                $table->string('source_file', 255)->nullable()->after('description');
            }

            if (!Schema::hasColumn('external_company_plannings', 'source_contract')) {
                $table->string('source_contract', 120)->nullable()->after('source_file');
            }

            if (!Schema::hasColumn('external_company_plannings', 'source_quarter')) {
                $table->unsignedTinyInteger('source_quarter')->nullable()->after('source_contract');
            }

            if (!Schema::hasColumn('external_company_plannings', 'source_hash')) {
                $table->char('source_hash', 64)->nullable()->after('source_quarter');
                $table->unique('source_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('external_company_plannings', function (Blueprint $table) {
            if (Schema::hasColumn('external_company_plannings', 'source_hash')) {
                $table->dropUnique(['source_hash']);
                $table->dropColumn('source_hash');
            }

            if (Schema::hasColumn('external_company_plannings', 'source_quarter')) {
                $table->dropColumn('source_quarter');
            }

            if (Schema::hasColumn('external_company_plannings', 'source_contract')) {
                $table->dropColumn('source_contract');
            }

            if (Schema::hasColumn('external_company_plannings', 'source_file')) {
                $table->dropColumn('source_file');
            }
        });
    }
};
