<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('spare_parts', function (Blueprint $table) {
            $table->string('phase', 20)->nullable()->after('supplier');
            $table->string('entry_mode', 20)->nullable()->after('phase');

            $table->date('discharge_date')->nullable()->after('entry_mode');
            $table->date('return_date')->nullable()->after('discharge_date');
            $table->string('serial_number', 190)->nullable()->after('return_date');

            $table->foreignId('action_user_id')->nullable()->after('serial_number')->constrained('users')->nullOnDelete();
            $table->foreignId('assistant_technician_id')->nullable()->after('action_user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->after('assistant_technician_id')->constrained('services')->nullOnDelete();
            $table->foreignId('major_signer_id')->nullable()->after('service_id')->constrained('users')->nullOnDelete();
            $table->foreignId('return_technician_id')->nullable()->after('major_signer_id')->constrained('users')->nullOnDelete();

            $table->string('condition_state', 20)->nullable()->after('return_technician_id');
            $table->text('comment')->nullable()->after('condition_state');
            $table->string('document_pdf_path')->nullable()->after('comment');

            $table->index('phase');
            $table->index('service_id');
            $table->index('discharge_date');
            $table->index('return_date');
        });
    }

    public function down(): void
    {
        Schema::table('spare_parts', function (Blueprint $table) {
            $table->dropForeign(['action_user_id']);
            $table->dropForeign(['assistant_technician_id']);
            $table->dropForeign(['service_id']);
            $table->dropForeign(['major_signer_id']);
            $table->dropForeign(['return_technician_id']);

            $table->dropIndex(['phase']);
            $table->dropIndex(['service_id']);
            $table->dropIndex(['discharge_date']);
            $table->dropIndex(['return_date']);

            $table->dropColumn([
                'phase',
                'entry_mode',
                'discharge_date',
                'return_date',
                'serial_number',
                'action_user_id',
                'assistant_technician_id',
                'service_id',
                'major_signer_id',
                'return_technician_id',
                'condition_state',
                'comment',
                'document_pdf_path',
            ]);
        });
    }
};
