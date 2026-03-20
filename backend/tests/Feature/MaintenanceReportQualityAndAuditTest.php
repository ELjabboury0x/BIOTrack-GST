<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsDomainFixtures;
use Tests\TestCase;

class MaintenanceReportQualityAndAuditTest extends TestCase
{
    use RefreshDatabase;
    use BuildsDomainFixtures;

    public function test_complaint_status_update_creates_audit_log_entry(): void
    {
        $service = $this->createService([
            'code' => 'RPE',
            'name' => 'Réanimation Pédiatrique',
        ]);
        $equipment = $this->createEquipment($service);
        $admin = $this->createUser('admin');

        $complaint = Complaint::query()->create([
            'service_id' => $service->id,
            'equipment_id' => $equipment->id,
            'reported_by_name' => 'Agent Test',
            'description' => 'Incident biomédical',
            'priority' => 'medium',
            'status' => 'open',
            'attachment_path' => [],
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['_token' => 'test-token'])
            ->patch(route('reclamations.status.update', $complaint), [
            '_token' => 'test-token',
            'status' => 'resolved',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'resolved',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'complaint.status.updated',
            'subject_type' => Complaint::class,
            'subject_id' => (string) $complaint->id,
            'actor_user_id' => (string) $admin->id,
        ]);
    }

    public function test_curative_page_renders_corrective_quality_samples(): void
    {
        $admin = $this->createUser('admin');
        $this->createService(['code' => 'RPE', 'name' => 'Réanimation Pédiatrique']);
        $this->createCompany(['name' => 'ACME MED']);

        DB::table('bilan_maintenance_correctives')->insert([
            [
                'company_name' => 'ACME MED',
                'equipment_designation' => 'Pompe',
                'brand_name' => 'B1',
                'model_name' => 'M1',
                'serial_number' => 'SN-1',
                'market_or_contract_ref' => 'MC-1',
                'failure_details' => 'D1',
                'observations' => 'O1',
                'service_names' => 'Réanimation',
                'intervention_date_text' => '2026-03-01',
                'source_file' => 'Maintenance corrective.xlsx',
                'source_sheet' => 'Feuil1',
                'source_row' => 2,
                'row_hash' => hash('sha256', 'row-1'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_name' => 'ACME MED',
                'equipment_designation' => 'Pompe',
                'brand_name' => 'B1',
                'model_name' => 'M1',
                'serial_number' => 'SN-1',
                'market_or_contract_ref' => 'MC-1',
                'failure_details' => 'D1',
                'observations' => 'O1',
                'service_names' => 'Réanimation',
                'intervention_date_text' => '2026-03-01',
                'source_file' => 'Maintenance corrective.xlsx',
                'source_sheet' => 'Feuil1',
                'source_row' => 3,
                'row_hash' => hash('sha256', 'row-2'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_name' => 'SOCIETE INCONNUE',
                'equipment_designation' => 'Incubateur',
                'brand_name' => 'B2',
                'model_name' => 'M2',
                'serial_number' => 'SN-2',
                'market_or_contract_ref' => 'MC-2',
                'failure_details' => 'D2',
                'observations' => 'O2',
                'service_names' => 'Urgences',
                'intervention_date_text' => '99/99/2099',
                'source_file' => 'Maintenance corrective.xlsx',
                'source_sheet' => 'Feuil1',
                'source_row' => 4,
                'row_hash' => hash('sha256', 'row-3'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('maintenance-reports.index', ['type' => 'curative']));

        $response
            ->assertOk()
            ->assertSeeText('Exemples doublons (top 10)')
            ->assertSeeText('Sociétés non reconnues (top 10)')
            ->assertSeeText('Dates invalides (top 10)');
    }
}
