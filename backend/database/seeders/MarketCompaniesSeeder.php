<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Market;
use Illuminate\Database\Seeder;

class MarketCompaniesSeeder extends Seeder
{
    public function run(): void
    {
        $mapping = [
            '01/2020'  => 'AGENTIS',
            '02/2020'  => 'AGENTIS',
            '60/2020'  => 'NUMELEC',
            '62/2020'  => 'NUMELEC',
            '32/2020'  => 'CLAES MEDICAL SERVICE',
            '161/2020' => 'CLAES MEDICAL SERVICE',
            '130/2020' => 'SAHMED',
            '51/2020'  => 'MEDICAR',
            '52/2020'  => 'MEDICAR',
            '57/2020'  => 'MEDICAR',
            '58/2020'  => 'MEDICAR',
            '59/2020'  => 'MEDICAR',
            '35/2020'  => 'BOTECH',
            '56/2020'  => 'BOTECH',
            '75/2020'  => 'T2S',
            '53/2020'  => 'SCRIM',
            '25/2020'  => 'CARREFOUR MEDICAL',
            '163/2020' => 'PROMAMEC',
            '132/2020' => 'SAHMED',
            '49/2020'  => 'DELTATEC',
            '50/2020'  => 'DELTATEC',
            '30/2020'  => 'SCRIM',
            '31/2020'  => 'SCRIM',
            '12/2020'  => 'SIEMENS',
            '22/2021'  => 'GEOMED',
            '04/2020'  => 'ERAMEDIC',
            '17/2020'  => 'ERAMEDIC',
            '43/2021'  => 'LOCAMED',
            '45/2021'  => 'LOCAMED',
            '48/2021'  => 'LOCAMED',
        ];

        $updated = 0;
        $notFound = [];

        foreach ($mapping as $marketNumber => $companyName) {
            $company = Company::firstOrCreate(['name' => $companyName]);

            $market = Market::where('market_number', $marketNumber)->first();

            if ($market) {
                $market->company_id = $company->id;
                $market->save();
                $updated++;
            } else {
                $notFound[] = $marketNumber;
            }
        }

        $this->command->info("Updated {$updated} markets with company names.");

        if (count($notFound) > 0) {
            $this->command->warn('Markets not found in DB: ' . implode(', ', $notFound));
        }
    }
}
