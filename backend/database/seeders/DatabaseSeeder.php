<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ZonesSeeder::class,
            HospitalStructureSeeder::class,
            MereEnfantsStructureSeeder::class,
            GstSmartStructureSeeder::class,
            OfficialOrganisationSeeder::class,
            BdProfilesUsersSeeder::class,
            ExternalInterventionsDemoSeeder::class,
        ]);
    }
}
