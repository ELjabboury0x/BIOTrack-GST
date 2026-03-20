<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('external_company_plannings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->date('planned_date');
            $table->string('contact_person', 120)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 30)->default('en_attente');
            $table->timestamps();

            $table->index(['planned_date', 'status']);
            $table->index(['company_id', 'planned_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_company_plannings');
    }
};
