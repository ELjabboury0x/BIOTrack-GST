<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->string('supplier', 150)->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('supplier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spare_parts');
    }
};
