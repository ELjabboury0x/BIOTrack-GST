<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE maintenance_reports MODIFY intervention_type ENUM('preventive','curative','diagnostic') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE maintenance_reports MODIFY intervention_type ENUM('preventive','curative') NOT NULL");
    }
};
