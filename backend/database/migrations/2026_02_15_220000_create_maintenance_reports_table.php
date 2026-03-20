<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenance_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number', 40)->unique();
            $table->enum('intervention_type', ['preventive', 'curative']);
            $table->enum('status', ['draft', 'submitted', 'validated', 'closed'])->default('draft');
            $table->date('intervention_date');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();

            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('engineer_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();

            $table->string('hospital_name', 255)->nullable();
            $table->string('unit_code', 120)->nullable();
            $table->string('equipment_designation', 255)->nullable();
            $table->string('equipment_serial_number', 120)->nullable();
            $table->string('equipment_inventory_number', 80)->nullable();
            $table->string('supplier_name', 180)->nullable();
            $table->string('brand_name', 120)->nullable();
            $table->string('model_name', 120)->nullable();
            $table->text('problem_description')->nullable();
            $table->text('operations_performed')->nullable();

            $table->string('technician_signature_path', 255)->nullable();
            $table->string('engineer_signature_path', 255)->nullable();
            $table->json('photo_paths')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->index('service_id');
            $table->index('equipment_id');
            $table->index('intervention_date');
            $table->index('status');
            $table->index(['intervention_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_reports');
    }
};
