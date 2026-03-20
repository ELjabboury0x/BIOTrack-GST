<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_number_current', 80)->unique();
            $table->string('serial_number', 120)->nullable();
            $table->string('designation', 255);
            $table->boolean('serial_label_removed')->default(false);
            $table->string('serial_label_comment', 255)->nullable();
            $table->string('service_name', 120)->nullable();
            $table->string('exact_location', 255)->nullable();
            $table->enum('operational_status', ['fonctionnel', 'reserve', 'panne', 'hors_service'])->default('fonctionnel');

            $table->foreignId('hospital_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('market_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();

            $table->timestamps();

            $table->index(['hospital_id', 'company_id']);
            $table->index('service_name');
            $table->index('market_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};
