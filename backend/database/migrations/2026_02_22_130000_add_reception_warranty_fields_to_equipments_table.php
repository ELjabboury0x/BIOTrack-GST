<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            if (!Schema::hasColumn('equipments', 'article')) {
                $table->string('article', 150)->nullable()->after('lot_number');
            }

            if (!Schema::hasColumn('equipments', 'date_reception_provisoire')) {
                $table->date('date_reception_provisoire')->nullable()->after('article');
            }

            if (!Schema::hasColumn('equipments', 'duree_garantie')) {
                $table->string('duree_garantie', 120)->nullable()->after('date_reception_provisoire');
            }

            if (!Schema::hasColumn('equipments', 'date_reception_definitive')) {
                $table->date('date_reception_definitive')->nullable()->after('duree_garantie');
            }
        });
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('equipments', 'date_reception_definitive')) {
                $columns[] = 'date_reception_definitive';
            }

            if (Schema::hasColumn('equipments', 'duree_garantie')) {
                $columns[] = 'duree_garantie';
            }

            if (Schema::hasColumn('equipments', 'date_reception_provisoire')) {
                $columns[] = 'date_reception_provisoire';
            }

            if (Schema::hasColumn('equipments', 'article')) {
                $columns[] = 'article';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
