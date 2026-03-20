<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->string('brand_name', 120)->nullable()->after('designation');
            $table->date('manufacture_date')->nullable()->after('brand_name');
            $table->string('icon_class', 80)->nullable()->after('manufacture_date');
            $table->string('category_name', 120)->nullable()->after('icon_class');
            $table->enum('lifecycle_status', ['actif', 'inactif', 'en_maintenance'])->default('actif')->after('category_name');
            $table->text('description')->nullable()->after('lifecycle_status');

            $table->index(['category_name', 'lifecycle_status']);
        });
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->dropIndex(['category_name', 'lifecycle_status']);
            $table->dropColumn([
                'brand_name',
                'manufacture_date',
                'icon_class',
                'category_name',
                'lifecycle_status',
                'description',
            ]);
        });
    }
};
