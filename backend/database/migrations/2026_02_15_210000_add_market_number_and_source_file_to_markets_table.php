<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('markets', function (Blueprint $table) {
            $table->string('market_number', 80)->nullable()->after('reference');
            $table->string('source_file_name', 255)->nullable()->after('company_id');

            $table->index('market_number');
        });
    }

    public function down(): void
    {
        Schema::table('markets', function (Blueprint $table) {
            $table->dropIndex(['market_number']);
            $table->dropColumn(['market_number', 'source_file_name']);
        });
    }
};
