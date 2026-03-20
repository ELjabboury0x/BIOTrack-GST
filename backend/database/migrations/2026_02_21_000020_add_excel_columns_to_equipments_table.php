<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->string('unit_name', 120)->nullable()->after('brand_name');
            $table->string('sector_name', 120)->nullable()->after('unit_name');
            $table->string('sector_description', 255)->nullable()->after('sector_name');
            $table->string('model_name', 120)->nullable()->after('sector_description');
            $table->string('market_label', 120)->nullable()->after('model_name');
            $table->string('lot_number', 120)->nullable()->after('market_label');

            $table->index('unit_name');
            $table->index('sector_name');
        });
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->dropIndex(['unit_name']);
            $table->dropIndex(['sector_name']);

            $table->dropColumn([
                'unit_name',
                'sector_name',
                'sector_description',
                'model_name',
                'market_label',
                'lot_number',
            ]);
        });
    }
};
