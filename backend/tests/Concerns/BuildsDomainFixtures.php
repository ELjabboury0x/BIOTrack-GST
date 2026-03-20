<?php

namespace Tests\Concerns;

use App\Models\Company;
use App\Models\Equipment;
use App\Models\Hospital;
use App\Models\Service;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\Hash;

trait BuildsDomainFixtures
{
    private function createZone(array $overrides = []): Zone
    {
        return Zone::query()->create(array_merge([
            'name' => 'Zone ' . uniqid(),
        ], $overrides));
    }

    private function createService(array $overrides = []): Service
    {
        $zone = $overrides['zone'] ?? $this->createZone();
        unset($overrides['zone']);

        return Service::query()->create(array_merge([
            'zone_id' => $zone->id,
            'code' => 'SRV-' . random_int(1000, 9999),
            'name' => 'Service ' . uniqid(),
        ], $overrides));
    }

    private function createHospital(array $overrides = []): Hospital
    {
        return Hospital::query()->create(array_merge([
            'code' => 'HME',
            'name' => 'Hôpital Mère-Enfant',
        ], $overrides));
    }

    private function createEquipment(Service $service, ?Hospital $hospital = null, array $overrides = []): Equipment
    {
        $hospital = $hospital ?: $this->createHospital();

        return Equipment::query()->create(array_merge([
            'inventory_number_current' => 'INV-' . random_int(10000, 99999),
            'designation' => 'Équipement ' . uniqid(),
            'service_name' => $service->name,
            'unit_name' => $service->name,
            'operational_status' => 'fonctionnel',
            'hospital_id' => $hospital->id,
            'zone_id' => $service->zone_id,
            'service_id' => $service->id,
        ], $overrides));
    }

    private function createUser(string $role = 'admin', ?Service $service = null, array $overrides = []): User
    {
        $serviceId = $service?->id ?? ($overrides['service_id'] ?? null);

        return User::query()->create(array_merge([
            'name' => 'Test User ' . uniqid(),
            'login' => 'user_' . uniqid(),
            'email' => 'test_' . uniqid() . '@example.test',
            'password' => Hash::make('Password123!'),
            'role' => $role,
            'is_active' => true,
            'must_change_password' => false,
            'service_id' => $serviceId,
        ], $overrides));
    }

    private function createCompany(array $overrides = []): Company
    {
        return Company::query()->create(array_merge([
            'name' => 'Company ' . uniqid(),
        ], $overrides));
    }
}
