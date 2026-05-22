<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExternalCompanyPlanningRequest;
use App\Http\Requests\UpdateExternalCompanyPlanningRequest;
use App\Models\Company;
use App\Models\ExternalCompanyPlanning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        ExternalCompanyPlanning::syncAutomaticStatuses();

        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));
        $selectedCompanyId = (int) $request->integer('company_id');

        $planningQuery = ExternalCompanyPlanning::query()
            ->with('company:id,name')
            ->orderBy('planned_date')
            ->orderBy('id');

        if ($selectedCompanyId > 0) {
            $planningQuery->where('company_id', $selectedCompanyId);
        }

        if ($dateFrom !== '') {
            $planningQuery->where(function ($query) use ($dateFrom) {
                $query->whereDate('planned_date', '>=', $dateFrom)
                    ->orWhereDate('planned_date_end', '>=', $dateFrom);
            });
        }

        if ($dateTo !== '') {
            $planningQuery->whereDate('planned_date', '<=', $dateTo);
        }

        $plannings = $planningQuery->get();

        $planningData = $plannings->map(function (ExternalCompanyPlanning $planning) {
            $quarter = $this->resolveQuarter($planning);
            $plannedDateStart = optional($planning->planned_date)->format('Y-m-d');
            $plannedDateEnd = optional($planning->planned_date_end)->format('Y-m-d');

            $displayDate = $plannedDateStart ?: '-';
            if (!empty($plannedDateStart) && !empty($plannedDateEnd) && $plannedDateEnd !== $plannedDateStart) {
                $displayDate = $plannedDateStart . ' → ' . $plannedDateEnd;
            }

            return [
                'id' => $planning->id,
                'societe' => $planning->company?->name ?: '-',
                'trimestre' => $quarter ? ('T' . $quarter) : '-',
                'date_prevue' => $displayDate,
                'intervenant' => $planning->contact_person ?: '-',
                'description' => $planning->description ? Str::limit($planning->description, 120) : '-',
                'statut' => $planning->status,
                'edit_url' => route('maintenance-preventive.edit', $planning),
                'delete_url' => route('maintenance-preventive.destroy', $planning),
            ];
        })->values();

        $quarterDashboardRows = $plannings
            ->groupBy(fn (ExternalCompanyPlanning $planning) => $planning->company?->name ?: '-')
            ->map(function ($items, $companyName) {
                $quarters = [];
                for ($quarter = 1; $quarter <= 8; $quarter++) {
                    $quarterItems = $items->filter(function (ExternalCompanyPlanning $planning) use ($quarter) {
                        return $this->resolveQuarter($planning) === $quarter;
                    });

                    $nextDate = optional($quarterItems->sortBy('planned_date')->first()?->planned_date)->format('d/m/Y');
                    $quarters['t' . $quarter] = $quarterItems->isEmpty()
                        ? '-'
                        : ($nextDate ?: '-') . ' (' . $quarterItems->count() . ')';
                }

                return array_merge([
                    'societe' => $companyName,
                    'total' => $items->count(),
                ], $quarters);
            })
            ->values();

        $today = now()->startOfDay();
        $upcomingLimit = $today->copy()->addDays(14);

        $upcomingQuery = ExternalCompanyPlanning::query()
            ->with('company:id,name')
            ->whereDate('planned_date', '>=', $today->toDateString())
            ->whereDate('planned_date', '<=', $upcomingLimit->toDateString())
            ->where('status', '!=', 'termine')
            ->orderBy('planned_date')
            ->orderBy('id');

        if ($selectedCompanyId > 0) {
            $upcomingQuery->where('company_id', $selectedCompanyId);
        }

        $upcomingRows = $upcomingQuery
            ->limit(20)
            ->get()
            ->map(function (ExternalCompanyPlanning $planning) use ($today) {
                $quarter = $this->resolveQuarter($planning);
                $daysLeft = $planning->planned_date
                    ? $today->diffInDays($planning->planned_date, false)
                    : null;

                return [
                    'id' => $planning->id,
                    'societe' => $planning->company?->name ?: '-',
                    'trimestre' => $quarter ? ('T' . $quarter) : '-',
                    'date' => optional($planning->planned_date)->format('d/m/Y') ?: '-',
                    'jours' => $daysLeft,
                    'description' => $planning->description ?: '-',
                    'edit_url' => route('maintenance-preventive.edit', $planning),
                ];
            })
            ->values();

        $selectedCompanyName = null;
        if ($selectedCompanyId > 0) {
            $selectedCompanyName = Company::query()->where('id', $selectedCompanyId)->value('name');
        }

        $contractSourceRows = DB::table('bilan_maintenance_contracts')
            ->select([
                'company_name',
                'brand_name',
                'model_name',
                'serial_number',
                'service_order_date',
                'quarter_1',
                'quarter_2',
                'quarter_3',
                'quarter_4',
                'quarter_5',
                'quarter_6',
                'quarter_7',
                'quarter_8',
                'service_names',
                'contract_number',
            ])
            ->when(!empty($selectedCompanyName), function ($query) use ($selectedCompanyName) {
                $query->where('company_name', $selectedCompanyName);
            })
            ->orderBy('company_name')
            ->orderBy('contract_number')
            ->orderBy('id')
            ->get()
            ->filter(function ($row) use ($dateFrom, $dateTo) {
                if ($dateFrom === '' && $dateTo === '') {
                    return true;
                }

                for ($quarter = 1; $quarter <= 8; $quarter++) {
                    $field = 'quarter_' . $quarter;
                    $date = $this->extractFirstDate((string) ($row->{$field} ?? ''));
                    if ($date === null) {
                        continue;
                    }

                    if ($dateFrom !== '' && $date < $dateFrom) {
                        continue;
                    }

                    if ($dateTo !== '' && $date > $dateTo) {
                        continue;
                    }

                    return true;
                }

                return false;
            });

        $contractCompanyCount = $contractSourceRows
            ->pluck('company_name')
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn ($name) => $name !== '')
            ->unique()
            ->count();

        $contractRows = $contractSourceRows
            ->map(function ($row) {
                return [
                    'societe' => $this->displayText((string) ($row->company_name ?? '')),
                    'marque' => $this->displayText((string) ($row->brand_name ?? '')),
                    'modele' => $this->displayText((string) ($row->model_name ?? '')),
                    'numero_serie' => $this->displayText((string) ($row->serial_number ?? '')),
                    'date_ordre_service' => $this->formatDisplayDate((string) ($row->service_order_date ?? '')),
                    'trimestre_1' => $this->formatDisplayDate((string) ($row->quarter_1 ?? '')),
                    'trimestre_2' => $this->formatDisplayDate((string) ($row->quarter_2 ?? '')),
                    'trimestre_3' => $this->formatDisplayDate((string) ($row->quarter_3 ?? '')),
                    'trimestre_4' => $this->formatDisplayDate((string) ($row->quarter_4 ?? '')),
                    'trimestre_5' => $this->formatDisplayDate((string) ($row->quarter_5 ?? '')),
                    'trimestre_6' => $this->formatDisplayDate((string) ($row->quarter_6 ?? '')),
                    'trimestre_7' => $this->formatDisplayDate((string) ($row->quarter_7 ?? '')),
                    'trimestre_8' => $this->formatDisplayDate((string) ($row->quarter_8 ?? '')),
                    'services' => $this->displayText((string) ($row->service_names ?? '')),
                ];
            })
            ->values();

        $planningCompanyCount = $plannings
            ->pluck('company.name')
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn ($name) => $name !== '')
            ->unique()
            ->count();

        $companiesInPeriodCount = max($planningCompanyCount, $contractCompanyCount);

        $companies = Company::query()->orderBy('name')->get(['id', 'name']);

        return view('pages.planning-societes-externes', [
            'planningData' => $planningData,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'selectedCompanyId' => $selectedCompanyId,
            'companies' => $companies,
            'quarterDashboardRows' => $quarterDashboardRows,
            'upcomingRows' => $upcomingRows,
            'contractRows' => $contractRows,
            'companiesInPeriodCount' => $companiesInPeriodCount,
        ]);
    }

    public function syncFromContracts(Request $request)
    {
        [$created, $updated, $hasContracts] = $this->syncPlanningFromContracts();

        if (!$hasContracts) {
            return redirect()
                ->route('maintenance-preventive')
                ->with('error', 'Aucune donnée de contrat trouvée. Lancez d\'abord l\'import du fichier Contrats de maintenance.');
        }

        return redirect()
            ->route('maintenance-preventive')
            ->with('success', "Planning synchronisé depuis contrats. Créés: {$created}, mis à jour: {$updated}.");
    }

    public function importContractsExcel(Request $request)
    {
        $validated = $request->validate([
            'contracts_file' => 'required|file|mimes:xlsx,xls|max:51200',
        ]);

        $uploadedFile = $validated['contracts_file'];
        $originalName = trim((string) $uploadedFile->getClientOriginalName());
        $safeName = $originalName !== '' ? $originalName : ('contrats-maintenance-' . now()->format('YmdHis') . '.xlsx');

        $tempDir = storage_path('app/imports/planning-contracts-only');
        $tempFile = $tempDir . DIRECTORY_SEPARATOR . $safeName;

        try {
            if (is_dir($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            File::ensureDirectoryExists($tempDir);
            File::copy($uploadedFile->getRealPath(), $tempFile);

            $exitCode = Artisan::call('gmao:import-bilan-activites', [
                '--dir' => $tempDir,
                '--force-importer' => 'contracts',
            ]);

            if ($exitCode !== 0) {
                return redirect()
                    ->route('maintenance-preventive')
                    ->with('error', 'Import Excel contrats échoué.');
            }

            [$created, $updated, $hasContracts] = $this->syncPlanningFromContracts();

            if (!$hasContracts) {
                return redirect()
                    ->route('maintenance-preventive')
                    ->with('error', 'Import terminé mais aucune donnée contrat exploitable n\'a été trouvée.');
            }

            return redirect()
                ->route('maintenance-preventive')
                ->with('success', 'Import Excel contrats réussi (' . $safeName . '). Planning synchronisé: créés ' . $created . ', mis à jour ' . $updated . '.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('maintenance-preventive')
                ->with('error', 'Import Excel impossible: ' . $e->getMessage());
        } finally {
            if (is_dir($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    private function syncPlanningFromContracts(): array
    {
        $contracts = DB::table('bilan_maintenance_contracts')
            ->select([
                'company_name',
                'contract_number',
                'equipment_designation',
                'service_names',
                'source_file',
                'quarter_1',
                'quarter_2',
                'quarter_3',
                'quarter_4',
                'quarter_5',
                'quarter_6',
                'quarter_7',
                'quarter_8',
            ])
            ->whereNotNull('company_name')
            ->get();

        if ($contracts->isEmpty()) {
            return [0, 0, false];
        }

        $created = 0;
        $updated = 0;

        foreach ($contracts as $contract) {
            $companyName = trim((string) ($contract->company_name ?? ''));
            if ($companyName === '') {
                continue;
            }

            $company = Company::query()->firstOrCreate([
                'name' => $companyName,
            ]);

            $createdOrUpdatedForContract = false;

            for ($quarter = 1; $quarter <= 8; $quarter++) {
                $field = 'quarter_' . $quarter;
                $rawDate = trim((string) ($contract->{$field} ?? ''));
                $plannedDate = $this->extractFirstDate($rawDate);

                if ($plannedDate === null) {
                    continue;
                }

                $contractNumber = trim((string) ($contract->contract_number ?? '-'));
                $equipmentDesignation = trim((string) ($contract->equipment_designation ?? '-'));
                $serviceNames = trim((string) ($contract->service_names ?? '-'));
                $sourceFile = trim((string) ($contract->source_file ?? 'Contrats de maintenance.xlsx'));

                $description = sprintf(
                    'Contrat %s | Trimestre %d | Équipement: %s | Service(s): %s',
                    $contractNumber !== '' ? $contractNumber : '-',
                    $quarter,
                    $equipmentDesignation !== '' ? $equipmentDesignation : '-',
                    $serviceNames !== '' ? $serviceNames : '-'
                );

                $sourceHash = hash('sha256', implode('|', [
                    $companyName,
                    $contractNumber,
                    $quarter,
                    $plannedDate,
                    $equipmentDesignation,
                ]));

                $existing = ExternalCompanyPlanning::query()
                    ->where('source_hash', $sourceHash)
                    ->first();

                ExternalCompanyPlanning::query()->updateOrCreate(
                    ['source_hash' => $sourceHash],
                    [
                        'company_id' => (int) $company->id,
                        'planned_date' => $plannedDate,
                        'planned_date_end' => $plannedDate,
                        'contact_person' => null,
                        'description' => $description,
                        'source_file' => $sourceFile,
                        'source_contract' => $contractNumber !== '' ? $contractNumber : null,
                        'source_quarter' => $quarter,
                        'status' => 'en_attente',
                    ]
                );

                if ($existing) {
                    $updated++;
                } else {
                    $created++;
                }

                $createdOrUpdatedForContract = true;
            }

            if (!$createdOrUpdatedForContract) {
                $contractNumber = trim((string) ($contract->contract_number ?? '-'));
                $equipmentDesignation = trim((string) ($contract->equipment_designation ?? '-'));
                $serviceNames = trim((string) ($contract->service_names ?? '-'));
                $sourceFile = trim((string) ($contract->source_file ?? 'Contrats de maintenance.xlsx'));

                $description = sprintf(
                    'Contrat %s | À planifier (trimestres non renseignés) | Équipement: %s | Service(s): %s',
                    $contractNumber !== '' ? $contractNumber : '-',
                    $equipmentDesignation !== '' ? $equipmentDesignation : '-',
                    $serviceNames !== '' ? $serviceNames : '-'
                );

                $fallbackDate = now()->addDays(30)->toDateString();
                $sourceHash = hash('sha256', implode('|', [
                    $companyName,
                    $contractNumber,
                    'fallback-no-quarter',
                    $equipmentDesignation,
                ]));

                $existingFallback = ExternalCompanyPlanning::query()
                    ->where('source_hash', $sourceHash)
                    ->first();

                ExternalCompanyPlanning::query()->updateOrCreate(
                    ['source_hash' => $sourceHash],
                    [
                        'company_id' => (int) $company->id,
                        'planned_date' => $fallbackDate,
                        'planned_date_end' => $fallbackDate,
                        'contact_person' => null,
                        'description' => $description,
                        'source_file' => $sourceFile,
                        'source_contract' => $contractNumber !== '' ? $contractNumber : null,
                        'source_quarter' => null,
                        'status' => 'en_attente',
                    ]
                );

                if ($existingFallback) {
                    $updated++;
                } else {
                    $created++;
                }
            }
        }

        return [$created, $updated, true];
    }

    private function extractFirstDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $value, $matches) === 1) {
            $day = str_pad((string) ((int) $matches[1]), 2, '0', STR_PAD_LEFT);
            $month = str_pad((string) ((int) $matches[2]), 2, '0', STR_PAD_LEFT);
            $year = (string) $matches[3];

            return $year . '-' . $month . '-' . $day;
        }

        return null;
    }

    private function resolveQuarter(ExternalCompanyPlanning $planning): ?int
    {
        if (!empty($planning->source_quarter)) {
            return (int) $planning->source_quarter;
        }

        $description = (string) ($planning->description ?? '');
        if (preg_match('/Trimestre\s*(\d)/i', $description, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    public function create()
    {
        return view('pages.forms.planning-create', [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreExternalCompanyPlanningRequest $request)
    {
        $data = $request->validated();
        if (empty($data['planned_date_end'])) {
            $data['planned_date_end'] = $data['planned_date'];
        }

        ExternalCompanyPlanning::query()->create($data);

        return redirect()
            ->route('maintenance-preventive')
            ->with('success', 'Planning ajouté avec succès.');
    }

    public function edit(ExternalCompanyPlanning $planning)
    {
        return view('pages.forms.planning-edit', [
            'planning' => $planning,
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateExternalCompanyPlanningRequest $request, ExternalCompanyPlanning $planning)
    {
        $data = $request->validated();
        if (empty($data['planned_date_end'])) {
            $data['planned_date_end'] = $data['planned_date'];
        }

        $planning->update($data);

        return redirect()
            ->route('maintenance-preventive')
            ->with('success', 'Planning modifié avec succès.');
    }

    public function destroy(ExternalCompanyPlanning $planning)
    {
        $planning->delete();

        return redirect()
            ->route('maintenance-preventive')
            ->with('success', 'Planning supprimé avec succès.');
    }

    private function displayText(string $value): string
    {
        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : '-';
    }

    private function formatDisplayDate(string $value): string
    {
        $date = $this->extractFirstDate($value);
        if ($date !== null) {
            return date('Y-m-d', strtotime($date));
        }

        return $this->displayText($value);
    }
}
