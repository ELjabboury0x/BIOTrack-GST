<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            if (!Schema::hasColumn('equipments', 'delivery_status')) {
                $table->string('delivery_status', 80)->nullable()->after('delivery_reception_provisoire');
            }

            if (!Schema::hasColumn('equipments', 'delivery_date')) {
                $table->date('delivery_date')->nullable()->after('delivery_status');
            }

            if (!Schema::hasColumn('equipments', 'market_complaint_status')) {
                $table->string('market_complaint_status', 80)->nullable()->after('delivery_date');
            }

            if (!Schema::hasColumn('equipments', 'market_complaint_date')) {
                $table->date('market_complaint_date')->nullable()->after('market_complaint_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $columns = [];
            foreach (['delivery_status', 'delivery_date', 'market_complaint_status', 'market_complaint_date'] as $column) {
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
