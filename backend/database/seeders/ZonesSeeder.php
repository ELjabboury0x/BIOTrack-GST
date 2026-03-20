<?php

namespace Database\Seeders;

use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZonesSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            'Réanimation Pédiatrique',
            'Urgences pédiatriques',
            'Consultations et Explorations Fonctionnelles Pédiatriques',
            'Chirurgie Pédiatrique Traumato-orthopédique',
            'Chirurgie Pédiatrique Urologique-Viscérale',
            'Néonatologie (Réanimation néonatale)',
            'Pédiatrie',
            "Unité d'Oncologie Pédiatrique",
            "Unité Technique d'Accouchement",
            'Unité de gynécologie',
            "Unité d'obstétrique",
            'Unité de PMA',
            'Bloc Opératoire Central - Module 3',
            'Bloc Opératoire Central - Module 4',
            'Bloc Opératoire Central - Réveil Enfant',
        ];

        foreach ($zones as $zoneName) {
            Zone::query()->updateOrCreate(
                ['name' => $zoneName],
                []
            );
        }
    }
}
