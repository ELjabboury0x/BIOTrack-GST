<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            if (!Schema::hasColumn('hospitals', 'gst_id')) {
                $table->foreignId('gst_id')->nullable()->after('id')->constrained('gsts')->nullOnDelete();
                $table->index('gst_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            if (Schema::hasColumn('hospitals', 'gst_id')) {
                $table->dropConstrainedForeignId('gst_id');
            }
        });
    }
};
