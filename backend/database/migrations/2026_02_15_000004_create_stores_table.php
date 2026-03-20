<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code', 40)->nullable();
            $table->string('name', 120);
            $table->timestamps();

            $table->unique(['hospital_id', 'name']);
            $table->index('hospital_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
