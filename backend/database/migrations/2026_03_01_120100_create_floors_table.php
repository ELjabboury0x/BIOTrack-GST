<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name', 40);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->unique(['building_id', 'name']);
            $table->index(['building_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};
