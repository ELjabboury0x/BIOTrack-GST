<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bilan_maintenance_preventives', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('equipment_designation')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('model_name')->nullable();
            $table->string('market_or_contract_ref')->nullable();
            $table->text('serial_number')->nullable();
            $table->string('intervention_dates_text')->nullable();
            $table->text('intervention_details')->nullable();
            $table->text('observations')->nullable();
            $table->text('service_names')->nullable();
            $table->boolean('activity_completed')->nullable();
            $table->string('source_file');
            $table->string('source_sheet');
            $table->unsignedInteger('source_row');
            $table->char('row_hash', 64)->unique();
            $table->timestamps();

            $table->index('company_name');
            $table->index('market_or_contract_ref');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bilan_maintenance_preventives');
    }
};
