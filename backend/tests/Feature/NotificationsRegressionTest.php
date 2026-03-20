<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Concerns\BuildsDomainFixtures;
use Tests\TestCase;

class NotificationsRegressionTest extends TestCase
{
    use RefreshDatabase;
    use BuildsDomainFixtures;

    public function test_missing_complaint_notification_exposes_archived_open_url(): void
    {
        $admin = $this->createUser('admin');
        $notificationId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\\Notifications\\ComplaintCreatedNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->id,
            'data' => json_encode([
                'complaint_id' => 999999,
                'service_name' => 'Service archivé',
                'equipment_label' => 'Équipement archivé',
                'priority' => 'urgent',
                'reported_by_name' => 'Archive',
                'status' => 'open',
            ], JSON_THROW_ON_ERROR),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson(route('dashboard.notifications.complaints'));

        $response
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('items.0.title', 'Ancienne réclamation')
            ->assertJsonPath('items.0.complaint_id', 999999)
            ->assertJsonPath('items.0.open_url', route('dashboard.notifications.complaints.archive', ['notificationId' => $notificationId]));
    }
}
