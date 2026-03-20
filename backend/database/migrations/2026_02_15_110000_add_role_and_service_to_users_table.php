<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('admin')->after('password');
            $table->foreignId('service_id')->nullable()->after('role')->constrained('services')->cascadeOnUpdate()->nullOnDelete();

            $table->index('role');
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['service_id']);
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn('role');
        });
    }
};
