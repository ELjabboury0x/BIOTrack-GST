<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('name', 120);
                $table->timestamps();

                $table->unique(['service_id', 'name']);
                $table->index('service_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
