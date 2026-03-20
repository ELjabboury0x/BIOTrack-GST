<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportBilanActivites extends Command
{
    protected $signature = 'gmao:import-bilan-activites {--dir=Bilan_Activites : Dossier des fichiers Excel} {--truncate : Vider les tables bilan avant import} {--force-importer= : Forcer un importeur (corrective|preventive|training|contracts|final_reception)}';

    protected $description = 'Importe les fichiers Excel du dossier Bilan_Activites dans des tables dédiées';

    public function handle(): int
    {
        ini_set('memory_limit', '1024M');

        $dirOption = trim((string) $this->option('dir'));
        $dirPath = is_dir($dirOption) ? $dirOption : base_path($dirOption);

        if (!is_dir($dirPath)) {
            $this->error('Dossier introuvable: ' . $dirPath);
            return self::FAILURE;
        }

        $xlsxFiles = glob(rtrim($dirPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.xlsx') ?: [];
        $xlsFiles = glob(rtrim($dirPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.xls') ?: [];
        $files = array_merge($xlsxFiles, $xlsFiles);
        $files = array_values(array_unique(array_filter($files, function (string $file) {
            return is_file($file) && !str_starts_with(basename($file), '~$');
        })));

        if (empty($files)) {
            $this->warn('Aucun fichier Excel (.xlsx/.xls) trouvé dans ' . $dirPath);
            return self::SUCCESS;
        }

        $forcedImporter = $this->resolveForcedImporter((string) $this->option('force-importer'));

        if ((bool) $this->option('truncate')) {
            $this->truncateBilanTables();
        }

        $summary = [
            'files' => 0,
            'rows_imported' => 0,
            'rows_skipped' => 0,
            'unknown_files' => 0,
        ];

        foreach ($files as $filePath) {
            $summary['files']++;
            $fileName = basename($filePath);
            $fileKey = $this->normalizeText(pathinfo($fileName, PATHINFO_FILENAME));

            $this->line('Traitement: ' . $fileName);

            try {
                $spreadsheet = IOFactory::load($filePath);
            } catch (\Throwable $e) {
                $summary['rows_skipped']++;
                $this->warn('Lecture impossible: ' . $fileName . ' (' . $e->getMessage() . ')');
                continue;
            }

            $importer = $forcedImporter ?? $this->resolveImporter($fileName, $fileKey);
            if ($importer === null) {
                $summary['unknown_files']++;
                $this->warn('Fichier non reconnu (ignoré): ' . $fileName);
                continue;
            }

            $sheet = $spreadsheet->getSheet(0);
            [$imported, $skipped] = $this->{$importer}($filePath, $sheet);
            $summary['rows_imported'] += $imported;
            $summary['rows_skipped'] += $skipped;

            $this->info("  Importé: {$imported}, Ignoré: {$skipped}");
        }

        $this->newLine();
        $this->info('Import Bilan Activités terminé.');
        $this->line('Fichiers traités: ' . $summary['files']);
        $this->line('Lignes importées: ' . $summary['rows_imported']);
        $this->line('Lignes ignorées: ' . $summary['rows_skipped']);
        $this->line('Fichiers non reconnus: ' . $summary['unknown_files']);

        return self::SUCCESS;
    }

    private function truncateBilanTables(): void
    {
        $tables = [
            'bilan_maintenance_correctives',
            'bilan_maintenance_preventives',
            'bilan_maintenance_contracts',
            'bilan_technical_trainings',
            'bilan_final_receptions',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function resolveImporter(string $fileName, string $fileKey): ?string
    {
        $raw = mb_strtolower($fileName);

        if ((str_contains($raw, 'maintenance') && str_contains($raw, 'correct')) || (str_contains($fileKey, 'maintenance') && str_contains($fileKey, 'correct'))) {
            return 'importCorrective';
        }

        if ((str_contains($raw, 'maintenance') && (str_contains($raw, 'prévent') || str_contains($raw, 'prevent'))) || (str_contains($fileKey, 'maintenance') && str_contains($fileKey, 'prevent'))) {
            return 'importPreventive';
        }

        if ((str_contains($raw, 'formation') && str_contains($raw, 'technique')) || (str_contains($fileKey, 'formation') && str_contains($fileKey, 'technique'))) {
            return 'importTraining';
        }

        if ((str_contains($raw, 'contrat') && str_contains($raw, 'maintenance')) || (str_contains($fileKey, 'contrat') && str_contains($fileKey, 'maintenance'))) {
            return 'importContracts';
        }

        if ((str_contains($raw, 'réception') && str_contains($raw, 'définitive')) || (str_contains($raw, 'reception') && str_contains($raw, 'definitive')) || (str_contains($fileKey, 'reception') && str_contains($fileKey, 'definitive'))) {
            return 'importFinalReception';
        }

        return null;
    }

    private function resolveForcedImporter(string $value): ?string
    {
        $normalized = $this->normalizeText($value);

        if ($normalized === 'corrective') {
            return 'importCorrective';
        }

        if ($normalized === 'preventive') {
            return 'importPreventive';
        }

        if ($normalized === 'training') {
            return 'importTraining';
        }

        if ($normalized === 'contracts') {
            return 'importContracts';
        }

        if ($normalized === 'final_reception' || $normalized === 'finalreception') {
            return 'importFinalReception';
        }

        return null;
    }

    private function importCorrective(string $filePath, $sheet): array
    {
        $headers = $this->readRow($sheet, 2);
        $map = $this->buildHeaderMap($headers);
        $highestRow = (int) $sheet->getHighestDataRow();

        $imported = 0;
        $skipped = 0;

        for ($r = 3; $r <= $highestRow; $r++) {
            $row = $this->readRow($sheet, $r);
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $payload = [
                'company_name' => $this->valueByAliasesOrIndex($row, $map, ['societe'], 0),
                'equipment_designation' => $this->valueByAliasesOrIndex($row, $map, ['designation de lequipement', 'designation de l equipement'], 1),
                'brand_name' => $this->valueByAliasesOrIndex($row, $map, ['marque'], 2),
                'model_name' => $this->valueByAliasesOrIndex($row, $map, ['modele'], 3),
                'serial_number' => $this->valueByAliasesOrIndex($row, $map, ['n de serie', 'numero de serie'], 4),
                'market_or_contract_ref' => $this->valueByAliasesOrIndex($row, $map, ['n de marche contrat de maintenance', 'n de marche contrat'], 5),
                'failure_details' => $this->valueByAliasesOrIndex($row, $map, ['details de la panne'], 6),
                'observations' => $this->valueByAliasesOrIndex($row, $map, ['observations'], 7),
                'service_names' => $this->valueByAliasesOrIndex($row, $map, ['services'], 8),
                'intervention_date_text' => $this->valueByAliasesOrIndex($row, $map, ['date dintervention'], 9),
            ];

            if (!$payload['company_name'] && !$payload['equipment_designation']) {
                $skipped++;
                continue;
            }

            $this->upsertRow('bilan_maintenance_correctives', $payload, $filePath, $sheet->getTitle(), $r);
            $imported++;
        }

        return [$imported, $skipped];
    }

    private function importPreventive(string $filePath, $sheet): array
    {
        $headers = $this->readRow($sheet, 2);
        $map = $this->buildHeaderMap($headers);
        $highestRow = (int) $sheet->getHighestDataRow();

        $imported = 0;
        $skipped = 0;

        for ($r = 3; $r <= $highestRow; $r++) {
            $row = $this->readRow($sheet, $r);
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $activityRaw = $this->valueByAliases($row, $map, ['activite achevee oui non']);

            $payload = [
                'company_name' => $this->valueByAliasesOrIndex($row, $map, ['societe'], 0),
                'equipment_designation' => $this->valueByAliasesOrIndex($row, $map, ['designation de lequipement', 'designation de l equipement'], 1),
                'brand_name' => $this->valueByAliasesOrIndex($row, $map, ['marque'], 2),
                'model_name' => $this->valueByAliasesOrIndex($row, $map, ['modele'], 3),
                'market_or_contract_ref' => $this->valueByAliasesOrIndex($row, $map, ['n de marche contrat'], 4),
                'serial_number' => $this->valueByAliasesOrIndex($row, $map, ['n de serie', 'numero de serie'], 5),
                'intervention_dates_text' => $this->valueByAliasesOrIndex($row, $map, ['dates dintervention'], 6),
                'intervention_details' => $this->valueByAliasesOrIndex($row, $map, ['details de lintervention'], 7),
                'observations' => $this->valueByAliasesOrIndex($row, $map, ['observations'], 8),
                'service_names' => $this->valueByAliasesOrIndex($row, $map, ['services'], 9),
                'activity_completed' => $this->parseOuiNon($activityRaw),
            ];

            if (!$payload['company_name'] && !$payload['equipment_designation']) {
                $skipped++;
                continue;
            }

            $this->upsertRow('bilan_maintenance_preventives', $payload, $filePath, $sheet->getTitle(), $r);
            $imported++;
        }

        return [$imported, $skipped];
    }

    private function importTraining(string $filePath, $sheet): array
    {
        $headers = $this->readRow($sheet, 2);
        $map = $this->buildHeaderMap($headers);
        $highestRow = (int) $sheet->getHighestDataRow();

        $imported = 0;
        $skipped = 0;

        for ($r = 3; $r <= $highestRow; $r++) {
            $row = $this->readRow($sheet, $r);
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $payload = [
                'company_name' => $this->valueByAliasesOrIndex($row, $map, ['societe'], 0),
                'equipment_designation' => $this->valueByAliasesOrIndex($row, $map, ['designation de lequipement', 'designation de l equipement'], 1),
                'brand_name' => $this->valueByAliasesOrIndex($row, $map, ['marque'], 2),
                'model_name' => $this->valueByAliasesOrIndex($row, $map, ['modele'], 3),
                'market_number' => $this->valueByAliasesOrIndex($row, $map, ['n de marche'], 4),
                'training_date' => $this->valueByAliasesOrIndex($row, $map, ['date de formation'], 5),
                'trained_personnel' => $this->valueByAliasesOrIndex($row, $map, ['personnel forme'], 6),
                'remarks' => $this->valueByAliasesOrIndex($row, $map, ['remarques'], 7),
            ];

            if (!$payload['company_name'] && !$payload['equipment_designation']) {
                $skipped++;
                continue;
            }

            $this->upsertRow('bilan_technical_trainings', $payload, $filePath, $sheet->getTitle(), $r);
            $imported++;
        }

        return [$imported, $skipped];
    }

    private function importContracts(string $filePath, $sheet): array
    {
        $highestRow = (int) $sheet->getHighestDataRow();
        $headerRow = $this->detectContractsHeaderRow($sheet, $highestRow);
        $headers = $this->readRow($sheet, $headerRow);
        $map = $this->buildHeaderMap($headers);

        $imported = 0;
        $skipped = 0;

        for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
            $row = $this->readRow($sheet, $r);
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $payload = [
                'contract_number' => $this->valueByAliases($row, $map, ['n de contrat', 'numero de contrat', 'contrat']),
                'company_name' => $this->valueByAliases($row, $map, ['societe', 'societe / fournisseur']),
                'equipment_designation' => $this->valueByAliases($row, $map, ['designation de lequipement', 'designation de l equipement', 'designation']),
                'brand_name' => $this->valueByAliases($row, $map, ['marque']),
                'model_name' => $this->valueByAliases($row, $map, ['modele']),
                'serial_number' => $this->valueByAliases($row, $map, ['n de serie', 'n serie', 'numero de serie', 'serial']),
                'service_order_date' => $this->valueByAliases($row, $map, ['date dordre de service', 'date ordre de service', 'date d ordre de service']),
                'quarter_1' => $this->valueByAliases($row, $map, ['trimestre 1', 'trimestre1', 't1']),
                'quarter_2' => $this->valueByAliases($row, $map, ['trimestre 2', 'trimestre2', 't2']),
                'quarter_3' => $this->valueByAliases($row, $map, ['trimestre 3', 'trimestre3', 't3']),
                'quarter_4' => $this->valueByAliases($row, $map, ['trimestre 4', 'trimestre4', 't4']),
                'quarter_5' => $this->valueByAliases($row, $map, ['trimestre 5', 'trimestre5', 't5']),
                'quarter_6' => $this->valueByAliases($row, $map, ['trimestre 6', 'trimestre6', 't6']),
                'quarter_7' => $this->valueByAliases($row, $map, ['trimestre 7', 'trimestre7', 't7']),
                'quarter_8' => $this->valueByAliases($row, $map, ['trimestre 8', 'trimestre8', 't8']),
                'service_names' => $this->valueByAliases($row, $map, ['services', 'service(s)', 'service']),
            ];

            if (!$payload['contract_number'] && !$payload['company_name'] && !$payload['equipment_designation']) {
                $skipped++;
                continue;
            }

            $this->upsertRow('bilan_maintenance_contracts', $payload, $filePath, $sheet->getTitle(), $r);
            $imported++;
        }

        return [$imported, $skipped];
    }

    private function detectContractsHeaderRow($sheet, int $highestRow): int
    {
        $maxProbe = min($highestRow, 8);

        for ($rowIndex = 1; $rowIndex <= $maxProbe; $rowIndex++) {
            $row = $this->readRow($sheet, $rowIndex);
            $normalizedRow = array_map(fn ($value) => $this->normalizeText((string) $value), $row);
            $joined = ' ' . implode(' ', array_filter($normalizedRow)) . ' ';

            $hasCompany = str_contains($joined, ' societe ');
            $hasBrandOrModel = str_contains($joined, ' marque ') || str_contains($joined, ' modele ');
            $hasQuarter = str_contains($joined, ' trimestre 1 ') || str_contains($joined, ' trimestre1 ') || str_contains($joined, ' t1 ');

            if ($hasCompany && ($hasBrandOrModel || $hasQuarter)) {
                return $rowIndex;
            }
        }

        // Fallback legacy template header line
        return 4;
    }

    private function importFinalReception(string $filePath, $sheet): array
    {
        $headers = $this->readRow($sheet, 1);
        $map = $this->buildHeaderMap($headers);
        $highestRow = (int) $sheet->getHighestDataRow();

        $imported = 0;
        $skipped = 0;

        for ($r = 2; $r <= $highestRow; $r++) {
            $row = $this->readRow($sheet, $r);
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $payload = [
                'company_name' => $this->valueByAliasesOrIndex($row, $map, ['societe'], 0),
                'market_number' => $this->valueByAliasesOrIndex($row, $map, ['marche'], 1),
                'lot' => $this->valueByAliasesOrIndex($row, $map, ['lot'], 2),
                'article' => $this->valueByAliasesOrIndex($row, $map, ['article'], 3),
                'equipment_designation' => $this->valueByAliasesOrIndex($row, $map, ['designation de lequipement', 'designation de l equipement'], 4),
                'quantity' => $this->valueByAliasesOrIndex($row, $map, ['quantite'], 5),
                'provisional_reception_date' => $this->valueByAliasesOrIndex($row, $map, ['date de la reception provisoire'], 6),
                'final_reception_date' => $this->valueByAliasesOrIndex($row, $map, ['date de la reception definitive'], 7),
                'observations' => $this->valueByAliasesOrIndex($row, $map, ['observations'], 8),
            ];

            if (!$payload['company_name'] && !$payload['market_number'] && !$payload['equipment_designation']) {
                $skipped++;
                continue;
            }

            $this->upsertRow('bilan_final_receptions', $payload, $filePath, $sheet->getTitle(), $r);
            $imported++;
        }

        return [$imported, $skipped];
    }

    private function upsertRow(string $table, array $payload, string $filePath, string $sheet, int $rowIndex): void
    {
        $normalizedPayload = [];
        foreach ($payload as $key => $value) {
            $normalizedPayload[$key] = is_string($value) ? trim($value) : $value;
        }

        $hashData = [
            'table' => $table,
            'sheet' => $sheet,
            'payload' => $normalizedPayload,
        ];
        $rowHash = hash('sha256', json_encode($hashData, JSON_UNESCAPED_UNICODE));

        $now = now();

        DB::table($table)->updateOrInsert(
            ['row_hash' => $rowHash],
            array_merge($normalizedPayload, [
                'source_file' => basename($filePath),
                'source_sheet' => $sheet,
                'source_row' => $rowIndex,
                'updated_at' => $now,
                'created_at' => $now,
            ])
        );
    }

    private function readRow($sheet, int $rowIndex): array
    {
        $highestColumn = $sheet->getHighestColumn();
        $row = $sheet->rangeToArray("A{$rowIndex}:{$highestColumn}{$rowIndex}", null, true, true, false);
        return $row[0] ?? [];
    }

    private function buildHeaderMap(array $headers): array
    {
        $map = [];
        foreach ($headers as $index => $header) {
            $normalized = $this->normalizeText((string) $header);
            if ($normalized !== '') {
                $map[$normalized] = $index;
            }
        }

        return $map;
    }

    private function valueByAliases(array $row, array $headerMap, array $aliases): ?string
    {
        foreach ($aliases as $alias) {
            $normalizedAlias = $this->normalizeText($alias);

            foreach ($headerMap as $header => $index) {
                if ($header === $normalizedAlias || str_contains($header, $normalizedAlias)) {
                    $value = trim((string) ($row[$index] ?? ''));
                    return $value === '' ? null : $value;
                }
            }
        }

        return null;
    }

    private function valueByAliasesOrIndex(array $row, array $headerMap, array $aliases, int $fallbackIndex): ?string
    {
        $value = $this->valueByAliases($row, $headerMap, $aliases);
        if ($value !== null) {
            return $value;
        }

        $fallback = trim((string) ($row[$fallbackIndex] ?? ''));
        return $fallback === '' ? null : $fallback;
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function parseOuiNon(?string $value): ?bool
    {
        $normalized = $this->normalizeText((string) $value);

        if ($normalized === '') {
            return null;
        }

        if (str_contains($normalized, 'oui')) {
            return true;
        }

        if (str_contains($normalized, 'non')) {
            return false;
        }

        return null;
    }

    private function normalizeText(string $value): string
    {
        $value = trim(mb_strtolower($value));

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (is_string($converted)) {
            $value = $converted;
        }

        $value = str_replace(["\r", "\n", "\t"], ' ', $value);
        $value = preg_replace('/[^a-z0-9\s\/\-]/', ' ', $value) ?: '';
        $value = preg_replace('/\s+/', ' ', $value) ?: '';

        return trim($value);
    }
}
