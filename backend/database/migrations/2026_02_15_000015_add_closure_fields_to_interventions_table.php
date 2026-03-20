<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->date('date_end')->nullable()->after('date_start');
            $table->string('closure_type', 10)->nullable()->after('status');
            $table->string('failure_cause', 255)->nullable()->after('closure_type');
            $table->text('closure_note')->nullable()->after('failure_cause');
            $table->string('closed_by_name', 150)->nullable()->after('closure_note');
            $table->timestamp('closed_at')->nullable()->after('closed_by_name');

            $table->index(['status', 'closure_type']);
            $table->index('closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropIndex(['status', 'closure_type']);
            $table->dropIndex(['closed_at']);
            $table->dropColumn([
                'date_end',
                'closure_type',
                'failure_cause',
                'closure_note',
                'closed_by_name',
                'closed_at',
            ]);
        });
    }
};
