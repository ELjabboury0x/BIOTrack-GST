<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('floors', function (Blueprint $table) {
            if (!Schema::hasColumn('floors', 'hospital_id')) {
                $table->foreignId('hospital_id')->nullable()->after('building_id')->constrained('hospitals')->nullOnDelete();
                $table->index(['hospital_id', 'display_order']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('floors', function (Blueprint $table) {
            if (Schema::hasColumn('floors', 'hospital_id')) {
                $table->dropIndex(['hospital_id', 'display_order']);
                $table->dropConstrainedForeignId('hospital_id');
            }
        });
    }
};
