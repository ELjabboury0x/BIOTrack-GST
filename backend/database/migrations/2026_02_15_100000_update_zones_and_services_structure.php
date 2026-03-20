<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            if (!Schema::hasColumn('zones', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'code')) {
                $table->string('code', 40)->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'code')) {
                $table->dropColumn('code');
            }
        });

        Schema::table('zones', function (Blueprint $table) {
            if (Schema::hasColumn('zones', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
