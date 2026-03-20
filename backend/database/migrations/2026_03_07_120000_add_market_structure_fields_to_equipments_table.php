<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            if (!Schema::hasColumn('equipments', 'market_object')) {
                $table->string('market_object', 255)->nullable()->after('market_label');
            }

            if (!Schema::hasColumn('equipments', 'quantity')) {
                $table->decimal('quantity', 12, 2)->nullable()->after('article');
            }

            if (!Schema::hasColumn('equipments', 'delivery_reception_provisoire')) {
                $table->string('delivery_reception_provisoire', 180)->nullable()->after('quantity');
            }

            if (!Schema::hasColumn('equipments', 'observations')) {
                $table->text('observations')->nullable()->after('delivery_reception_provisoire');
            }

            if (!Schema::hasColumn('equipments', 'recommendations')) {
                $table->text('recommendations')->nullable()->after('observations');
            }

            if (!Schema::hasColumn('equipments', 'annual_maintenance_amount_ht')) {
                $table->decimal('annual_maintenance_amount_ht', 14, 2)->nullable()->after('recommendations');
            }
        });
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $columns = [];

            foreach ([
                'market_object',
                'quantity',
                'delivery_reception_provisoire',
                'observations',
                'recommendations',
                'annual_maintenance_amount_ht',
            ] as $column) {
                if (Schema::hasColumn('equipments', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
