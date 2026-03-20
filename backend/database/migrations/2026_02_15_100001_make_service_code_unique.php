<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::table('services')
            ->whereNull('code')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($service) {
                DB::table('services')
                    ->where('id', $service->id)
                    ->update(['code' => 'SRV-' . $service->id]);
            });

        DB::statement('ALTER TABLE services MODIFY code VARCHAR(40) NOT NULL');
        DB::statement('CREATE UNIQUE INDEX services_code_unique ON services (code)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX services_code_unique ON services');
    }
};
