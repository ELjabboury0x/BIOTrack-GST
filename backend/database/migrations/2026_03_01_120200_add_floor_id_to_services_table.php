<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'floor_id')) {
                $table->foreignId('floor_id')->nullable()->after('zone_id')->constrained('floors')->nullOnDelete();
                $table->index('floor_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'floor_id')) {
                $table->dropConstrainedForeignId('floor_id');
            }
        });
    }
};
