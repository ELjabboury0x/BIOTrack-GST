<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_designation_assets', function (Blueprint $table) {
            $table->id();
            $table->string('designation', 255)->unique();
            $table->string('image_path')->nullable();
            $table->string('user_manual_path')->nullable();
            $table->string('technical_manual_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_designation_assets');
    }
};
