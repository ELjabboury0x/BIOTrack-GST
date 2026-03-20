<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Hospital;
use App\Models\Market;
use App\Models\Zone;
use App\Models\MarketEquipmentImportLine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportBiomedicalEquipmentsExcel extends Command
{
    protected $signature = 'gmao:import-biomedical-excel {--file=* : Fichier(s) Excel à importer (chemin absolu ou relatif)} {--dir= : Dossier contenant les fichiers Excel} {--replace : Supprimer les anciens équipements/marchés avant import}';

    protected $description = 'Importe les équipements biomédicaux depuis les fichiers Excel et les rattache aux marchés';

    public function handle(): int
    {
        ini_set('memory_limit', '1024M');

        $files = $this->resolveFiles();

        if (empty($files)) {
            $this->error('Aucun fichier Excel trouvé.');
            return self::FAILURE;
        }

        if ($this->option('replace')) {
            $this->replaceExistingImportedData();
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $skippedFiles = 0;
        $touchedMarketIds = [];

        foreach ($files as $filePath) {
            $this->line("Traitement: {$filePath}");

            try {
                $spreadsheet = IOFactory::load($filePath);
            } catch (\Throwable $exception) {
                $skippedFiles++;
                $this->warn('Fichier ignoré (lecture impossible): ' . basename($filePath));
                continue;
            }

            $companyMap = $this->buildCompanyMapFromColorCode($spreadsheet);
            $fileBaseName = pathinfo($filePath, PATHINFO_FILENAME);
            $marketNumberFromFile = $this->extractMarketNumberFromText($fileBaseName);
            $fileYear = $this->extractYearFromText($fileBaseName);
            $zoneNameFromFile = $this->extractZoneFromText($fileBaseName);
            $zone = $zoneNameFromFile ? Zone::firstOrCreate(['name' => $zoneNameFromFile]) : null;
            $companyNameFromFile = $companyMap[$marketNumberFromFile] ?? 'Inconnue';

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $title = (string) $worksheet->getTitle();

                $rows = $worksheet->toArray(null, true, true, false);
                if (count($rows) < 2) {
                    continue;
                }

                [$headerIndex, $columns] = $this->detectColumns($rows);
                if ($headerIndex === null) {
                    continue;
                }

                $carryMarketNumber = null;
                $carryMarketObject = null;
                $carryCompanyName = null;
                $carryLotNumber = null;

                for ($i = $headerIndex + 1; $i < count($rows); $i++) {
                    $row = $rows[$i] ?? [];

                    $designation = $this->readCell($row, $columns['designation'] ?? null);
                    if ($designation === null) {
                        $skipped++;
                        continue;
                    }

                    $serialNumber = $this->readCell($row, $columns['serial'] ?? null);
                    $serviceName = $this->readCell($row, $columns['service'] ?? null);

                    $marketNumber = $this->readCell($row, $columns['market_number'] ?? null);
                    if ($marketNumber !== null) {
                        $carryMarketNumber = $marketNumber;
                    } else {
                        $marketNumber = $carryMarketNumber;
                    }

                    $marketObject = $this->readCell($row, $columns['market_object'] ?? null);
                    if ($marketObject !== null) {
                        $carryMarketObject = $marketObject;
                    } else {
                        $marketObject = $carryMarketObject;
                    }

                    $companyNameFromRow = $this->readCell($row, $columns['company_name'] ?? null);
                    if ($companyNameFromRow !== null) {
                        $carryCompanyName = $companyNameFromRow;
                    } else {
                        $companyNameFromRow = $carryCompanyName;
                    }

                    $lotNumber = $this->readCell($row, $columns['lot_number'] ?? null);
                    if ($lotNumber !== null) {
                        $carryLotNumber = $lotNumber;
                    } else {
                        $lotNumber = $carryLotNumber;
                    }

                    $article = $this->readCell($row, $columns['article'] ?? null);
                    $quantity = $this->nullableNumber($this->readCell($row, $columns['quantity'] ?? null));

                    $deliveryStatus = $this->readCell($row, $columns['delivery_status'] ?? null);
                    $deliveryDateRaw = $this->readCell($row, $columns['delivery_date'] ?? null);
                    $complaintStatus = $this->readCell($row, $columns['complaint_status'] ?? null);
                    $complaintDateRaw = $this->readCell($row, $columns['complaint_date'] ?? null);

                    $deliveryReceptionProvisoire = $this->readCell($row, $columns['delivery_reception_provisoire'] ?? null);
                    if ($deliveryReceptionProvisoire === null) {
                        $deliveryReceptionProvisoire = $deliveryStatus;
                    }
                    $observations = $this->readCell($row, $columns['observations'] ?? null);
                    $recommendations = $this->readCell($row, $columns['recommendations'] ?? null);
                    $annualMaintenanceAmountHt = $this->nullableNumber($this->readCell($row, $columns['annual_maintenance_amount_ht'] ?? null));

                    $inventoryRaw = $this->readCell($row, $columns['inventory'] ?? 0);
                    $inventory = $this->normalizeInventory($inventoryRaw);
                    if ($inventory === null || !$this->looksLikeInventory($inventory)) {
                        $inventory = $this->buildSyntheticInventory(
                            $filePath,
                            $title,
                            $i,
                            $designation,
                            $lotNumber,
                            $article
                        );
                    }

                    $effectiveMarketNumber = $this->normalizeMarketNumberWithYear(
                        $marketNumber,
                        $marketNumberFromFile,
                        $fileYear
                    );

                    if ($effectiveMarketNumber !== null) {
                        $effectiveMarketNumber = mb_substr($effectiveMarketNumber, 0, 80);
                    }

                    $effectiveCompanyName = trim((string) ($companyNameFromRow ?: ($companyMap[$marketNumberFromFile] ?? $companyNameFromFile))) ?: 'Inconnue';

                    $company = Company::firstOrCreate(['name' => $effectiveCompanyName]);
                    $marketReference = $effectiveMarketNumber
                        ? ($fileBaseName . '|' . $effectiveMarketNumber)
                        : $fileBaseName;
                    $market = $this->resolveMarket($marketReference, $effectiveMarketNumber, (int) $company->id, basename($filePath));

                    // Intentionally do not create/update equipments from market imports.
                    // Market import should only register market/company metadata.
                    $rowSignature = $this->buildImportRowSignature(
                        basename($filePath),
                        $title,
                        $i,
                        $effectiveMarketNumber,
                        $designation,
                        $lotNumber,
                        $article
                    );

                    $linePayload = [
                        'market_id' => (int) ($market?->id ?? 0),
                        'market_object' => $marketObject ? mb_substr($marketObject, 0, 255) : null,
                        'lot_number' => $lotNumber ? mb_substr($lotNumber, 0, 120) : null,
                        'article' => $article ? mb_substr($article, 0, 150) : null,
                        'designation' => mb_substr($designation, 0, 255),
                        'quantity' => $quantity,
                        'delivery_status' => $deliveryStatus ? mb_substr($deliveryStatus, 0, 80) : null,
                        'delivery_date' => $this->nullableDate($deliveryDateRaw),
                        'market_complaint_status' => $complaintStatus ? mb_substr($complaintStatus, 0, 80) : null,
                        'market_complaint_date' => $this->nullableDate($complaintDateRaw),
                        'observations' => $observations,
                        'recommendations' => $recommendations,
                        'source_file_name' => basename($filePath),
                        'source_sheet_name' => mb_substr($title, 0, 120),
                        'source_row_index' => $i + 1,
                    ];

                    if (!$market?->id) {
                        $skipped++;
                        continue;
                    }

                    DB::transaction(function () use ($rowSignature, $linePayload, &$created, &$updated) {
                        $existing = MarketEquipmentImportLine::where('row_signature', $rowSignature)->first();

                        MarketEquipmentImportLine::updateOrCreate(
                            ['row_signature' => $rowSignature],
                            $linePayload
                        );

                        if ($existing) {
                            $updated++;
                        } else {
                            $created++;
                        }
                    });

                    $touchedMarketIds[] = (int) $market->id;
                }
            }
        }

        $this->synchronizeDeliveryStatusByMarkets(array_values(array_unique($touchedMarketIds)));

        $this->info("Import terminé. Créés: {$created}, Mis à jour: {$updated}, Ignorés: {$skipped}, Fichiers ignorés: {$skippedFiles}");
        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function resolveFiles(): array
    {
        $files = (array) $this->option('file');
        if (!empty($files)) {
            return array_values(array_filter(array_map(function (string $path) {
                $resolved = is_file($path) ? $path : base_path($path);
                return is_file($resolved) ? $resolved : null;
            }, $files)));
        }

        $dir = $this->option('dir');
        if (is_string($dir) && trim($dir) !== '') {
            $resolvedDir = is_dir($dir) ? $dir : base_path($dir);
            if (is_dir($resolvedDir)) {
                $fromDir = glob(rtrim($resolvedDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.xlsx') ?: [];
                return array_values(array_filter($fromDir, fn (string $file) => is_file($file)
                    && !str_starts_with(basename($file), '~$')
                    && $this->isMarketWorkbookName(pathinfo($file, PATHINFO_FILENAME))
                ));
            }
        }

        $preferred = base_path("Rectification des N° d'inventaire.xlsx");
        if (is_file($preferred)) {
            return [$preferred];
        }

        $all = glob(base_path('*.xlsx')) ?: [];
        return array_values(array_filter($all, fn (string $file) => is_file($file)
            && !str_starts_with(basename($file), '~$')
            && $this->isMarketWorkbookName(pathinfo($file, PATHINFO_FILENAME))
        ));
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     * @return array{0:int|null,1:array<string,int|null>}
     */
    private function detectColumns(array $rows): array
    {
        for ($i = 0; $i < min(20, count($rows)); $i++) {
            $line = $rows[$i] ?? [];
            $inventory = null;
            $designation = null;
            $serial = null;
            $service = null;
            $marketNumber = null;
            $companyName = null;
            $marketObject = null;
            $lotNumber = null;
            $article = null;
            $quantity = null;
            $deliveryReceptionProvisoire = null;
            $deliveryStatus = null;
            $deliveryDate = null;
            $complaintStatus = null;
            $complaintDate = null;
            $observations = null;
            $recommendations = null;
            $annualMaintenanceAmountHt = null;

            $statusColumns = [];
            $dateColumns = [];

            foreach ($line as $index => $value) {
                $normalized = $this->normalizeHeader((string) $value);

                if ($inventory === null && str_contains($normalized, 'inventaire')) {
                    $inventory = $index;
                }

                if ($designation === null && (
                    str_contains($normalized, 'designation')
                    || str_contains($normalized, 'equipement')
                    || str_contains($normalized, 'equipment')
                )) {
                    $designation = $index;
                }

                if ($serial === null && (str_contains($normalized, 'serie') || str_contains($normalized, 'sn'))) {
                    $serial = $index;
                }

                if ($service === null && str_contains($normalized, 'service')) {
                    $service = $index;
                }

                if ($marketNumber === null && (
                    str_contains($normalized, 'n du marche')
                    || str_contains($normalized, 'numero du marche')
                    || str_contains($normalized, 'no du marche')
                )) {
                    $marketNumber = $index;
                }

                if ($companyName === null && str_contains($normalized, 'societe')) {
                    $companyName = $index;
                }

                if ($marketObject === null && (str_contains($normalized, 'objet') && str_contains($normalized, 'marche'))) {
                    $marketObject = $index;
                }

                if ($lotNumber === null && str_contains($normalized, 'lot')) {
                    $lotNumber = $index;
                }

                if ($article === null && (str_contains($normalized, 'art n') || str_contains($normalized, 'article'))) {
                    $article = $index;
                }

                if ($quantity === null && (str_contains($normalized, 'quantite') || str_contains($normalized, 'qte'))) {
                    $quantity = $index;
                }

                $isStatusHeader = str_contains($normalized, 'status')
                    || str_contains($normalized, 'statut')
                    || str_contains($normalized, 'etat');

                $isDeliveryHeader = str_contains($normalized, 'livraison') || str_contains($normalized, 'livre');
                $isComplaintHeader = str_contains($normalized, 'reclamation') || str_contains($normalized, 'reclam');

                if ($isStatusHeader && !in_array($index, $statusColumns, true)) {
                    $statusColumns[] = $index;
                }

                if (str_contains($normalized, 'date') && !in_array($index, $dateColumns, true)) {
                    $dateColumns[] = $index;
                }

                if ($deliveryStatus === null && $isStatusHeader && $isDeliveryHeader) {
                    $deliveryStatus = $index;
                }

                if ($complaintStatus === null && $isStatusHeader && $isComplaintHeader) {
                    $complaintStatus = $index;
                }

                if ($deliveryDate === null && str_contains($normalized, 'date') && $isDeliveryHeader) {
                    $deliveryDate = $index;
                }

                if ($complaintDate === null && str_contains($normalized, 'date') && $isComplaintHeader) {
                    $complaintDate = $index;
                }

                if ($deliveryReceptionProvisoire === null && (
                    str_contains($normalized, 'livraison')
                    || (str_contains($normalized, 'reception') && str_contains($normalized, 'provis'))
                )) {
                    $deliveryReceptionProvisoire = $index;
                }

                if ($observations === null && str_contains($normalized, 'observation')) {
                    $observations = $index;
                }

                if ($recommendations === null && (str_contains($normalized, 'recommandation') || str_contains($normalized, 'recommendation'))) {
                    $recommendations = $index;
                }

                if ($annualMaintenanceAmountHt === null && (
                    str_contains($normalized, 'montant')
                    && str_contains($normalized, 'maintenance')
                    && str_contains($normalized, 'ht')
                )) {
                    $annualMaintenanceAmountHt = $index;
                }
            }

            if ($deliveryStatus === null && isset($statusColumns[0])) {
                $deliveryStatus = $statusColumns[0];
            }
            if ($complaintStatus === null && isset($statusColumns[1])) {
                $complaintStatus = $statusColumns[1];
            }

            if ($deliveryDate === null && isset($dateColumns[0])) {
                $deliveryDate = $dateColumns[0];
            }
            if ($complaintDate === null && isset($dateColumns[1])) {
                $complaintDate = $dateColumns[1];
            }

            if ($quantity !== null) {
                $next1 = $quantity + 1;
                $next2 = $quantity + 2;
                $next3 = $quantity + 3;
                $next4 = $quantity + 4;

                if ($deliveryStatus === null && array_key_exists($next1, $line)) {
                    $deliveryStatus = $next1;
                }

                if ($deliveryDate === null && array_key_exists($next2, $line)) {
                    $deliveryDate = $next2;
                }

                if ($complaintStatus === null && array_key_exists($next3, $line)) {
                    $complaintStatus = $next3;
                }

                if ($complaintDate === null && array_key_exists($next4, $line)) {
                    $complaintDate = $next4;
                }
            }

            if ($designation !== null) {
                return [$i, [
                    'inventory' => $inventory,
                    'designation' => $designation,
                    'serial' => $serial,
                    'service' => $service,
                    'market_number' => $marketNumber,
                    'company_name' => $companyName,
                    'market_object' => $marketObject,
                    'lot_number' => $lotNumber,
                    'article' => $article,
                    'quantity' => $quantity,
                    'delivery_reception_provisoire' => $deliveryReceptionProvisoire,
                    'delivery_status' => $deliveryStatus,
                    'delivery_date' => $deliveryDate,
                    'complaint_status' => $complaintStatus,
                    'complaint_date' => $complaintDate,
                    'observations' => $observations,
                    'recommendations' => $recommendations,
                    'annual_maintenance_amount_ht' => $annualMaintenanceAmountHt,
                ]];
            }
        }

        return [null, []];
    }

    private function normalizeHeader(string $header): string
    {
        $header = mb_strtolower(trim($header));
        $header = str_replace(['°', "'", '’'], ['', '', ''], $header);
        $header = strtr($header, [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ô' => 'o', 'ö' => 'o',
            'î' => 'i', 'ï' => 'i',
            'ç' => 'c',
        ]);
        $header = preg_replace('/\s+/', ' ', $header) ?? $header;

        return trim($header);
    }

    private function readCell(array $row, ?int $index): ?string
    {
        if ($index === null || !array_key_exists($index, $row)) {
            return null;
        }

        $value = trim((string) $row[$index]);
        return $value === '' ? null : $value;
    }

    private function nullableNumber(?string $value): ?float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $normalized = str_replace(["\u{00A0}", ' '], '', $raw);
        $normalized = str_replace(',', '.', $normalized);
        $normalized = preg_replace('/[^0-9.\-]/', '', $normalized) ?? '';

        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function nullableDate(?string $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $raw);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {
            }
        }

        foreach (['Y-m-d', 'Y/m/d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y', 'd.m.Y', 'd/m/y', 'm/d/y', 'd-m-y', 'm-d-y', 'd.m.y'] as $format) {
            $dt = \DateTime::createFromFormat($format, $raw);
            if ($dt !== false) {
                return $dt->format('Y-m-d');
            }
        }

        $timestamp = strtotime($raw);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function looksLikeInventory(string $value): bool
    {
        $normalized = trim($value);

        if ($normalized === '') {
            return false;
        }

        return preg_match('/^(?=.*\d)[A-Z0-9\-\/]+$/i', $normalized) === 1;
    }

    private function normalizeInventory(?string $value): ?string
    {
        $normalized = strtoupper(trim((string) $value));
        if ($normalized === '') {
            return null;
        }

        $normalized = preg_replace('/\s+/', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/[^A-Z0-9\-\/]/', '', $normalized) ?? $normalized;

        return $normalized === '' ? null : mb_substr($normalized, 0, 80);
    }

    private function buildSyntheticInventory(string $filePath, string $sheetTitle, int $rowIndex, string $designation, ?string $lotNumber, ?string $article): string
    {
        $signature = implode('|', [
            basename($filePath),
            $sheetTitle,
            (string) $rowIndex,
            trim($designation),
            trim((string) $lotNumber),
            trim((string) $article),
        ]);

        return 'MKT-' . strtoupper(substr(sha1($signature), 0, 16));
    }

    private function buildImportRowSignature(
        string $sourceFile,
        string $sheetTitle,
        int $rowIndex,
        ?string $marketNumber,
        string $designation,
        ?string $lotNumber,
        ?string $article
    ): string {
        $signature = implode('|', [
            trim($sourceFile),
            trim($sheetTitle),
            (string) $rowIndex,
            trim((string) $marketNumber),
            trim($designation),
            trim((string) $lotNumber),
            trim((string) $article),
        ]);

        return sha1($signature);
    }

    private function resolveMarket(string $reference, ?string $marketNumber, int $companyId, string $sourceFileName): ?Market
    {
        $year = null;
        if ($marketNumber && preg_match('/\/([0-9]{4})$/', $marketNumber, $matches) === 1) {
            $year = $matches[1];
        }

        $date = $year ? $year . '-01-01' : now()->toDateString();

        return Market::updateOrCreate(
            ['reference' => $reference],
            [
                'market_number' => $marketNumber,
                'market_date' => $date,
                'company_id' => $companyId,
                'source_file_name' => $sourceFileName,
            ]
        );
    }

    /**
     * @return array<string, string>
     */
    private function buildCompanyMapFromColorCode($spreadsheet): array
    {
        $map = [];
        $sheet = $spreadsheet->getSheetByName('Color code');

        if (!$sheet) {
            return $map;
        }

        $rows = $sheet->toArray(null, true, true, false);
        foreach ($rows as $row) {
            $rawReference = trim((string) ($row[0] ?? ''));
            $company = trim((string) ($row[1] ?? ''));

            if ($rawReference === '' || $company === '' || mb_strtolower($rawReference) === 'marche') {
                continue;
            }

            $normalized = $this->normalizeMarketReference($rawReference);
            if ($normalized) {
                $map[$normalized] = $company;
            }
        }

        return $map;
    }

    private function normalizeMarketReference(string $value): ?string
    {
        if (preg_match('/^0*([0-9]+)\s*\/\s*([0-9]{4})$/', $value, $matches) !== 1) {
            return null;
        }

        return ((int) $matches[1]) . '/' . $matches[2];
    }

    private function extractMarketNumberFromText(string $value): ?string
    {
        if (preg_match('/M\s*0*([0-9]+)-\s*([0-9]{4})/i', $value, $matches) !== 1) {
            return null;
        }

        return ((int) $matches[1]) . '/' . $matches[2];
    }

    private function extractYearFromText(string $value): ?int
    {
        if (preg_match('/\b(20\d{2})\b/', $value, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    private function normalizeMarketNumberWithYear(?string $marketNumberRaw, ?string $marketNumberFromFile, ?int $fileYear): ?string
    {
        $candidate = trim((string) ($marketNumberRaw ?? ''));

        if ($candidate === '' && $marketNumberFromFile) {
            $candidate = trim((string) $marketNumberFromFile);
        }

        if ($candidate === '') {
            return null;
        }

        if (preg_match('/^0*([0-9]+)\s*\/\s*([0-9]{4})$/', $candidate, $matches) === 1) {
            return ((int) $matches[1]) . '/' . $matches[2];
        }

        if (preg_match('/^M\s*0*([0-9]+)\s*[-\/]\s*([0-9]{4})$/i', $candidate, $matches) === 1) {
            return ((int) $matches[1]) . '/' . $matches[2];
        }

        if (preg_match('/^0*([0-9]+)$/', $candidate, $matches) === 1) {
            if ($fileYear) {
                return ((int) $matches[1]) . '/' . $fileYear;
            }

            return (string) ((int) $matches[1]);
        }

        return $candidate;
    }

    private function isMarketWorkbookName(string $value): bool
    {
        return preg_match('/^M\s*\d+-\d{4}/i', trim($value)) === 1;
    }

    private function extractZoneFromText(string $value): ?string
    {
        if (preg_match('/\b(L\d+|LU|AU)\b/i', strtoupper($value), $matches) !== 1) {
            return null;
        }

        return strtoupper($matches[1]);
    }

    private function replaceExistingImportedData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            if (Schema::hasTable('market_equipment_import_lines')) {
                DB::table('market_equipment_import_lines')->delete();
            }
            DB::table('complaints')->delete();
            DB::table('interventions')->delete();
            DB::table('equipment_verification_logs')->delete();
            DB::table('inventory_number_rectifications')->delete();
            DB::table('equipment_verifications')->delete();
            DB::table('equipments')->delete();
            DB::table('markets')->delete();
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Rule: for each market, if any line has delivery status = Oui, set Oui on all lines;
     * otherwise keep delivery status blank on all lines.
     *
     * @param array<int, int> $marketIds
     */
    private function synchronizeDeliveryStatusByMarkets(array $marketIds): void
    {
        if (empty($marketIds)) {
            return;
        }

        foreach ($marketIds as $marketId) {
            $baseQuery = MarketEquipmentImportLine::query()->where('market_id', (int) $marketId);

            $hasOui = (clone $baseQuery)
                ->whereRaw('LOWER(TRIM(COALESCE(delivery_status, ""))) = "oui"')
                ->exists();

            if ($hasOui) {
                (clone $baseQuery)->update(['delivery_status' => 'Oui']);
            } else {
                (clone $baseQuery)->update(['delivery_status' => null]);
            }
        }
    }
}
