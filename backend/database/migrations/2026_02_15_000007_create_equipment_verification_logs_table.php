<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('old_status', ['oui', 'non'])->nullable();
            $table->enum('new_status', ['oui', 'non']);
            $table->dateTime('changed_at');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'changed_at']);
            $table->index('changed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_verification_logs');
    }
};
