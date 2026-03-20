<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsDomainFixtures;
use Tests\TestCase;

class SidebarRoleSmokeTest extends TestCase
{
    use RefreshDatabase;
    use BuildsDomainFixtures;

    public function test_technicien_sees_complaints_and_corrective_links_in_sidebar(): void
    {
        $service = $this->createService([
            'code' => 'RPE',
            'name' => 'Réanimation Pédiatrique',
        ]);
        $user = $this->createUser('technicien', $service);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSeeText('Historique Réclamations')
            ->assertSeeText('Maintenance corrective');
    }

    public function test_manager_does_not_see_complaints_link_in_sidebar(): void
    {
        $service = $this->createService([
            'code' => 'RPE',
            'name' => 'Réanimation Pédiatrique',
        ]);
        $user = $this->createUser('manager', $service);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertDontSeeText('Historique Réclamations')
            ->assertSeeText('Maintenance corrective');
    }
}
