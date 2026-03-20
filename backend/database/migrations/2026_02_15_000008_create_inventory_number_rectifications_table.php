<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_number_rectifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('old_inventory_number', 80);
            $table->string('new_inventory_number', 80);
            $table->string('reason', 255)->nullable();
            $table->dateTime('rectified_at');
            $table->unsignedBigInteger('rectified_by')->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'rectified_at']);
            $table->index('new_inventory_number');
            $table->index('rectified_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_number_rectifications');
    }
};
