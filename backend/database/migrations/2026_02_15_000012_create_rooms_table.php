<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('room_number', 60);
            $table->timestamps();

            $table->unique(['service_id', 'room_number']);
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
