<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class HospitalStructureSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            'Pôle Pédiatrique' => [
                'description' => 'Unités pédiatriques, urgences et spécialités enfant.',
                'services' => [
                    ['code' => 'RPE', 'name' => 'Réanimation Pédiatrique'],
                    ['code' => 'URP', 'name' => 'Urgences Pédiatriques'],
                    ['code' => 'CEFP', 'name' => 'Consultations et Explorations Fonctionnelles Pédiatriques'],
                    ['code' => 'TOP', 'name' => 'Chirurgie Pédiatrique Traumato-orthopédique'],
                    ['code' => 'UVP', 'name' => 'Chirurgie Pédiatrique Urologique-Viscérale'],
                    ['code' => 'NEO', 'name' => 'Néonatologie (Réanimation néonatale)'],
                    ['code' => 'PED', 'name' => 'Pédiatrie'],
                    ['code' => 'UOP', 'name' => "Unité d'Oncologie Pédiatrique"],
                ],
            ],
            'Pôle Gynéco-Obstétrique et Maternité' => [
                'description' => 'Unités techniques et soins mère-enfant.',
                'services' => [
                    ['code' => 'UTA', 'name' => "Unité Technique d'Accouchement"],
                    ['code' => 'GYN', 'name' => 'Unité de gynécologie'],
                    ['code' => 'OBS', 'name' => "Unité d'obstétrique"],
                    ['code' => 'PMA', 'name' => 'Unité de PMA'],
                ],
            ],
            'Bloc Opératoire Central' => [
                'description' => 'Modules et salle de réveil du bloc opératoire central.',
                'services' => [
                    ['code' => 'BOC M3', 'name' => 'Bloc Opératoire Central - Module 3'],
                    ['code' => 'BOC M4', 'name' => 'Bloc Opératoire Central - Module 4'],
                    ['code' => 'BOC RVE', 'name' => 'Bloc Opératoire Central - Réveil Enfant'],
                ],
            ],
        ];

        foreach ($zones as $zoneName => $payload) {
            $zone = Zone::query()->updateOrCreate(
                ['name' => $zoneName],
                ['description' => $payload['description']]
            );

            foreach ($payload['services'] as $serviceData) {
                Service::query()->updateOrCreate(
                    ['code' => $serviceData['code']],
                    [
                        'name' => $serviceData['name'],
                        'zone_id' => $zone->id,
                    ]
                );
            }
        }
    }
}
