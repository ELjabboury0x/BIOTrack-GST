<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 80)->nullable()->unique();
            $table->date('market_date');
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'market_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('markets');
    }
};
