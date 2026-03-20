<?php

namespace App\Console\Commands;

use App\Models\Equipment;
use App\Models\Hospital;
use App\Models\Market;
use App\Models\Room;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ImportEquipmentsExcelFile extends Command
{
    protected $signature = 'equipements:import-file {--file=} {--replace-existing=1}';

    protected $description = 'Import equipments from Excel file with selected columns';

    private array $serviceContextCache = [];

    private array $marketCache = [];

    public function handle(): int
    {
        @ini_set('max_execution_time', '0');
        @set_time_limit(0);

        $filePath = (string) $this->option('file');
        $replaceExisting = (bool) ((int) $this->option('replace-existing'));

        if ($filePath === '' || !is_file($filePath)) {
            $this->line(json_encode([
                'ok' => false,
                'message' => 'Fichier introuvable.',
            ], JSON_UNESCAPED_UNICODE));

            return 1;
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            if (!$spreadsheet instanceof Spreadsheet || count($spreadsheet->getAllSheets()) === 0) {
                $this->line(json_encode([
                    'ok' => false,
                    'message' => 'Le fichier Excel est vide.',
                ], JSON_UNESCAPED_UNICODE));

                return 1;
            }

            $created = 0;
            $updated = 0;
            $skipped = 0;
            $deleted = 0;
            $importedRows = 0;

            if ($replaceExisting) {
                $tables = [
                    'equipment_verification_logs',
                    'equipment_verifications',
                    'inventory_number_rectifications',
                    'maintenance_reports',
                    'preventive_maintenances',
                    'interventions',
                    'complaints',
                ];

                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                try {
                    foreach ($tables as $table) {
                        if (Schema::hasTable($table)) {
                            DB::table($table)->delete();
                        }
                    }

                    $deleted = Equipment::query()->count();
                    Equipment::query()->delete();
                } finally {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                }
            }

            $hospital = Hospital::query()->first()
                ?? Hospital::query()->create([
                    'code' => 'HSP',
                    'name' => 'Hôpital Principal',
                ]);

            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                    $sheetTitle = $sheet->getTitle();
                    $layout = $this->resolveSheetLayout($sheet);
                    if ($layout === null) {
                        continue;
                    }

                    $rows = $layout['rows'];
                    $headerRowIndex = $layout['headerRowIndex'];
                    $headerMap = $layout['headerMap'];

                    foreach ($rows as $rowIndex => $row) {
                        if ($rowIndex <= $headerRowIndex) {
                            continue;
                        }

                        $mapped = [];
                        foreach ($headerMap as $columnKey => $targetKey) {
                            $mapped[$targetKey] = isset($row[$columnKey]) ? trim((string) $row[$columnKey]) : null;
                        }

                        $inventory = trim((string) ($mapped['inventory_number_current'] ?? ''));
                        $designation = trim((string) ($mapped['designation'] ?? ''));
                        $serial = trim((string) ($mapped['serial_number'] ?? ''));

                        if ($inventory === '' && $designation === '') {
                            continue;
                        }

                        if ($inventory === '') {
                            if ($serial !== '') {
                                $inventory = 'SER-' . preg_replace('/\s+/', '', $serial) . '-' . $rowIndex;
                            } elseif ($designation !== '') {
                                $inventory = 'AUTO-' . strtoupper(substr(sha1($sheetTitle . '|' . $rowIndex . '|' . $designation), 0, 12));
                            } else {
                                $skipped++;
                                continue;
                            }
                        }

                        $payload = [
                            'designation' => $designation !== '' ? $designation : 'Équipement sans désignation',
                            'serial_number' => $this->nullableTrim($mapped['serial_number'] ?? null),
                            'unit_name' => $this->nullableTrim($mapped['unit_name'] ?? null),
                            'sector_name' => $this->nullableTrim($mapped['sector_name'] ?? null),
                            'sector_description' => $this->nullableTrim($mapped['sector_description'] ?? null),
                            'brand_name' => $this->nullableTrim($mapped['brand_name'] ?? null),
                            'model_name' => $this->nullableTrim($mapped['model_name'] ?? null),
                            'market_label' => $this->nullableTrim($mapped['market_label'] ?? null),
                            'lot_number' => $this->nullableTrim($mapped['lot_number'] ?? null),
                            'article' => $this->nullableTrim($mapped['article'] ?? null),
                            'date_reception_provisoire' => $this->nullableDate($mapped['date_reception_provisoire'] ?? null),
                            'duree_garantie' => $this->nullableTrim($mapped['duree_garantie'] ?? null),
                            'date_reception_definitive' => $this->nullableDate($mapped['date_reception_definitive'] ?? null),
                            'service_name' => $this->nullableTrim($mapped['unit_name'] ?? null),
                            'exact_location' => $this->nullableTrim($mapped['sector_description'] ?? null),
                            'operational_status' => 'fonctionnel',
                        ];

                        $serviceContext = $this->resolveServiceContext(
                            $payload['unit_name'] ?? null,
                            $payload['sector_name'] ?? null
                        );

                        $payload['zone_id'] = $serviceContext['zone_id'];
                        $payload['service_id'] = $serviceContext['service_id'];
                        $payload['room_id'] = $serviceContext['room_id'];

                        $market = $this->resolveMarketFromLabel($payload['market_label'] ?? null);
                        $payload['market_id'] = $market?->id;
                        $payload['company_id'] = $market?->company_id;

                        $existing = Equipment::query()->where('inventory_number_current', $inventory)->first();
                        if ($existing) {
                            $existing->update($payload);
                            $updated++;
                        } else {
                            Equipment::query()->create(array_merge([
                                'inventory_number_current' => $inventory,
                                'hospital_id' => $hospital->id,
                            ], $payload));
                            $created++;
                        }

                        $importedRows++;
                    }
                }

            if ($importedRows === 0) {
                $this->line(json_encode([
                    'ok' => false,
                    'message' => 'Aucune ligne importable trouvée. Vérifiez les en-têtes et la feuille contenant les données.',
                ], JSON_UNESCAPED_UNICODE));

                return 1;
            }

            $this->line(json_encode([
                'ok' => true,
                'deleted' => $deleted,
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
            ], JSON_UNESCAPED_UNICODE));

            return 0;
        } catch (Throwable $e) {
            $this->line(json_encode([
                'ok' => false,
                'message' => 'Erreur import Excel: ' . $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE));

            return 1;
        }
    }

    private function normalizeExcelHeader(string $header): string
    {
        $normalized = strtolower(trim($header));
        $normalized = str_replace(['é', 'è', 'ê', 'ë'], 'e', $normalized);
        $normalized = str_replace(['à', 'â', 'ä'], 'a', $normalized);
        $normalized = str_replace(['î', 'ï'], 'i', $normalized);
        $normalized = str_replace(['ô', 'ö'], 'o', $normalized);
        $normalized = str_replace(['ù', 'û', 'ü'], 'u', $normalized);
        $normalized = str_replace(['ç'], 'c', $normalized);
        $normalized = str_replace(["\n", "\r", "\t"], ' ', $normalized);
        $normalized = preg_replace('/[^a-z0-9 ]+/u', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return $normalized;
    }

    private function nullableTrim($value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function nullableDate($value): ?string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        if (is_numeric($trimmed)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $trimmed);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {
            }
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];
        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $trimmed);
            if ($dt !== false) {
                return $dt->format('Y-m-d');
            }
        }

        $timestamp = strtotime($trimmed);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function resolveMarketFromLabel(?string $label): ?Market
    {
        $value = trim((string) $label);

        if ($value === '') {
            return null;
        }

        $cacheKey = mb_strtolower($value);
        if (array_key_exists($cacheKey, $this->marketCache)) {
            return $this->marketCache[$cacheKey];
        }

        $market = Market::query()
            ->where('market_number', $value)
            ->orWhere('reference', $value)
            ->first();

        if (!$market && preg_match('/\b\d{1,3}\/\d{4}\b/', $value, $match)) {
            $market = Market::query()->where('market_number', $match[0])->first();
        }

        if (!$market && preg_match('/\bM\d{1,3}-\d{4}\b/i', $value, $match)) {
            $market = Market::query()->where('reference', 'like', strtoupper($match[0]) . '%')->first();
        }

        $this->marketCache[$cacheKey] = $market;

        return $market;
    }

    private function resolveSheetLayout(Worksheet $sheet): ?array
    {
        $rows = $sheet->toArray(null, true, true, true);
        if (empty($rows)) {
            return null;
        }

        $best = null;
        foreach (array_slice($rows, 0, 60, true) as $rowIndex => $row) {
            $tempMap = [];
            foreach ($row as $columnKey => $headerValue) {
                $normalizedHeader = $this->normalizeExcelHeader((string) $headerValue);
                $resolvedField = $this->resolveFieldFromHeader($normalizedHeader);

                if ($resolvedField !== null) {
                    $tempMap[$columnKey] = $resolvedField;
                }
            }

            if (!in_array('inventory_number_current', $tempMap, true)) {
                continue;
            }

            $score = count(array_unique(array_values($tempMap)));
            if ($score < 3) {
                continue;
            }

            $importableRows = 0;
            foreach ($rows as $dataRowIndex => $dataRow) {
                if ($dataRowIndex <= $rowIndex) {
                    continue;
                }

                $inventory = '';
                foreach ($tempMap as $columnKey => $targetKey) {
                    if ($targetKey === 'inventory_number_current') {
                        $inventory = trim((string) ($dataRow[$columnKey] ?? ''));
                        break;
                    }
                }

                if ($inventory !== '') {
                    $importableRows++;
                }
            }

            if ($importableRows === 0) {
                continue;
            }

            if (
                $best === null
                || $importableRows > $best['importableRows']
                || ($importableRows === $best['importableRows'] && $score > $best['score'])
            ) {
                $best = [
                    'rows' => $rows,
                    'headerRowIndex' => $rowIndex,
                    'headerMap' => $tempMap,
                    'score' => $score,
                    'importableRows' => $importableRows,
                ];
            }
        }

        if ($best === null) {
            return null;
        }

        return [
            'rows' => $best['rows'],
            'headerRowIndex' => $best['headerRowIndex'],
            'headerMap' => $best['headerMap'],
        ];
    }

    private function resolveFieldFromHeader(string $header): ?string
    {
        if ($header === '') {
            return null;
        }

        if (str_contains($header, 'invent') || str_contains($header, 'code bar') || str_contains($header, 'barcode')) {
            return 'inventory_number_current';
        }

        if ((str_contains($header, 'description') && str_contains($header, 'equip')) || str_contains($header, 'designation')) {
            return 'designation';
        }

        if (str_contains($header, 'serie')) {
            return 'serial_number';
        }

        if (str_contains($header, 'description') && (str_contains($header, 'secteur') || str_contains($header, 'section'))) {
            return 'sector_description';
        }

        if (str_contains($header, 'secteur') || str_contains($header, 'section')) {
            return 'sector_name';
        }

        if (str_contains($header, 'unite') || str_contains($header, 'service')) {
            return 'unit_name';
        }

        if (str_contains($header, 'marque') || str_contains($header, 'fabricant') || str_contains($header, 'constructeur')) {
            return 'brand_name';
        }

        if (str_contains($header, 'modele') || str_contains($header, 'model')) {
            return 'model_name';
        }

        if (str_contains($header, 'marche') || str_contains($header, 'contract')) {
            return 'market_label';
        }

        if (str_contains($header, 'lot')) {
            return 'lot_number';
        }

        if (str_contains($header, 'article')) {
            return 'article';
        }

        if (str_contains($header, 'garantie') || str_contains($header, 'warranty')) {
            return 'duree_garantie';
        }

        if ((str_contains($header, 'reception') || str_contains($header, 'reception')) && str_contains($header, 'provis')) {
            return 'date_reception_provisoire';
        }

        if ((str_contains($header, 'reception') || str_contains($header, 'reception')) && str_contains($header, 'definit')) {
            return 'date_reception_definitive';
        }

        $aliases = [
            'inventory_number_current' => [
                'code a barres',
                'code barres',
                'code barre',
                'barcode',
                'n inventaire',
                'numero inventaire',
                'num inventaire',
                'inventaire',
                'reference inventaire',
            ],
            'designation' => [
                'description de lequipement',
                'description equipement',
                'designation',
                'equipement',
                'description',
            ],
            'serial_number' => [
                'n de serie',
                'numero de serie',
                'num serie',
                'serial number',
                'serie',
            ],
            'unit_name' => [
                'unite',
                'service',
            ],
            'sector_name' => [
                'secteur',
                'section',
            ],
            'sector_description' => [
                'description secteur',
                'description section',
                'localisation',
                'emplacement',
            ],
            'brand_name' => [
                'marque',
                'brand',
                'constructeur',
                'fabricant',
            ],
            'model_name' => [
                'modele',
                'model',
                'type',
            ],
            'market_label' => [
                'marche',
                'marche public',
                'contract',
                'bon de commande',
            ],
            'lot_number' => [
                'lot',
                'n lot',
                'numero lot',
            ],
            'article' => [
                'article',
                'code article',
                'reference article',
            ],
            'duree_garantie' => [
                'duree garantie',
                'garantie',
                'warranty',
            ],
            'date_reception_provisoire' => [
                'date reception provisoire',
                'reception provisoire',
            ],
            'date_reception_definitive' => [
                'date reception definitive',
                'reception definitive',
            ],
        ];

        foreach ($aliases as $field => $patterns) {
            foreach ($patterns as $pattern) {
                if ($header === $pattern || str_contains($header, $pattern)) {
                    return $field;
                }
            }
        }

        return null;
    }

    private function resolveServiceContext(?string $unitName, ?string $sectorName): array
    {
        $unitName = trim((string) $unitName);
        $sectorName = trim((string) $sectorName);

        $cacheKey = mb_strtolower($unitName . '|' . $sectorName);
        if (array_key_exists($cacheKey, $this->serviceContextCache)) {
            return $this->serviceContextCache[$cacheKey];
        }

        if ($unitName === '') {
            $result = [
                'zone_id' => null,
                'service_id' => null,
                'room_id' => null,
            ];

            $this->serviceContextCache[$cacheKey] = $result;

            return $result;
        }

        $service = Service::query()
            ->whereRaw('LOWER(name) = LOWER(?)', [$unitName])
            ->orWhereRaw('LOWER(code) = LOWER(?)', [$unitName])
            ->first();

        if (!$service) {
            $result = [
                'zone_id' => null,
                'service_id' => null,
                'room_id' => null,
            ];

            $this->serviceContextCache[$cacheKey] = $result;

            return $result;
        }

        $roomId = null;
        if ($sectorName !== '') {
            $roomId = Room::query()
                ->where('service_id', $service->id)
                ->whereRaw('LOWER(room_number) = LOWER(?)', [$sectorName])
                ->value('id');
        }

        $result = [
            'zone_id' => $service->zone_id,
            'service_id' => $service->id,
            'room_id' => $roomId,
        ];

        $this->serviceContextCache[$cacheKey] = $result;

        return $result;
    }
}
