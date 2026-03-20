<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->unique()->constrained('equipments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('status', ['oui', 'non']);
            $table->dateTime('verified_at')->nullable();
            $table->foreignId('source_market_id')->nullable()->constrained('markets')->cascadeOnUpdate()->nullOnDelete();
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['source_market_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_verifications');
    }
};
