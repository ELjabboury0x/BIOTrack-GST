<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Equipment;
use App\Models\Market;
use Illuminate\Console\Command;

class LinkEquipmentsToMarkets extends Command
{
    protected $signature = 'equipements:link-markets
                            {--create-missing : Créer les marchés manquants à partir du numéro}
                            {--normalize-leading-zero : Normaliser 02/2020 vers 2/2020}';

    protected $description = 'Link existing equipments to markets using market label/number/reference';

    public function handle(): int
    {
        $updated = 0;
        $createdMarkets = 0;
        $createMissing = (bool) $this->option('create-missing');
        $normalizeLeadingZero = (bool) $this->option('normalize-leading-zero');
        $fallbackCompanyId = $this->fallbackCompanyId();

        Equipment::query()
            ->whereNull('market_id')
            ->whereNotNull('market_label')
            ->whereRaw('TRIM(market_label) <> ""')
            ->chunkById(200, function ($equipments) use (&$updated, &$createdMarkets, $createMissing, $normalizeLeadingZero, $fallbackCompanyId) {
                foreach ($equipments as $equipment) {
                    $market = $this->resolveMarketFromLabel($equipment->market_label, $normalizeLeadingZero);

                    if (!$market && $createMissing) {
                        $canonicalNumber = $this->extractCanonicalMarketNumber($equipment->market_label, $normalizeLeadingZero);

                        if ($canonicalNumber !== null) {
                            $market = Market::query()->firstOrCreate(
                                ['market_number' => $canonicalNumber],
                                [
                                    'reference' => null,
                                    'market_date' => $this->inferMarketDateFromCanonicalNumber($canonicalNumber),
                                    'company_id' => $equipment->company_id ?: $fallbackCompanyId,
                                ]
                            );

                            if ($market->wasRecentlyCreated) {
                                $createdMarkets++;
                            }
                        }
                    }

                    if (!$market) {
                        continue;
                    }

                    $equipment->market_id = $market->id;
                    if (!$equipment->company_id && $market->company_id) {
                        $equipment->company_id = $market->company_id;
                    }
                    $equipment->save();
                    $updated++;
                }
            });

        $linked = Equipment::query()->whereNotNull('market_id')->count();
        $total = Equipment::query()->count();

        $this->info("Équipements mis à jour: {$updated}");
        $this->info("Marchés créés: {$createdMarkets}");
        $this->info("Équipements liés à un marché: {$linked}/{$total}");

        return self::SUCCESS;
    }

    private function resolveMarketFromLabel(?string $label, bool $normalizeLeadingZero = false): ?Market
    {
        $value = trim((string) $label);

        if ($value === '') {
            return null;
        }

        $market = Market::query()
            ->where('market_number', $value)
            ->orWhere('reference', $value)
            ->first();

        if (!$market) {
            $canonicalNumber = $this->extractCanonicalMarketNumber($value, $normalizeLeadingZero);
            if ($canonicalNumber !== null) {
                $market = Market::query()->where('market_number', $canonicalNumber)->first();
            }
        }

        if (!$market && preg_match('/\b\d{1,3}\/\d{4}\b/', $value, $match)) {
            $market = Market::query()->where('market_number', $match[0])->first();
        }

        if (!$market && preg_match('/\bM\d{1,3}-\d{4}\b/i', $value, $match)) {
            $market = Market::query()->where('reference', 'like', strtoupper($match[0]) . '%')->first();
        }

        return $market;
    }

    private function extractCanonicalMarketNumber(?string $label, bool $normalizeLeadingZero = false): ?string
    {
        $value = trim((string) $label);

        if ($value === '') {
            return null;
        }

        if (preg_match('/\b(\d{1,3})\/(\d{4})\b/', $value, $match)) {
            $number = $match[1];
            if ($normalizeLeadingZero) {
                $number = (string) ((int) $number);
            }

            return $number . '/' . $match[2];
        }

        return null;
    }

    private function inferMarketDateFromCanonicalNumber(string $canonicalNumber): string
    {
        if (preg_match('/\/(\d{4})$/', $canonicalNumber, $match)) {
            return $match[1] . '-01-01';
        }

        return now()->toDateString();
    }

    private function fallbackCompanyId(): int
    {
        $company = Company::query()->firstOrCreate(['name' => 'Inconnue']);

        return (int) $company->id;
    }
}
