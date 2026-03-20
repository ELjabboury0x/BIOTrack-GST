<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Gst;
use App\Models\Hospital;
use App\Models\Service;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfficialOrganisationSeeder extends Seeder
{
    public function run(): void
    {
        $gst = Gst::query()->updateOrCreate(
            ['name' => 'GST Tanger–Tétouan–Al Hoceima'],
            ['region' => 'Tanger–Tétouan–Al Hoceima']
        );

        $hme = Hospital::query()->updateOrCreate(
            ['code' => 'HME'],
            ['name' => 'Hôpital Mère-Enfants', 'gst_id' => (int) $gst->id]
        );

        Hospital::query()->updateOrCreate(
            ['code' => 'HSP'],
            ['name' => 'Hôpital des Spécialités', 'gst_id' => (int) $gst->id]
        );

        Building::query()->updateOrCreate(
            ['hospital_id' => (int) $hme->id, 'name' => 'Hôpital Mère-Enfants'],
            ['code' => 'HME-B1']
        );

        $structure = [
            'ETG4' => ['Gynécologie', 'Obstétrique'],
            'ETG3' => ['Pédiatrie', 'Unité Oncologie pédiatrique', 'CH. INF. Viscérale', 'CH. INF. Traumatologie'],
            'ETG2' => ['Direction générale'],
            'ETG1' => ['U.T.A', 'Réa Pédiatrique', 'Unité Réa Néonatale', 'U.S.I.C', 'Unité Rééducation cardiaque'],
            'ETG0' => ['SAUV Adultes 1 et 2', 'UHTCD Adultes 1 et 2', 'SAUV Enfants', 'UHTCD Enfants', 'Radiologie des urgences', 'Bloc porte'],
            'ETG-1' => ['Locaux Techniques'],
        ];

        $orderByFloor = [
            'ETG4' => 40,
            'ETG3' => 30,
            'ETG2' => 20,
            'ETG1' => 10,
            'ETG0' => 0,
            'ETG-1' => -10,
        ];

        foreach ($structure as $floorName => $services) {
            $floor = Floor::query()->updateOrCreate(
                ['hospital_id' => (int) $hme->id, 'name' => $floorName],
                ['display_order' => (int) ($orderByFloor[$floorName] ?? 0)]
            );

            $zone = Zone::query()->updateOrCreate(
                ['name' => 'HME - ' . $floorName],
                ['description' => 'Organisation officielle HME ' . $floorName]
            );

            foreach ($services as $serviceName) {
                $baseCode = strtoupper(Str::substr(Str::slug(Str::ascii($serviceName), ''), 0, 16));
                if ($baseCode === '') {
                    $baseCode = 'HME-SERVICE';
                }
                $code = Str::substr('HME-' . $baseCode, 0, 40);
                $suffix = 2;
                while (Service::query()->where('code', $code)->where('name', '!=', $serviceName)->exists()) {
                    $candidate = 'HME-' . $baseCode . '-' . $suffix;
                    $code = Str::substr($candidate, 0, 40);
                    $suffix++;
                }

                Service::query()->updateOrCreate(
                    ['name' => $serviceName],
                    [
                        'code' => $code,
                        'zone_id' => (int) $zone->id,
                        'floor_id' => (int) $floor->id,
                    ]
                );
            }
        }
    }
}
