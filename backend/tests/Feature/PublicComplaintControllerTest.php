<?php

namespace Tests\Feature;

use App\Models\Complaint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsDomainFixtures;
use Tests\TestCase;

class PublicComplaintControllerTest extends TestCase
{
    use RefreshDatabase;
    use BuildsDomainFixtures;

    public function test_public_complaint_form_displays_equipment_for_service(): void
    {
        $service = $this->createService([
            'code' => 'RPE',
            'name' => 'Réanimation Pédiatrique',
        ]);

        $equipment = $this->createEquipment($service, null, [
            'designation' => 'Moniteur multiparamétrique',
            'service_name' => 'REANIMATION PEDIATRIQUE',
        ]);

        $response = $this->get(route('public.reclamation.form', ['service_code' => 'RPE']));

        $response
            ->assertOk()
            ->assertViewHas('equipments', function ($equipments) use ($equipment): bool {
                return $equipments->contains(fn ($item) => (int) $item->id === (int) $equipment->id);
            });
    }

    public function test_public_complaint_store_creates_record_with_selected_equipment(): void
    {
        $service = $this->createService([
            'code' => 'RPE',
            'name' => 'Réanimation Pédiatrique',
        ]);

        $equipment = $this->createEquipment($service, null, [
            'designation' => 'Respirateur test',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])->post(route('public.reclamation.store', ['service_code' => 'RPE']), [
            '_token' => 'test-token',
            'reported_by_name' => 'Infirmier Test',
            'equipment_id' => $equipment->id,
            'room_number' => 'A-12',
            'description' => 'Panne intermittente constatée',
            'priority' => 'urgent',
        ]);

        $response->assertRedirect(route('public.reclamation.form', ['service_code' => 'RPE']));

        $this->assertDatabaseHas('complaints', [
            'service_id' => $service->id,
            'equipment_id' => $equipment->id,
            'reported_by_name' => 'Infirmier Test',
            'priority' => 'urgent',
            'status' => 'open',
        ]);

        $this->assertSame(1, Complaint::query()->count());
    }
}
