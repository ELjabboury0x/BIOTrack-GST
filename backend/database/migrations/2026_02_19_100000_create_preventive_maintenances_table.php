<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('preventive_maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('periodicity', 40);
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date');
            $table->string('status', 20)->default('actif');
            $table->timestamps();

            $table->index(['status', 'next_maintenance_date']);
            $table->index(['equipment_id', 'next_maintenance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenances');
    }
};
