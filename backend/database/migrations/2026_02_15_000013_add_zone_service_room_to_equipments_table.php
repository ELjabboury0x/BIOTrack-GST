<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->foreignId('zone_id')->nullable()->after('hospital_id')->constrained('zones')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->after('zone_id')->constrained('services')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->after('service_id')->constrained('rooms')->cascadeOnUpdate()->nullOnDelete();

            $table->index('zone_id');
            $table->index('service_id');
            $table->index('room_id');
            $table->index(['zone_id', 'service_id', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->dropIndex(['zone_id', 'service_id', 'room_id']);
            $table->dropIndex(['zone_id']);
            $table->dropIndex(['service_id']);
            $table->dropIndex(['room_id']);

            $table->dropConstrainedForeignId('room_id');
            $table->dropConstrainedForeignId('service_id');
            $table->dropConstrainedForeignId('zone_id');
        });
    }
};
