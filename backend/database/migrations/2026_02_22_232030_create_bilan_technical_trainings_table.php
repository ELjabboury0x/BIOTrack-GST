<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bilan_technical_trainings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('equipment_designation')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('model_name')->nullable();
            $table->string('market_number')->nullable();
            $table->string('training_date')->nullable();
            $table->string('trained_personnel')->nullable();
            $table->text('remarks')->nullable();
            $table->string('source_file');
            $table->string('source_sheet');
            $table->unsignedInteger('source_row');
            $table->char('row_hash', 64)->unique();
            $table->timestamps();

            $table->index('company_name');
            $table->index('market_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bilan_technical_trainings');
    }
};
