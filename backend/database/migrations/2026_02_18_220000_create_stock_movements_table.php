<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_type', 30);
            $table->string('part_reference', 150);
            $table->unsignedInteger('quantity');
            $table->date('movement_date');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['movement_type', 'movement_date']);
            $table->index('part_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
