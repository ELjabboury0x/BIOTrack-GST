<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->foreignId('complaint_id')
                ->nullable()
                ->after('equipment_id')
                ->constrained('complaints')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(['complaint_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropIndex(['complaint_id', 'status']);
            $table->dropConstrainedForeignId('complaint_id');
        });
    }
};
