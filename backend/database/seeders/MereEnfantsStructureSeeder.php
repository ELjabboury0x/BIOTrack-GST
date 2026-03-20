<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Hospital;
use App\Models\Service;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MereEnfantsStructureSeeder extends Seeder
{
    public function run(): void
    {
        $hospital = Hospital::query()->updateOrCreate(
            ['code' => 'HME'],
            ['name' => 'Hôpital Mère-Enfants']
        );

        $building = Building::query()->updateOrCreate(
            ['hospital_id' => (int) $hospital->id, 'name' => 'Hôpital Mère-Enfants'],
            ['code' => 'HME-B1']
        );

        $structure = [
            'ETG4' => ['Gynécologie', 'Obstétrique'],
            'ETG3' => ['Pédiatrie', 'Unité Oncologie pédiatrique', 'CH. INF. Viscérale', 'CH. INF. Traumatologie'],
            'ETG2' => ['Direction générale'],
            'ETG1' => ['U.T.A', 'Réa Pédiatrique', 'Réa Néonatale', 'USIC', 'Rééducation cardiaque'],
            'ETG0' => ['SAUV Adultes 1 et 2', 'UHTCD Adultes 1 et 2', 'SAUV Enfants', 'UHTCD Enfants', 'Radiologie urgences', 'Bloc porte'],
            'ETG-1' => ['Locaux Techniques'],
        ];

        $floorOrder = [
            'ETG4' => 40,
            'ETG3' => 30,
            'ETG2' => 20,
            'ETG1' => 10,
            'ETG0' => 0,
            'ETG-1' => -10,
        ];

        foreach ($structure as $floorName => $services) {
            $floor = Floor::query()->updateOrCreate(
                ['building_id' => (int) $building->id, 'name' => $floorName],
                ['display_order' => $floorOrder[$floorName] ?? 0]
            );

            $zone = Zone::query()->updateOrCreate(
                ['name' => 'HME - ' . $floorName],
                ['description' => 'Zone officielle Hôpital Mère-Enfants ' . $floorName]
            );

            foreach ($services as $serviceName) {
                $baseCode = strtoupper(Str::substr(Str::slug(Str::ascii($serviceName), ''), 0, 16));
                if ($baseCode === '') {
                    $baseCode = 'HME-SERVICE';
                }
                $code = 'HME-' . $baseCode;

                $suffix = 2;
                while (
                    Service::query()
                        ->where('code', $code)
                        ->where('name', '!=', $serviceName)
                        ->exists()
                ) {
                    $candidate = 'HME-' . $baseCode . '-' . $suffix;
                    $code = Str::substr($candidate, 0, 40);
                    $suffix++;
                }

                Service::query()->updateOrCreate(
                    ['name' => $serviceName],
                    [
                        'code' => Str::substr($code, 0, 40),
                        'zone_id' => (int) $zone->id,
                        'floor_id' => (int) $floor->id,
                    ]
                );
            }
        }
    }
}
