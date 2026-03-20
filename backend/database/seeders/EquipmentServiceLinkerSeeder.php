<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Maps the text field `service_name` on equipments to the correct `service_id`
 * based on known name variants for each service in the services table.
 */
class EquipmentServiceLinkerSeeder extends Seeder
{
    public function run(): void
    {
        // service_id => array of service_name patterns (case-insensitive LIKE)
        $mapping = [
            // 1 - RPE - Réanimation Pédiatrique
            1 => [
                'REANIMATION PEDIATRIQUE',
                'REA PEDIATRIQUE',
                'RPE%',
            ],

            // 2 - URP - Urgences Pédiatriques
            2 => [
                'URGENCES PEDIATRIQUES',
                'URGENCE PEDIATRIQUE%',
                'URP%',
            ],

            // 3 - CEFP - Consultations et Explorations Fonctionnelles Pédiatriques
            3 => [
                'CONSULTATIONS EXTERNES PEDIATRIQUES',
                'CONSULTATIONS PEDIATRIQUES',
                'CEF',
                'CEEF',
                'CEFP%',
            ],

            // 4 - TOP - Chirurgie Pédiatrique Traumato-orthopédique
            4 => [
                'CHIRURGIE PEDIATRIQUE TRAUMATO%',
                'CHIRERGIE TRAUMATO%',
                'CHIR PED TRO',
                'TRAUMATO-ORTHO PEDIATRIQUE',
                'Chr. P_d Traumato%',
            ],

            // 5 - UVP - Chirurgie Pédiatrique Urologique-Viscérale
            5 => [
                'CHIRURGIE PEDIATRIQUE URO%',
                'CHIRERGIE URO%',
                'CHIRURGIE URO-VISCERALE PEDIATRIQUE',
                'CHIR PED UVP',
                'URO-VISC PEDIATRIQUE',
                'CVP',
            ],

            // 6 - NEO - Néonatologie (Réanimation néonatale)
            6 => [
                'NEONATOLOGIE',
                'NEONATALOGIE',
                'REANIMATION NEONATOLOG%',
                'REANIMATION NEONATOLOGIE',
                'NEONAT%',
            ],

            // 7 - PED - Pédiatrie
            7 => [
                'PEDIATRIE',
            ],

            // 8 - UOP - Unité d'Oncologie Pédiatrique
            8 => [
                'Oncologie P_diatrique',
                'ONCOLOGIE PEDIATRIE',
                'ONCOLOGIE PED%',
            ],

            // 9 - UTA - Unité Technique d'Accouchement
            9 => [
                'UTA',
                'UTA %',
            ],

            // 10 - GYN - Unité de gynécologie
            10 => [
                'GYNECOLOGIE',
            ],

            // 11 - OBS - Unité d'obstétrique
            11 => [
                'OBSTETRIQUE',
            ],

            // 12 - PMA - Unité de PMA
            12 => [
                'PMA',
            ],

            // 13 - BOC M3 - Bloc Opératoire Central - Module 3
            13 => [
                'BOC M3',
                'BOC MODULE 3',
                'BLOC MODULE 3',
            ],

            // 14 - BOC M4 - Bloc Opératoire Central - Module 4
            14 => [
                'BOC M4',
                'BOC MODULE 4',
                'BLOC MODULE 4',
            ],

            // 15 - BOC RVE - Bloc Opératoire Central - Réveil Enfant
            15 => [
                'BOC REVEIL ENFANT',
                'REVEIL ENFANT%',
            ],
        ];

        $totalUpdated = 0;

        foreach ($mapping as $serviceId => $patterns) {
            foreach ($patterns as $pattern) {
                $count = Equipment::query()
                    ->whereNull('service_id')
                    ->where('service_name', 'LIKE', $pattern)
                    ->update(['service_id' => $serviceId]);

                if ($count > 0) {
                    $this->command->info("  service_id={$serviceId}: '{$pattern}' → {$count} équipements");
                    $totalUpdated += $count;
                }
            }
        }

        $remaining = Equipment::query()->whereNull('service_id')->count();

        $this->command->newLine();
        $this->command->info("Terminé : {$totalUpdated} équipements liés à un service.");
        $this->command->info("Restant sans service : {$remaining}");
    }
}
