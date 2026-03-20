<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('market_equipment_import_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained('markets')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('row_signature', 64)->unique();
            $table->string('market_object', 255)->nullable();
            $table->string('lot_number', 120)->nullable();
            $table->string('article', 150)->nullable();
            $table->string('designation', 255)->nullable();
            $table->decimal('quantity', 12, 2)->nullable();
            $table->string('delivery_status', 80)->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('market_complaint_status', 80)->nullable();
            $table->date('market_complaint_date')->nullable();
            $table->text('observations')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('source_file_name', 255)->nullable();
            $table->string('source_sheet_name', 120)->nullable();
            $table->unsignedInteger('source_row_index')->nullable();
            $table->timestamps();

            $table->index(['market_id']);
            $table->index(['source_file_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_equipment_import_lines');
    }
};
