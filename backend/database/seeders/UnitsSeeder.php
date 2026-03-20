<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class UnitsSeeder extends Seeder
{
    public function run(): void
    {
        // Define units for each service
        $serviceUnits = [
            'Réanimation Pédiatrique' => [
                'Unité de Soins Intensifs',
                'Unité de Surveillance Continue',
            ],
            'Urgences Pédiatriques' => [
                'Unité d\'Accueil et Tri',
                'Unité de Soins d\'Urgence',
                'Unité d\'Observation',
            ],
            'Consultations et Explorations Fonctionnelles Pédiatriques' => [
                'Unité de Consultation',
                'Unité d\'Explorations Fonctionnelles',
            ],
            'Chirurgie Pédiatrique Traumato-orthopédique' => [
                'Unité d\'Hospitalisation',
                'Unité de Consultation Chirurgicale',
            ],
            'Chirurgie Pédiatrique Urologique-Viscérale' => [
                'Unité d\'Hospitalisation',
                'Unité de Consultation Chirurgicale',
            ],
            'Néonatologie (Réanimation néonatale)' => [
                'Unité de Réanimation Néonatale',
                'Unité de Soins Intensifs Néonatals',
                'Unité Kangourou',
            ],
            'Pédiatrie' => [
                'Unité d\'Hospitalisation A',
                'Unité d\'Hospitalisation B',
                'Unité de Consultation',
            ],
            'Unité d\'Oncologie Pédiatrique' => [
                'Unité de Chimiothérapie',
                'Unité d\'Hospitalisation',
            ],
            'Unité Technique d\'Accouchement' => [
                'Salle d\'Accouchement',
                'Salle de Travail',
            ],
            'Unité de gynécologie' => [
                'Unité d\'Hospitalisation',
                'Unité de Consultation',
            ],
            'Unité d\'obstétrique' => [
                'Unité d\'Hospitalisation',
                'Unité de Grossesse à Risque',
            ],
            'Unité de PMA' => [
                'Laboratoire PMA',
                'Unité de Consultation PMA',
            ],
            'Bloc Opératoire Central - Module 3' => [
                'Salle d\'Opération 1',
                'Salle d\'Opération 2',
                'Salle de Réveil',
            ],
            'Bloc Opératoire Central - Module 4' => [
                'Salle d\'Opération 1',
                'Salle d\'Opération 2',
                'Salle de Réveil',
            ],
            'Bloc Opératoire Central - Réveil Enfant' => [
                'Salle de Réveil',
                'Unité de Surveillance',
            ],
            'Hôpital Universitaire Mère-Enfant Mohammed VI-Tanger' => [
                'Service Biomédical',
                'Service Technique',
                'Administration',
            ],
        ];

        $created = 0;

        foreach ($serviceUnits as $serviceName => $unitNames) {
            $service = Service::where('name', $serviceName)->first();

            if (!$service) {
                $this->command->warn("Service not found: {$serviceName}");
                continue;
            }

            foreach ($unitNames as $unitName) {
                Unit::firstOrCreate(
                    ['service_id' => $service->id, 'name' => $unitName]
                );
                $created++;
            }
        }

        $this->command->info("Created/verified {$created} units across services.");

        // Assign existing users to the first unit of their service
        $usersWithoutUnit = User::whereNotNull('service_id')->whereNull('unit_id')->get();

        foreach ($usersWithoutUnit as $user) {
            $firstUnit = Unit::where('service_id', $user->service_id)->orderBy('id')->first();
            if ($firstUnit) {
                $user->unit_id = $firstUnit->id;
                $user->save();
                $this->command->info("  Assigned user '{$user->name}' to unit '{$firstUnit->name}'");
            }
        }

        // Ensure admin has a service + unit if not set
        $admin = User::where('login', 'ADMIN')->first();
        if ($admin && !$admin->service_id) {
            $hospitalService = Service::where('name', 'like', '%Mohammed VI%')->first();
            if ($hospitalService) {
                $adminUnit = Unit::where('service_id', $hospitalService->id)->first();
                $admin->service_id = $hospitalService->id;
                $admin->unit_id = $adminUnit?->id;
                $admin->save();
                $this->command->info("  Assigned admin to hospital service + unit.");
            }
        }
    }
}
