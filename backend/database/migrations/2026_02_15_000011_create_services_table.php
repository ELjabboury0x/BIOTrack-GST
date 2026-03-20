<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('zones')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 120);
            $table->timestamps();

            $table->unique(['zone_id', 'name']);
            $table->index('zone_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
