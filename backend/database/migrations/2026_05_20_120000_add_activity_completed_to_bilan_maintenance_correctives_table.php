<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bilan_maintenance_correctives', function (Blueprint $table) {
            $table->boolean('activity_completed')->nullable()->after('intervention_date_text');
        });
    }

    public function down(): void
    {
        Schema::table('bilan_maintenance_correctives', function (Blueprint $table) {
            $table->dropColumn('activity_completed');
        });
    }
};
