<?php

namespace App\Console\Commands;

use App\Models\Equipment;
use App\Models\Market;
use Illuminate\Console\Command;

class AnalyzeEquipmentMarketLinks extends Command
{
    protected $signature = 'equipements:analyze-market-links';

    protected $description = 'Analyze why equipments are not linked to markets';

    public function handle(): int
    {
        $total = Equipment::query()->count();
        $linked = Equipment::query()->whereNotNull('market_id')->count();
        $unlinked = Equipment::query()->whereNull('market_id')->count();

        $unlinkedMissingLabel = Equipment::query()
            ->whereNull('market_id')
            ->where(function ($query) {
                $query->whereNull('market_label')->orWhereRaw('TRIM(market_label) = ""');
            })
            ->count();

        $unlinkedWithLabel = Equipment::query()
            ->whereNull('market_id')
            ->whereNotNull('market_label')
            ->whereRaw('TRIM(market_label) <> ""')
            ->count();

        $labels = Equipment::query()
            ->whereNull('market_id')
            ->whereNotNull('market_label')
            ->whereRaw('TRIM(market_label) <> ""')
            ->selectRaw('TRIM(market_label) as label, COUNT(*) as c')
            ->groupByRaw('TRIM(market_label)')
            ->orderByDesc('c')
            ->get();

        $knownNumbers = Market::query()->pluck('market_number')->filter()->map(fn ($v) => trim((string) $v))->values()->all();
        $knownReferences = Market::query()->pluck('reference')->filter()->map(fn ($v) => trim((string) $v))->values()->all();

        $knownNumbersLookup = array_fill_keys($knownNumbers, true);
        $knownReferencesLookup = array_fill_keys($knownReferences, true);

        $noMarketFoundCount = 0;
        $topUnmatchedLabels = [];

        foreach ($labels as $row) {
            $label = (string) $row->label;
            $count = (int) $row->c;

            $found = isset($knownNumbersLookup[$label]) || isset($knownReferencesLookup[$label]);

            if (!$found && preg_match('/\b\d{1,3}\/\d{4}\b/', $label, $m)) {
                $found = isset($knownNumbersLookup[$m[0]]);
            }

            if (!$found && preg_match('/\bM\d{1,3}-\d{4}\b/i', $label, $m)) {
                foreach ($knownReferencesLookup as $reference => $_) {
                    if (str_starts_with(strtoupper((string) $reference), strtoupper($m[0]))) {
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                $noMarketFoundCount += $count;
                if (count($topUnmatchedLabels) < 10) {
                    $topUnmatchedLabels[] = $label . ' => ' . $count;
                }
            }
        }

        $this->info("Total: {$total}");
        $this->info("Liés: {$linked}");
        $this->info("Non liés: {$unlinked}");
        $this->info("Non liés sans market_label: {$unlinkedMissingLabel}");
        $this->info("Non liés avec market_label: {$unlinkedWithLabel}");
        $this->info("Parmi ceux avec market_label, introuvables dans markets: {$noMarketFoundCount}");

        if (!empty($topUnmatchedLabels)) {
            $this->line('Top labels introuvables:');
            foreach ($topUnmatchedLabels as $line) {
                $this->line(' - ' . $line);
            }
        }

        return self::SUCCESS;
    }
}
