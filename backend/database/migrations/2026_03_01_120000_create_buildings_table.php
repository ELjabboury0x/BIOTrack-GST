<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('code', 40)->nullable();
            $table->timestamps();

            $table->unique(['hospital_id', 'name']);
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
