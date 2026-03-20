<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->string('name');
            $table->string('type', 30);
            $table->string('code')->nullable();
            $table->string('responsable')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['parent_id', 'order']);
            $table->index('type');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('structures');
    }
};
