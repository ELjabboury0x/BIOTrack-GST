<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bilan_maintenance_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->nullable();
            $table->string('company_name')->nullable();
            $table->string('equipment_designation')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('model_name')->nullable();
            $table->text('serial_number')->nullable();
            $table->string('service_order_date')->nullable();
            $table->string('quarter_1')->nullable();
            $table->string('quarter_2')->nullable();
            $table->string('quarter_3')->nullable();
            $table->string('quarter_4')->nullable();
            $table->string('quarter_5')->nullable();
            $table->string('quarter_6')->nullable();
            $table->string('quarter_7')->nullable();
            $table->string('quarter_8')->nullable();
            $table->text('service_names')->nullable();
            $table->string('source_file');
            $table->string('source_sheet');
            $table->unsignedInteger('source_row');
            $table->char('row_hash', 64)->unique();
            $table->timestamps();

            $table->index('company_name');
            $table->index('contract_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bilan_maintenance_contracts');
    }
};
