<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('reported_by_name', 150);
            $table->string('room_number', 80)->nullable();
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved'])->default('open');
            $table->json('attachment_path')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('service_id');
            $table->index('equipment_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
