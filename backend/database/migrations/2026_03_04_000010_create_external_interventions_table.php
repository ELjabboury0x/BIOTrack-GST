<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('external_interventions')) {
            return;
        }

        Schema::create('external_interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained('interventions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnUpdate()->nullOnDelete();
            $table->dateTime('first_call_datetime')->nullable();
            $table->dateTime('technician_arrival_datetime')->nullable();
            $table->dateTime('resolution_datetime')->nullable();
            $table->string('status', 40)->default('en_attente');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('company_id', 'external_interventions_company_id_idx');
            $table->index('equipment_id', 'external_interventions_equipment_id_idx');
            $table->index('intervention_id', 'external_interventions_intervention_id_idx');
            $table->index(['first_call_datetime', 'resolution_datetime'], 'external_interventions_time_window_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_interventions');
    }
};
