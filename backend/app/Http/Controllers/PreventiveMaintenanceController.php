<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePreventiveMaintenanceRequest;
use App\Http\Requests\UpdatePreventiveMaintenanceRequest;
use App\Models\Equipment;
use App\Models\PreventiveMaintenance;
use App\Support\ServiceAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class PreventiveMaintenanceController extends Controller
{
    public function index()
    {
        $rows = $this->buildActiveMaintenanceRows();

        $historicalRows = collect();

        if (Schema::hasTable('bilan_maintenance_preventives')) {
            $historicalRows = DB::table('bilan_maintenance_preventives')
                ->orderByDesc('created_at')
                ->limit(1000)
                ->get()
                ->map(function ($item) {
                    $historicalCode = 'MP-HIST-' . (string) $item->id;

                    return [
                        'societe' => (string) ($item->company_name ?: '-'),
                        'designation_equipement' => (string) ($item->equipment_designation ?: '-'),
                        'marque' => (string) ($item->brand_name ?: '-'),
                        'modele' => (string) ($item->model_name ?: '-'),
                        'marche_contrat' => (string) ($item->market_or_contract_ref ?: '-'),
                        'numero_serie' => (string) ($item->serial_number ?: '-'),
                        'dates_intervention' => (string) ($item->intervention_dates_text ?: '-'),
                        'details_intervention' => (string) ($item->intervention_details ?: '-'),
                        'observations' => (string) ($item->observations ?: '-'),
                        'services' => (string) ($item->service_names ?: '-'),
                        'activite_achevee' => is_null($item->activity_completed)
                            ? '-'
                            : ((bool) $item->activity_completed ? 'OUI' : 'NON'),
                        'edit_url' => route('maintenance-preventive.create', [
                            'source' => 'historical',
                            'historical_id' => $item->id,
                            'code' => $historicalCode,
                            'equipment_search' => (string) ($item->equipment_designation ?: ''),
                            'periodicity' => 'Annuel',
                            'status' => 'actif',
                        ]),
                    ];
                })
                ->values();
        }

        return view('pages.maintenance-preventive', [
            'maintenanceData' => $rows,
            'historicalMaintenanceData' => $historicalRows,
        ]);
    }

    public function importExcel(Request $request)
    {
        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:51200',
        ]);

        $uploaded = $validated['excel_file'];
        $storedPath = $uploaded->store('imports/maintenance-preventive');
        $absolutePath = storage_path('app/' . $storedPath);

        try {
            $spreadsheet = IOFactory::load($absolutePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (count($rows) === 0) {
                return redirect()
                    ->route('maintenance-preventive')
                    ->with('error', 'Le fichier Excel est vide.');
            }

            [$headerMap, $firstDataRow] = $this->resolveImportHeaderMap($rows);
            $equipmentLookup = $this->buildScopedEquipmentLookup($request->user());

            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($rows as $rowNumber => $row) {
                if (!is_array($row) || $rowNumber < $firstDataRow) {
                    continue;
                }

                $code = trim((string) ($row[$headerMap['code']] ?? ''));
                $equipmentRef = trim((string) ($row[$headerMap['equipment']] ?? ''));
                $periodicityRaw = (string) ($row[$headerMap['periodicity']] ?? '');
                $nextDateRaw = $row[$headerMap['next_date']] ?? null;
                $lastDateRaw = $headerMap['last_date'] !== null
                    ? ($row[$headerMap['last_date']] ?? null)
                    : null;
                $statusRaw = $headerMap['status'] !== null
                    ? (string) ($row[$headerMap['status']] ?? '')
                    : 'actif';

                $isEmptyRow = $code === ''
                    && $equipmentRef === ''
                    && trim($periodicityRaw) === ''
                    && trim((string) $nextDateRaw) === '';

                if ($isEmptyRow) {
                    continue;
                }

                $equipmentId = $this->resolveEquipmentIdFromReference($equipmentRef, $equipmentLookup);
                $periodicity = $this->normalizePeriodicity($periodicityRaw);
                $nextDate = $this->parseExcelDateCell($nextDateRaw);
                $lastDate = $this->parseExcelDateCell($lastDateRaw);
                $status = $this->normalizeStatus($statusRaw);

                if ($code === '' || $equipmentId === null || $periodicity === null || $nextDate === null) {
                    $skipped++;
                    continue;
                }

                $payload = [
                    'equipment_id' => $equipmentId,
                    'periodicity' => $periodicity,
                    'last_maintenance_date' => $lastDate,
                    'next_maintenance_date' => $nextDate,
                    'status' => $status,
                ];

                $existing = PreventiveMaintenance::query()
                    ->where('code', $code)
                    ->first();

                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                    continue;
                }

                PreventiveMaintenance::query()->create(array_merge(['code' => $code], $payload));
                $created++;
            }

            return redirect()
                ->route('maintenance-preventive')
                ->with('success', "Import terminé. {$created} créée(s), {$updated} mise(s) à jour, {$skipped} ignorée(s).");
        } catch (Throwable $e) {
            return redirect()
                ->route('maintenance-preventive')
                ->with('error', 'Import Excel impossible: ' . $e->getMessage());
        } finally {
            if (is_file($absolutePath)) {
                @unlink($absolutePath);
            }
        }
    }

    public function exportExcel(Request $request)
    {
        $rows = $this->buildActiveMaintenanceRows();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Code',
            'Équipement',
            'Périodicité',
            'Dernière maintenance',
            'Prochaine maintenance',
            'Statut',
        ];

        $sheet->fromArray($headers, null, 'A1');

        foreach ($rows as $index => $row) {
            $sheet->fromArray([
                $row['code'] ?? '-',
                $row['equipement'] ?? '-',
                $row['periodicite'] ?? '-',
                $row['dernier'] ?? '-',
                $row['prochain'] ?? '-',
                $row['statut'] ?? '-',
            ], null, 'A' . ($index + 2));
        }

        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $fileName = 'maintenance-preventive-' . now()->format('Y-m-d-His') . '.xlsx';
        $tempFile = storage_path('app/' . uniqid('maintenance_preventive_export_', true) . '.xlsx');

        (new Xlsx($spreadsheet))->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        return response()->view('pages.exports.maintenance-preventive-print', [
            'rows' => $this->buildActiveMaintenanceRows(),
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    public function create(Request $request)
    {
        $equipmentId = (int) $request->query('equipment_id', 0);
        $equipmentSearch = trim((string) $request->query('equipment_search', ''));

        $equipmentQuery = Equipment::query()
            ->select('id', 'inventory_number_current', 'designation')
            ->orderBy('designation');
        ServiceAccess::applyEquipmentScope($equipmentQuery, $request->user());

        if ($equipmentId <= 0 && $equipmentSearch !== '') {
            $match = (clone $equipmentQuery)
                ->select('id')
                ->where(function ($query) use ($equipmentSearch) {
                    $query->where('designation', 'like', '%' . $equipmentSearch . '%')
                        ->orWhere('inventory_number_current', 'like', '%' . $equipmentSearch . '%');
                })
                ->first();

            if ($match) {
                $equipmentId = (int) $match->id;
            }
        }

        $prefill = [
            'source' => (string) $request->query('source', ''),
            'historical_id' => (string) $request->query('historical_id', ''),
            'code' => (string) $request->query('code', ''),
            'equipment_id' => $equipmentId > 0 ? $equipmentId : null,
            'equipment_search' => $equipmentSearch,
            'periodicity' => (string) $request->query('periodicity', 'Mensuel'),
            'status' => (string) $request->query('status', 'actif'),
            'last_maintenance_date' => (string) $request->query('last_maintenance_date', ''),
            'next_maintenance_date' => (string) $request->query('next_maintenance_date', ''),
        ];

        return view('pages.forms.maintenance-create', [
            'equipments' => $equipmentQuery->get(),
            'prefill' => $prefill,
        ]);
    }

    public function store(StorePreventiveMaintenanceRequest $request)
    {
        $equipmentQuery = Equipment::query()->select('id');
        ServiceAccess::applyEquipmentScope($equipmentQuery, $request->user());
        $isAllowed = (clone $equipmentQuery)
            ->where('id', (int) $request->validated('equipment_id'))
            ->exists();

        if (!$isAllowed) {
            return back()->withInput()->with('error', 'Équipement invalide pour votre périmètre.');
        }

        PreventiveMaintenance::query()->create($request->validated());

        return redirect()
            ->route('maintenance-preventive')
            ->with('success', 'Maintenance préventive ajoutée avec succès.');
    }

    public function edit(PreventiveMaintenance $maintenance_preventive)
    {
        $equipmentQuery = Equipment::query()
            ->select('id', 'inventory_number_current', 'designation')
            ->orderBy('designation');
        ServiceAccess::applyEquipmentScope($equipmentQuery, auth()->user());

        return view('pages.forms.maintenance-edit', [
            'maintenance' => $maintenance_preventive,
            'equipments' => $equipmentQuery->get(),
        ]);
    }

    public function update(UpdatePreventiveMaintenanceRequest $request, PreventiveMaintenance $maintenance_preventive)
    {
        $equipmentQuery = Equipment::query()->select('id');
        ServiceAccess::applyEquipmentScope($equipmentQuery, $request->user());
        $isAllowed = (clone $equipmentQuery)
            ->where('id', (int) $request->validated('equipment_id'))
            ->exists();

        if (!$isAllowed) {
            return back()->withInput()->with('error', 'Équipement invalide pour votre périmètre.');
        }

        $maintenance_preventive->update($request->validated());

        return redirect()
            ->route('maintenance-preventive')
            ->with('success', 'Maintenance préventive modifiée avec succès.');
    }

    public function destroy(PreventiveMaintenance $maintenance_preventive)
    {
        $maintenance_preventive->delete();

        return redirect()
            ->route('maintenance-preventive')
            ->with('success', 'Maintenance préventive supprimée avec succès.');
    }

    private function buildActiveMaintenanceRows()
    {
        return PreventiveMaintenance::query()
            ->with('equipment:id,inventory_number_current,designation')
            ->orderBy('next_maintenance_date')
            ->orderBy('code')
            ->get()
            ->map(function (PreventiveMaintenance $item) {
                $equipmentLabel = trim((string) ($item->equipment?->inventory_number_current . ' - ' . $item->equipment?->designation));

                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'equipement' => $equipmentLabel !== '-' ? $equipmentLabel : ($item->equipment?->designation ?: '-'),
                    'periodicite' => $item->periodicity,
                    'dernier' => optional($item->last_maintenance_date)->format('Y-m-d') ?: '-',
                    'prochain' => optional($item->next_maintenance_date)->format('Y-m-d') ?: '-',
                    'statut' => $item->status,
                    'edit_url' => route('maintenance-preventive.edit', $item),
                    'delete_url' => route('maintenance-preventive.destroy', $item),
                ];
            })
            ->values();
    }

    private function resolveImportHeaderMap(array $rows): array
    {
        $defaultMap = [
            'code' => 'A',
            'equipment' => 'B',
            'periodicity' => 'C',
            'last_date' => 'D',
            'next_date' => 'E',
            'status' => 'F',
        ];

        $firstRow = is_array($rows[1] ?? null) ? $rows[1] : [];
        if ($firstRow === []) {
            return [$defaultMap, 1];
        }

        $aliases = [
            'code' => ['code', 'code_maintenance', 'maintenance_code'],
            'equipment' => ['equipement', 'equipment', 'designation', 'designation_equipement', 'numero_inventaire', 'n_inventaire', 'inventory_number', 'equipment_id'],
            'periodicity' => ['periodicite', 'periodicity', 'frequence'],
            'last_date' => ['derniere_maintenance', 'date_derniere_maintenance', 'last_maintenance_date', 'last_maintenance'],
            'next_date' => ['prochaine_maintenance', 'date_prochaine_maintenance', 'next_maintenance_date', 'next_maintenance'],
            'status' => ['statut', 'status'],
        ];

        $normalizedByColumn = [];
        foreach ($firstRow as $column => $value) {
            $normalizedByColumn[$column] = $this->normalizeLookupToken((string) $value);
        }

        $map = [];
        foreach ($aliases as $field => $candidates) {
            foreach ($normalizedByColumn as $column => $token) {
                if (in_array($token, $candidates, true)) {
                    $map[$field] = $column;
                    break;
                }
            }
        }

        $hasRequired = isset($map['code'], $map['equipment'], $map['periodicity'], $map['next_date']);
        if (!$hasRequired) {
            return [$defaultMap, 1];
        }

        return [[
            'code' => $map['code'],
            'equipment' => $map['equipment'],
            'periodicity' => $map['periodicity'],
            'last_date' => $map['last_date'] ?? null,
            'next_date' => $map['next_date'],
            'status' => $map['status'] ?? null,
        ], 2];
    }

    private function buildScopedEquipmentLookup($user): array
    {
        $query = Equipment::query()->select('id', 'inventory_number_current', 'designation');
        ServiceAccess::applyEquipmentScope($query, $user);

        $equipments = $query->get();

        $byId = [];
        $byInventory = [];
        $byDesignation = [];

        foreach ($equipments as $equipment) {
            $id = (int) $equipment->id;
            $byId[(string) $id] = $id;

            $inventoryToken = $this->normalizeLookupToken((string) ($equipment->inventory_number_current ?? ''));
            if ($inventoryToken !== '') {
                $byInventory[$inventoryToken] = $id;
            }

            $designationToken = $this->normalizeLookupToken((string) ($equipment->designation ?? ''));
            if ($designationToken !== '' && !isset($byDesignation[$designationToken])) {
                $byDesignation[$designationToken] = $id;
            }
        }

        return [
            'by_id' => $byId,
            'by_inventory' => $byInventory,
            'by_designation' => $byDesignation,
        ];
    }

    private function resolveEquipmentIdFromReference(string $reference, array $lookup): ?int
    {
        $trimmed = trim($reference);
        if ($trimmed === '') {
            return null;
        }

        if (isset($lookup['by_id'][$trimmed])) {
            return (int) $lookup['by_id'][$trimmed];
        }

        $normalized = $this->normalizeLookupToken($trimmed);
        if ($normalized === '') {
            return null;
        }

        if (isset($lookup['by_inventory'][$normalized])) {
            return (int) $lookup['by_inventory'][$normalized];
        }

        if (isset($lookup['by_designation'][$normalized])) {
            return (int) $lookup['by_designation'][$normalized];
        }

        if (str_contains($trimmed, '-')) {
            $inventoryPart = trim((string) explode('-', $trimmed, 2)[0]);
            $inventoryToken = $this->normalizeLookupToken($inventoryPart);
            if ($inventoryToken !== '' && isset($lookup['by_inventory'][$inventoryToken])) {
                return (int) $lookup['by_inventory'][$inventoryToken];
            }
        }

        foreach (($lookup['by_designation'] ?? []) as $designationToken => $equipmentId) {
            if (str_contains($normalized, $designationToken) || str_contains($designationToken, $normalized)) {
                return (int) $equipmentId;
            }
        }

        return null;
    }

    private function normalizePeriodicity(string $value): ?string
    {
        $token = $this->normalizeLookupToken($value);
        if ($token === '') {
            return null;
        }

        return match ($token) {
            'mensuel', 'mensuelle', 'mois', 'monthly' => 'Mensuel',
            'trimestriel', 'trimestrielle', 'quarterly', 'trimestre' => 'Trimestriel',
            'semestriel', 'semestrielle', 'semiannual', 'semi-annuel', 'semestrielle' => 'Semestriel',
            'annuel', 'annuelle', 'annual', 'yearly' => 'Annuel',
            default => null,
        };
    }

    private function normalizeStatus(string $value): string
    {
        $token = $this->normalizeLookupToken($value);

        if ($token === '') {
            return 'actif';
        }

        return in_array($token, ['inactif', 'inactive', 'non actif', 'non-actif', 'disabled'], true)
            ? 'inactif'
            : 'actif';
    }

    private function parseExcelDateCell($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
            } catch (Throwable $e) {
                // Fallback below.
            }
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (Throwable $e) {
            return null;
        }
    }

    private function normalizeLookupToken(string $value): string
    {
        $text = Str::ascii(trim($value));
        $text = mb_strtolower($text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }
}
