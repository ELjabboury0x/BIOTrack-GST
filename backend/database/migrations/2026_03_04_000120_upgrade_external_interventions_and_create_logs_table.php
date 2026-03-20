<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('external_interventions')) {
            Schema::table('external_interventions', function (Blueprint $table) {
                if (!Schema::hasColumn('external_interventions', 'ticket_number')) {
                    $table->string('ticket_number', 60)->nullable()->after('id');
                }

                if (!Schema::hasColumn('external_interventions', 'service_name')) {
                    $table->string('service_name', 180)->nullable()->after('company_id');
                }

                if (!Schema::hasColumn('external_interventions', 'failure_datetime')) {
                    $table->dateTime('failure_datetime')->nullable()->after('service_name');
                }

                if (!Schema::hasColumn('external_interventions', 'arrival_datetime')) {
                    $table->dateTime('arrival_datetime')->nullable()->after('first_call_datetime');
                }

                if (!Schema::hasColumn('external_interventions', 'intervention_description')) {
                    $table->text('intervention_description')->nullable()->after('resolution_datetime');
                }

                if (!Schema::hasColumn('external_interventions', 'replaced_parts')) {
                    $table->text('replaced_parts')->nullable()->after('intervention_description');
                }

                if (!Schema::hasColumn('external_interventions', 'technician_name')) {
                    $table->string('technician_name', 180)->nullable()->after('replaced_parts');
                }

                if (!Schema::hasColumn('external_interventions', 'status')) {
                    $table->string('status', 40)->default('ouvert')->after('technician_name');
                }

                if (!Schema::hasColumn('external_interventions', 'resolution_status')) {
                    $table->string('resolution_status', 40)->nullable()->after('status');
                }

                if (!Schema::hasColumn('external_interventions', 'intervention_duration_hours')) {
                    $table->decimal('intervention_duration_hours', 10, 2)->nullable()->after('resolution_status');
                }
            });

            if (Schema::hasColumn('external_interventions', 'intervention_status') && Schema::hasColumn('external_interventions', 'status')) {
                DB::table('external_interventions')
                    ->whereNull('status')
                    ->update([
                        'status' => DB::raw('COALESCE(intervention_status, "ouvert")'),
                    ]);
            }

            if (Schema::hasColumn('external_interventions', 'technician_arrival_datetime') && Schema::hasColumn('external_interventions', 'arrival_datetime')) {
                DB::table('external_interventions')
                    ->whereNull('arrival_datetime')
                    ->update([
                        'arrival_datetime' => DB::raw('technician_arrival_datetime'),
                    ]);
            }

            if (Schema::hasColumn('external_interventions', 'first_call_datetime') && Schema::hasColumn('external_interventions', 'resolution_datetime') && Schema::hasColumn('external_interventions', 'intervention_duration_hours')) {
                DB::statement('UPDATE external_interventions SET intervention_duration_hours = TIMESTAMPDIFF(MINUTE, first_call_datetime, resolution_datetime) / 60 WHERE first_call_datetime IS NOT NULL AND resolution_datetime IS NOT NULL AND resolution_datetime >= first_call_datetime');
            }

            Schema::table('external_interventions', function (Blueprint $table) {
                $table->index('company_id', 'external_interventions_company_idx_v2');
                $table->index('equipment_id', 'external_interventions_equipment_idx_v2');
                $table->index('ticket_number', 'external_interventions_ticket_idx_v2');
            });
        }

        if (!Schema::hasTable('external_intervention_logs')) {
            Schema::create('external_intervention_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('external_intervention_id')->constrained('external_interventions')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action_type', 60);
                $table->string('from_status', 40)->nullable();
                $table->string('to_status', 40)->nullable();
                $table->json('payload')->nullable();
                $table->dateTime('logged_at');
                $table->timestamps();

                $table->index('external_intervention_id', 'external_intervention_logs_ei_idx');
                $table->index('action_type', 'external_intervention_logs_action_idx');
                $table->index('logged_at', 'external_intervention_logs_logged_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('external_intervention_logs');

        if (Schema::hasTable('external_interventions')) {
            Schema::table('external_interventions', function (Blueprint $table) {
                foreach ([
                    'external_interventions_company_idx_v2',
                    'external_interventions_equipment_idx_v2',
                    'external_interventions_ticket_idx_v2',
                ] as $indexName) {
                    try {
                        $table->dropIndex($indexName);
                    } catch (\Throwable $e) {
                    }
                }

                foreach ([
                    'ticket_number',
                    'service_name',
                    'failure_datetime',
                    'arrival_datetime',
                    'intervention_description',
                    'replaced_parts',
                    'technician_name',
                    'status',
                    'resolution_status',
                    'intervention_duration_hours',
                ] as $column) {
                    if (Schema::hasColumn('external_interventions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
