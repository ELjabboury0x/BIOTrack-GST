<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bilan_maintenance_corrective_pdfs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corrective_id');
            $table->string('document_kind');
            $table->string('stored_path');
            $table->string('original_name');
            $table->timestamps();

            $table->index('corrective_id');
            $table->index('document_kind');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bilan_maintenance_corrective_pdfs');
    }
};
