<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('technician_name', 150)->nullable();
            $table->enum('type', ['Préventive', 'Curative', 'Urgente'])->default('Préventive');
            $table->enum('status', ['en_attente', 'en_cours', 'termine'])->default('en_attente');
            $table->date('date_start')->nullable();
            $table->timestamps();

            $table->index(['status', 'date_start']);
            $table->index('equipment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
