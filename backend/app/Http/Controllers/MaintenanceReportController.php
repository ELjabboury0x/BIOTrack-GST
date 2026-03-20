<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\AuditLog;
use App\Models\MaintenanceReport;
use App\Models\Service;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\ServiceAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class MaintenanceReportController extends Controller
{
    public function index(Request $request)
    {
        $type = trim((string) $request->query('type', ''));
        $status = trim((string) $request->query('status', ''));
        $search = trim((string) $request->query('q', ''));
        $selectedServiceId = (int) $request->integer('service_id');
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        $correctiveSearch = trim((string) $request->query('corrective_q', ''));
        $correctiveCompany = trim((string) $request->query('corrective_company', ''));
        $correctiveService = trim((string) $request->query('corrective_service', ''));
        $correctiveDate = trim((string) $request->query('corrective_date', ''));

        $query = MaintenanceReport::query()
            ->select([
                'id',
                'report_number',
                'intervention_type',
                'intervention_date',
                'equipment_id',
                'service_id',
                'user_id',
                'duration_minutes',
                'status',
            ])
            ->with(['equipment:id,inventory_number_current,designation', 'service:id,name', 'technician:id,name,login'])
            ->latest('id');

        ServiceAccess::applyReportScope($query, $request->user());

        if (in_array($type, [MaintenanceReport::TYPE_PREVENTIVE, MaintenanceReport::TYPE_CURATIVE, MaintenanceReport::TYPE_DIAGNOSTIC], true)) {
            $query->where('intervention_type', $type);
        }

        if (in_array($status, [
            MaintenanceReport::STATUS_DRAFT,
            MaintenanceReport::STATUS_SUBMITTED,
            MaintenanceReport::STATUS_VALIDATED,
            MaintenanceReport::STATUS_CLOSED,
        ], true)) {
            $query->where('status', $status);
        }

        if ($selectedServiceId > 0) {
            $query->where('service_id', $selectedServiceId);
        }

        if ($dateFrom !== '') {
            $query->whereDate('intervention_date', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('intervention_date', '<=', $dateTo);
        }

        if ($search !== '') {
            $searchLike = '%' . $search . '%';
            $query->where(function ($innerQuery) use ($searchLike) {
                $innerQuery
                    ->where('report_number', 'like', $searchLike)
                    ->orWhereHas('service', fn ($serviceQuery) => $serviceQuery->where('name', 'like', $searchLike))
                    ->orWhereHas('equipment', function ($equipmentQuery) use ($searchLike) {
                        $equipmentQuery
                            ->where('inventory_number_current', 'like', $searchLike)
                            ->orWhere('designation', 'like', $searchLike);
                    })
                    ->orWhereHas('technician', function ($technicianQuery) use ($searchLike) {
                        $technicianQuery
                            ->where('name', 'like', $searchLike)
                            ->orWhere('login', 'like', $searchLike);
                    });
            });
        }

        $reports = $query->get()->map(function (MaintenanceReport $report) {
            return [
                'id' => $report->id,
                'numero' => $report->report_number,
                'type' => match ($report->intervention_type) {
                    MaintenanceReport::TYPE_PREVENTIVE => 'Préventive interne',
                    MaintenanceReport::TYPE_DIAGNOSTIC => 'Diagnostic interne',
                    default => 'Curative interne',
                },
                'date_intervention' => optional($report->intervention_date)->toDateString(),
                'equipement' => trim(($report->equipment?->inventory_number_current ?? '-') . ' - ' . ($report->equipment?->designation ?? '-')),
                'service' => $report->service?->name ?: '-',
                'technicien' => $report->technician?->name ?: ($report->technician?->login ?: '-'),
                'duree' => $report->duration_minutes ? ($report->duration_minutes . ' min') : '-',
                'statut' => $report->status,
                'edit_url' => route('maintenance-reports.edit', $report),
            ];
        })->values();

        $bilanCorrectiveData = collect();
        if ($type === MaintenanceReport::TYPE_CURATIVE && Schema::hasTable('bilan_maintenance_correctives')) {
            $bilanQuery = DB::table('bilan_maintenance_correctives');

            if ($correctiveCompany !== '') {
                $bilanQuery->where('company_name', 'like', '%' . $correctiveCompany . '%');
            }

            if ($correctiveService !== '') {
                $bilanQuery->where('service_names', 'like', '%' . $correctiveService . '%');
            }

            if ($correctiveDate !== '') {
                $bilanQuery->where('intervention_date_text', 'like', '%' . $correctiveDate . '%');
            }

            if ($correctiveSearch !== '') {
                $searchLike = '%' . $correctiveSearch . '%';
                $bilanQuery->where(function ($innerQuery) use ($searchLike) {
                    $innerQuery
                        ->where('company_name', 'like', $searchLike)
                        ->orWhere('equipment_designation', 'like', $searchLike)
                        ->orWhere('brand_name', 'like', $searchLike)
                        ->orWhere('model_name', 'like', $searchLike)
                        ->orWhere('serial_number', 'like', $searchLike)
                        ->orWhere('market_or_contract_ref', 'like', $searchLike)
                        ->orWhere('failure_details', 'like', $searchLike)
                        ->orWhere('observations', 'like', $searchLike)
                        ->orWhere('service_names', 'like', $searchLike)
                        ->orWhere('intervention_date_text', 'like', $searchLike)
                        ->orWhere('source_file', 'like', $searchLike)
                        ->orWhere('source_sheet', 'like', $searchLike);
                });
            }

            $bilanCorrectiveData = $bilanQuery
                ->orderByDesc('id')
                ->get([
                    'id',
                    'company_name',
                    'equipment_designation',
                    'brand_name',
                    'model_name',
                    'serial_number',
                    'market_or_contract_ref',
                    'failure_details',
                    'observations',
                    'service_names',
                    'intervention_date_text',
                    'source_file',
                    'source_sheet',
                    'source_row',
                ])
                ->map(function ($row) {
                    return [
                        'societe' => $row->company_name ?: '-',
                        'equipement' => $row->equipment_designation ?: '-',
                        'marque' => $row->brand_name ?: '-',
                        'modele' => $row->model_name ?: '-',
                        'numero_serie' => $row->serial_number ?: '-',
                        'marche_contrat' => $row->market_or_contract_ref ?: '-',
                        'details_panne' => $row->failure_details ?: '-',
                        'observations' => $row->observations ?: '-',
                        'services' => $row->service_names ?: '-',
                        'date_intervention' => $row->intervention_date_text ?: '-',
                        'source_file' => $row->source_file ?: '-',
                        'source_sheet' => $row->source_sheet ?: '-',
                        'source_row' => $row->source_row ?: '-',
                    ];
                })
                ->values();
        }

        $correctiveQuality = ($type === MaintenanceReport::TYPE_CURATIVE)
            ? $this->buildCorrectiveDataQualityReport()
            : [
                'duplicates_count' => 0,
                'unmatched_company_count' => 0,
                'invalid_date_count' => 0,
                'duplicate_samples' => collect(),
                'unmatched_company_samples' => collect(),
                'invalid_date_samples' => collect(),
            ];

        $correctivePdfDocuments = collect();
        if ($type === MaintenanceReport::TYPE_CURATIVE) {
            $documentLabels = [
                'decharge' => 'Décharge',
                'bon_retour' => 'Bon de retour',
                'intervention_technique' => 'Intervention technique',
            ];

            $originalNamesByPath = AuditLog::query()
                ->where('action', 'maintenance_corrective.pdf_imported')
                ->orderByDesc('id')
                ->get(['meta'])
                ->map(function (AuditLog $log) {
                    $meta = is_array($log->meta) ? $log->meta : [];
                    $storedPath = trim((string) ($meta['stored_path'] ?? ''));
                    $originalName = trim((string) ($meta['original_name'] ?? ''));

                    if ($storedPath === '' || $originalName === '') {
                        return null;
                    }

                    return [
                        'stored_path' => $storedPath,
                        'original_name' => $originalName,
                    ];
                })
                ->filter()
                ->unique('stored_path')
                ->pluck('original_name', 'stored_path');

            $baseDirectory = 'maintenance-reports/corrective-pdf';
            if (Storage::disk('public')->exists($baseDirectory)) {
                $correctivePdfDocuments = collect(Storage::disk('public')->allFiles($baseDirectory))
                    ->filter(fn (string $path) => strtolower((string) pathinfo($path, PATHINFO_EXTENSION)) === 'pdf')
                    ->map(function (string $path) use ($baseDirectory, $documentLabels, $originalNamesByPath) {
                        $relativePath = str_starts_with($path, $baseDirectory . '/')
                            ? substr($path, strlen($baseDirectory) + 1)
                            : $path;

                        $segments = array_values(array_filter(explode('/', $relativePath)));
                        $documentKind = (string) ($segments[0] ?? 'decharge');

                        $lastModifiedTs = 0;
                        try {
                            $lastModifiedTs = (int) Storage::disk('public')->lastModified($path);
                        } catch (\Throwable $e) {
                            $lastModifiedTs = 0;
                        }

                        return [
                            'document_kind' => $documentKind,
                            'document_label' => $documentLabels[$documentKind] ?? ucfirst(str_replace('_', ' ', $documentKind)),
                            'file_name' => (string) ($originalNamesByPath[$path] ?? basename($path)),
                            'stored_path' => $path,
                            'file_url' => Storage::disk('public')->url($path),
                            'last_modified_ts' => $lastModifiedTs,
                            'last_modified' => $lastModifiedTs > 0 ? Carbon::createFromTimestamp($lastModifiedTs)->format('d/m/Y H:i') : '-',
                        ];
                    })
                    ->sortByDesc('last_modified_ts')
                    ->values();
            }
        }

        return view('pages.maintenance-reports.index', [
            'reportsData' => $reports,
            'bilanCorrectiveData' => $bilanCorrectiveData,
            'currentType' => $type,
            'currentStatus' => $status,
            'searchTerm' => $search,
            'selectedServiceId' => $selectedServiceId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'serviceOptions' => $this->loadServiceOptions($request->user()),
            'correctiveSearch' => $correctiveSearch,
            'correctiveCompany' => $correctiveCompany,
            'correctiveService' => $correctiveService,
            'correctiveDate' => $correctiveDate,
            'correctiveQuality' => $correctiveQuality,
            'correctivePdfDocuments' => $correctivePdfDocuments,
        ]);
    }

    public function correctiveManual()
    {
        return view('pages.maintenance-reports.corrective-manual');
    }

    public function correctiveTemplate()
    {
        $sourceFile = base_path('Bilan_Activites' . DIRECTORY_SEPARATOR . 'Maintenance corrective.xlsx');
        if (!is_file($sourceFile)) {
            return redirect()
                ->route('maintenance-reports.index', ['type' => MaintenanceReport::TYPE_CURATIVE])
                ->with('error', 'Fichier template introuvable: Bilan_Activites/Maintenance corrective.xlsx');
        }

        return response()->download($sourceFile, 'Maintenance-corrective-template.xlsx');
    }

    public function exportCorrectiveExcel(Request $request)
    {
        if (!Schema::hasTable('bilan_maintenance_correctives')) {
            return redirect()
                ->route('maintenance-reports.index', ['type' => MaintenanceReport::TYPE_CURATIVE])
                ->with('error', 'Table corrective introuvable.');
        }

        $correctiveSearch = trim((string) $request->query('corrective_q', ''));
        $correctiveCompany = trim((string) $request->query('corrective_company', ''));
        $correctiveService = trim((string) $request->query('corrective_service', ''));
        $correctiveDate = trim((string) $request->query('corrective_date', ''));

        $query = DB::table('bilan_maintenance_correctives')->orderByDesc('id');

        if ($correctiveCompany !== '') {
            $query->where('company_name', 'like', '%' . $correctiveCompany . '%');
        }

        if ($correctiveService !== '') {
            $query->where('service_names', 'like', '%' . $correctiveService . '%');
        }

        if ($correctiveDate !== '') {
            $query->where('intervention_date_text', 'like', '%' . $correctiveDate . '%');
        }

        if ($correctiveSearch !== '') {
            $searchLike = '%' . $correctiveSearch . '%';
            $query->where(function ($innerQuery) use ($searchLike) {
                $innerQuery
                    ->where('company_name', 'like', $searchLike)
                    ->orWhere('equipment_designation', 'like', $searchLike)
                    ->orWhere('brand_name', 'like', $searchLike)
                    ->orWhere('model_name', 'like', $searchLike)
                    ->orWhere('serial_number', 'like', $searchLike)
                    ->orWhere('market_or_contract_ref', 'like', $searchLike)
                    ->orWhere('failure_details', 'like', $searchLike)
                    ->orWhere('observations', 'like', $searchLike)
                    ->orWhere('service_names', 'like', $searchLike)
                    ->orWhere('intervention_date_text', 'like', $searchLike)
                    ->orWhere('source_file', 'like', $searchLike)
                    ->orWhere('source_sheet', 'like', $searchLike);
            });
        }

        $rows = $query->get([
            'company_name',
            'equipment_designation',
            'brand_name',
            'model_name',
            'serial_number',
            'market_or_contract_ref',
            'failure_details',
            'observations',
            'service_names',
            'intervention_date_text',
            'source_file',
            'source_sheet',
            'source_row',
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Maintenance corrective');

        $headers = [
            'Société',
            'Désignation équipement',
            'Marque',
            'Modèle',
            'N° série',
            'N° marché/contrat',
            'Détails panne',
            'Observations',
            'Services',
            'Date intervention',
            'Fichier source',
            'Feuille source',
            'Ligne source',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $rowIndex = 2;
        foreach ($rows as $row) {
            $sheet->fromArray([
                (string) ($row->company_name ?? ''),
                (string) ($row->equipment_designation ?? ''),
                (string) ($row->brand_name ?? ''),
                (string) ($row->model_name ?? ''),
                (string) ($row->serial_number ?? ''),
                (string) ($row->market_or_contract_ref ?? ''),
                (string) ($row->failure_details ?? ''),
                (string) ($row->observations ?? ''),
                (string) ($row->service_names ?? ''),
                (string) ($row->intervention_date_text ?? ''),
                (string) ($row->source_file ?? ''),
                (string) ($row->source_sheet ?? ''),
                (string) ($row->source_row ?? ''),
            ], null, 'A' . $rowIndex);

            $rowIndex++;
        }

        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $fileName = 'maintenance-corrective-' . now()->format('Y-m-d-His') . '.xlsx';
        $tempFile = storage_path('app/' . uniqid('maintenance_corrective_export_', true) . '.xlsx');
        (new Xlsx($spreadsheet))->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $requestedType = trim((string) $request->query('type', ''));
        $defaultType = in_array($requestedType, [MaintenanceReport::TYPE_PREVENTIVE, MaintenanceReport::TYPE_CURATIVE, MaintenanceReport::TYPE_DIAGNOSTIC], true)
            ? $requestedType
            : MaintenanceReport::TYPE_PREVENTIVE;

        $staff = $this->loadStaffOptions();

        return view('pages.maintenance-reports.form', [
            'report' => new MaintenanceReport([
                'status' => MaintenanceReport::STATUS_DRAFT,
                'intervention_type' => $defaultType,
                'intervention_date' => now()->toDateString(),
            ]),
            'equipments' => $this->loadEquipmentOptions($user),
            'services' => $this->loadServiceOptions($user),
            'technicians' => $staff['technicians'],
            'engineers' => $staff['engineers'],
            'reportHistory' => $this->buildReportHistory(
                $request,
                null,
                (int) $request->integer('equipment_id'),
                (int) $request->integer('service_id')
            ),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $equipment = Equipment::query()->findOrFail((int) $validated['equipment_id']);
        $equipmentScopeQuery = Equipment::query()->select('id');
        ServiceAccess::applyEquipmentScope($equipmentScopeQuery, $request->user());
        if (!(clone $equipmentScopeQuery)->where('id', (int) $equipment->id)->exists()) {
            return back()->withInput()->with('error', 'Équipement invalide pour votre périmètre.');
        }

        $report = new MaintenanceReport($validated);
        $report->hospital_name = $validated['hospital_name'] ?? 'Hôpital Universitaire Mère-Enfant Mohammed VI - Tanger';
        $report->equipment_designation = $validated['equipment_designation'] ?? $equipment->designation;
        $report->equipment_serial_number = $validated['equipment_serial_number'] ?? $equipment->serial_number;
        $report->equipment_inventory_number = $validated['equipment_inventory_number'] ?? $equipment->inventory_number_current;

        $this->attachFiles($request, $report);

        $report->save();

        return redirect()->route('maintenance-reports.edit', $report)->with('success', 'Rapport créé en brouillon.');
    }

    public function edit(Request $request, MaintenanceReport $maintenanceReport)
    {
        $user = auth()->user();
        $staff = $this->loadStaffOptions();

        return view('pages.maintenance-reports.form', [
            'report' => $maintenanceReport,
            'equipments' => $this->loadEquipmentOptions($user),
            'services' => $this->loadServiceOptions($user),
            'technicians' => $staff['technicians'],
            'engineers' => $staff['engineers'],
            'reportHistory' => $this->buildReportHistory(
                $request,
                $maintenanceReport,
                (int) $maintenanceReport->equipment_id,
                (int) $maintenanceReport->service_id
            ),
        ]);
    }

    public function update(Request $request, MaintenanceReport $maintenanceReport)
    {
        if ($maintenanceReport->status === MaintenanceReport::STATUS_CLOSED) {
            return back()->with('error', 'Un rapport clôturé ne peut plus être modifié.');
        }

        $validated = $this->validatePayload($request);
        $equipment = Equipment::query()->findOrFail((int) $validated['equipment_id']);
        $equipmentScopeQuery = Equipment::query()->select('id');
        ServiceAccess::applyEquipmentScope($equipmentScopeQuery, $request->user());
        if (!(clone $equipmentScopeQuery)->where('id', (int) $equipment->id)->exists()) {
            return back()->withInput()->with('error', 'Équipement invalide pour votre périmètre.');
        }

        $maintenanceReport->fill($validated);
        $maintenanceReport->hospital_name = $validated['hospital_name'] ?? $maintenanceReport->hospital_name;
        $maintenanceReport->equipment_designation = $validated['equipment_designation'] ?? $equipment->designation;
        $maintenanceReport->equipment_serial_number = $validated['equipment_serial_number'] ?? $equipment->serial_number;
        $maintenanceReport->equipment_inventory_number = $validated['equipment_inventory_number'] ?? $equipment->inventory_number_current;

        $this->attachFiles($request, $maintenanceReport);

        $maintenanceReport->save();

        return back()->with('success', 'Rapport mis à jour.');
    }

    public function submit(MaintenanceReport $maintenanceReport)
    {
        if (!$maintenanceReport->canTransitionTo(MaintenanceReport::STATUS_SUBMITTED)) {
            return back()->with('error', 'Transition invalide.');
        }

        $maintenanceReport->status = MaintenanceReport::STATUS_SUBMITTED;
        $maintenanceReport->submitted_at = now();
        $maintenanceReport->save();

        app(AuditLogger::class)->log('maintenance_report.submitted', $maintenanceReport, [
            'status' => MaintenanceReport::STATUS_SUBMITTED,
        ], request());

        return back()->with('success', 'Rapport soumis pour validation.');
    }

    public function validateReport(MaintenanceReport $maintenanceReport)
    {
        if (!$maintenanceReport->canTransitionTo(MaintenanceReport::STATUS_VALIDATED)) {
            return back()->with('error', 'Transition invalide.');
        }

        $maintenanceReport->status = MaintenanceReport::STATUS_VALIDATED;
        $maintenanceReport->validated_at = now();
        if (!$maintenanceReport->engineer_user_id && auth()->check()) {
            $maintenanceReport->engineer_user_id = auth()->id();
        }
        $maintenanceReport->save();

        app(AuditLogger::class)->log('maintenance_report.validated', $maintenanceReport, [
            'status' => MaintenanceReport::STATUS_VALIDATED,
            'engineer_user_id' => (int) ($maintenanceReport->engineer_user_id ?? 0),
        ], request());

        return back()->with('success', 'Rapport validé.');
    }

    public function close(MaintenanceReport $maintenanceReport)
    {
        if (!$maintenanceReport->canTransitionTo(MaintenanceReport::STATUS_CLOSED)) {
            return back()->with('error', 'Transition invalide.');
        }

        $maintenanceReport->status = MaintenanceReport::STATUS_CLOSED;
        $maintenanceReport->closed_at = now();
        $maintenanceReport->save();

        app(AuditLogger::class)->log('maintenance_report.closed', $maintenanceReport, [
            'status' => MaintenanceReport::STATUS_CLOSED,
        ], request());

        return back()->with('success', 'Rapport clôturé.');
    }

    public function exportPdf(Request $request, MaintenanceReport $maintenanceReport)
    {
        return $this->generatePDF($request, $maintenanceReport);
    }

    public function generatePDF(Request $request, MaintenanceReport $maintenanceReport)
    {
        $maintenanceReport->load(['equipment', 'service', 'technician', 'engineer']);

        $paper = strtolower((string) $request->query('paper', 'a4'));
        if (!in_array($paper, ['a4', 'a3', 'letter'], true)) {
            $paper = 'a4';
        }

        $orientation = strtolower((string) $request->query('orientation', 'portrait'));
        if (!in_array($orientation, ['portrait', 'landscape'], true)) {
            $orientation = 'portrait';
        }

        $pdf = Pdf::loadView('pdf.report', [
            'report' => $maintenanceReport,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper($paper, $orientation);

        return $pdf->download('rapport-intervention-' . $maintenanceReport->report_number . '.pdf');
    }

    public function importCorrectiveFromBilan(Request $request)
    {
        $validated = $request->validate([
            'corrective_file' => 'required|file|mimes:xlsx,xls|max:51200',
        ]);

        $uploadedFile = $validated['corrective_file'];

        $originalName = trim((string) $uploadedFile->getClientOriginalName());
        $safeName = $originalName !== '' ? $originalName : ('maintenance-corrective-' . now()->format('YmdHis') . '.xlsx');

        $tempDir = storage_path('app/imports/bilan-corrective-only');
        $tempFile = $tempDir . DIRECTORY_SEPARATOR . $safeName;

        try {
            if (is_dir($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            File::ensureDirectoryExists($tempDir);
            File::copy($uploadedFile->getRealPath(), $tempFile);

            $exitCode = Artisan::call('gmao:import-bilan-activites', [
                '--dir' => $tempDir,
                '--force-importer' => 'corrective',
            ]);

            $artisanOutput = (string) Artisan::output();
            preg_match('/Lignes\s+import[ée]es\s*:\s*(\d+)/iu', $artisanOutput, $matches);
            $importedRows = isset($matches[1]) ? (int) $matches[1] : 0;

            if ($exitCode !== 0) {
                return redirect()
                    ->route('maintenance-reports.index', ['type' => MaintenanceReport::TYPE_CURATIVE])
                    ->with('error', 'Import corrective échoué.');
            }

            if ($importedRows === 0) {
                return redirect()
                    ->route('maintenance-reports.index', ['type' => MaintenanceReport::TYPE_CURATIVE])
                    ->with('error', 'Import terminé mais aucune ligne corrective n\'a été importée. Vérifiez le contenu du fichier Excel.');
            }

            $syncExitCode = Artisan::call('gmao:sync-corrective-bilan-reports', [
                '--default-duration' => 120,
                '--status' => MaintenanceReport::STATUS_VALIDATED,
            ]);
            $syncOutput = (string) Artisan::output();

            if ($syncExitCode !== 0) {
                return redirect()
                    ->route('maintenance-reports.index', ['type' => MaintenanceReport::TYPE_CURATIVE])
                    ->with('error', 'Import corrective réussi, mais la synchronisation vers rapports a échoué.');
            }

            preg_match('/Rapports\s+cr[ée]és\s*:\s*(\d+)/iu', $syncOutput, $syncCreatedMatches);
            preg_match('/Ignor[ée]s\s*\(d[ée]j[àa]\s+synchronis[ée]s\)\s*:\s*(\d+)/iu', $syncOutput, $syncExistingMatches);
            preg_match('/Ignor[ée]s\s*\([ée]quipement\s+non\s+trouv[ée]\)\s*:\s*(\d+)/iu', $syncOutput, $syncUnmatchedMatches);
            preg_match('/Ignor[ée]s\s*\(service\s+indisponible\)\s*:\s*(\d+)/iu', $syncOutput, $syncNoServiceMatches);

            $syncCreated = isset($syncCreatedMatches[1]) ? (int) $syncCreatedMatches[1] : 0;
            $syncExisting = isset($syncExistingMatches[1]) ? (int) $syncExistingMatches[1] : 0;
            $syncUnmatched = isset($syncUnmatchedMatches[1]) ? (int) $syncUnmatchedMatches[1] : 0;
            $syncNoService = isset($syncNoServiceMatches[1]) ? (int) $syncNoServiceMatches[1] : 0;

            app(AuditLogger::class)->log('maintenance_corrective.import.executed', 'bilan_maintenance_correctives', [
                'source_file' => $safeName,
                'import_dir' => $tempDir,
                'exit_code' => $exitCode,
                'imported_rows' => $importedRows,
                'sync_created_reports' => $syncCreated,
                'sync_skipped_existing' => $syncExisting,
                'sync_skipped_unmatched_equipment' => $syncUnmatched,
                'sync_skipped_no_service' => $syncNoService,
            ], request());

            return redirect()
                ->route('maintenance-reports.index', ['type' => MaintenanceReport::TYPE_CURATIVE])
                ->with('success', 'Import corrective terminé (' . $importedRows . ' ligne(s)) pour le fichier: ' . $safeName . '. Rapports créés: ' . $syncCreated . ', déjà synchronisés: ' . $syncExisting . ', sans équipement correspondant: ' . $syncUnmatched . ', sans service: ' . $syncNoService . '.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('maintenance-reports.index', ['type' => MaintenanceReport::TYPE_CURATIVE])
                ->with('error', 'Import corrective impossible: ' . $e->getMessage());
        } finally {
            if (is_dir($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    public function importCorrectivePdf(Request $request)
    {
        $validated = $request->validate([
            'document_kind' => ['required', Rule::in(['decharge', 'bon_retour', 'intervention_technique'])],
            'corrective_pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        $uploadedPdf = $validated['corrective_pdf'];
        $documentKind = (string) $validated['document_kind'];

        $documentLabels = [
            'decharge' => 'Décharge',
            'bon_retour' => 'Bon de retour',
            'intervention_technique' => 'Intervention technique',
        ];

        $targetDirectory = 'maintenance-reports/corrective-pdf/' . $documentKind;
        $originalName = (string) $uploadedPdf->getClientOriginalName();
        $originalBaseName = (string) pathinfo($originalName, PATHINFO_FILENAME);
        $safeBaseName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $originalBaseName);
        $safeBaseName = trim((string) $safeBaseName, '.-_');
        if ($safeBaseName === '') {
            $safeBaseName = 'document-' . $documentKind;
        }

        $extension = strtolower((string) $uploadedPdf->getClientOriginalExtension());
        if ($extension === '') {
            $extension = 'pdf';
        }

        $fileName = $safeBaseName . '.' . $extension;
        $counter = 1;
        while (Storage::disk('public')->exists($targetDirectory . '/' . $fileName)) {
            $counter++;
            $fileName = $safeBaseName . '-' . $counter . '.' . $extension;
        }

        $storedPath = $uploadedPdf->storeAs($targetDirectory, $fileName, 'public');

        app(AuditLogger::class)->log('maintenance_corrective.pdf_imported', 'maintenance_reports', [
            'document_kind' => $documentKind,
            'document_label' => $documentLabels[$documentKind] ?? $documentKind,
            'stored_path' => $storedPath,
            'stored_name' => $fileName,
            'original_name' => (string) $uploadedPdf->getClientOriginalName(),
            'size' => (int) $uploadedPdf->getSize(),
        ], $request);

        return redirect()
            ->route('maintenance-reports.index', ['type' => MaintenanceReport::TYPE_CURATIVE])
            ->with('success', 'PDF importé avec succès (' . ($documentLabels[$documentKind] ?? $documentKind) . ').');
    }

    public function deleteCorrectivePdf(Request $request)
    {
        $validated = $request->validate([
            'stored_path' => ['required', 'string'],
        ]);

        $storedPath = trim((string) $validated['stored_path']);
        $baseDirectory = 'maintenance-reports/corrective-pdf/';

        if (!str_starts_with($storedPath, $baseDirectory)) {
            return redirect()
                ->route('maintenance-reports.index', ['type' => MaintenanceReport::TYPE_CURATIVE])
                ->with('error', 'Chemin de fichier non autorisé.');
        }

        if (strtolower((string) pathinfo($storedPath, PATHINFO_EXTENSION)) !== 'pdf') {
            return redirect()
                ->route('maintenance-reports.index', ['type' => MaintenanceReport::TYPE_CURATIVE])
                ->with('error', 'Seuls les fichiers PDF peuvent être supprimés ici.');
        }

        if (!Storage::disk('public')->exists($storedPath)) {
            return redirect()
                ->route('maintenance-reports.index', ['type' => MaintenanceReport::TYPE_CURATIVE])
                ->with('error', 'Fichier introuvable ou déjà supprimé.');
        }

        $fileName = basename($storedPath);
        Storage::disk('public')->delete($storedPath);

        app(AuditLogger::class)->log('maintenance_corrective.pdf_deleted', 'maintenance_reports', [
            'stored_path' => $storedPath,
            'file_name' => $fileName,
        ], $request);

        return redirect()
            ->route('maintenance-reports.index', ['type' => MaintenanceReport::TYPE_CURATIVE])
            ->with('success', 'PDF supprimé avec succès: ' . $fileName . '.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'intervention_type' => 'required|in:preventive,curative,diagnostic',
            'intervention_date' => 'required|date',
            'started_at' => 'nullable|date',
            'ended_at' => 'nullable|date|after_or_equal:started_at',
            'equipment_id' => 'required|exists:equipments,id',
            'service_id' => 'required|exists:services,id',
            'user_id' => 'required|exists:users,id',
            'engineer_user_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'major'))],
            'hospital_name' => 'nullable|string|max:255',
            'unit_code' => 'nullable|string|max:120',
            'equipment_designation' => 'nullable|string|max:255',
            'equipment_serial_number' => 'nullable|string|max:120',
            'equipment_inventory_number' => 'nullable|string|max:80',
            'supplier_name' => 'nullable|string|max:180',
            'brand_name' => 'nullable|string|max:120',
            'model_name' => 'nullable|string|max:120',
            'problem_description' => 'nullable|string',
            'operations_performed' => 'nullable|string',
            'technician_signature' => 'nullable|image|max:4096',
            'engineer_signature' => 'nullable|image|max:4096',
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:6144',
        ]);
    }

    private function attachFiles(Request $request, MaintenanceReport $report): void
    {
        if ($request->hasFile('technician_signature')) {
            $report->technician_signature_path = $request->file('technician_signature')->store('maintenance-reports/signatures', 'public');
        }

        if ($request->hasFile('engineer_signature')) {
            $report->engineer_signature_path = $request->file('engineer_signature')->store('maintenance-reports/signatures', 'public');
        }

        if ($request->hasFile('photos')) {
            $existing = is_array($report->photo_paths) ? $report->photo_paths : [];
            $uploaded = [];

            foreach ($request->file('photos') as $photo) {
                $uploaded[] = $photo->store('maintenance-reports/photos', 'public');
            }

            $report->photo_paths = array_values(array_merge($existing, $uploaded));
        }
    }

    private function buildReportHistory(
        Request $request,
        ?MaintenanceReport $currentReport,
        int $equipmentId = 0,
        int $serviceId = 0
    ) {
        $query = MaintenanceReport::query()
            ->select([
                'id',
                'report_number',
                'intervention_type',
                'intervention_date',
                'equipment_id',
                'service_id',
                'user_id',
                'status',
            ])
            ->with(['equipment:id,inventory_number_current,designation', 'service:id,name', 'technician:id,name,login'])
            ->latest('id');

        ServiceAccess::applyReportScope($query, $request->user());

        if ($equipmentId > 0) {
            $query->where('equipment_id', $equipmentId);
        } elseif ($serviceId > 0) {
            $query->where('service_id', $serviceId);
        }

        if ($currentReport?->exists) {
            $query->where('id', '<>', (int) $currentReport->id);
        }

        return $query
            ->limit(8)
            ->get()
            ->map(function (MaintenanceReport $report) {
                return [
                    'id' => $report->id,
                    'numero' => $report->report_number,
                    'date' => optional($report->intervention_date)->format('d/m/Y') ?: '-',
                    'type' => match ($report->intervention_type) {
                        MaintenanceReport::TYPE_PREVENTIVE => 'Préventive interne',
                        MaintenanceReport::TYPE_DIAGNOSTIC => 'Diagnostic interne',
                        default => 'Curative interne',
                    },
                    'service' => $report->service?->name ?: '-',
                    'equipement' => trim(((string) ($report->equipment?->inventory_number_current ?: '-')) . ' - ' . ((string) ($report->equipment?->designation ?: '-'))),
                    'technicien' => $report->technician?->name ?: ($report->technician?->login ?: '-'),
                    'statut' => $report->status,
                    'edit_url' => route('maintenance-reports.edit', $report),
                ];
            })
            ->values();
    }

    private function loadEquipmentOptions(?User $user)
    {
        $equipmentQuery = Equipment::query()
            ->select('id', 'inventory_number_current', 'designation', 'service_id')
            ->orderBy('inventory_number_current');

        ServiceAccess::applyEquipmentScope($equipmentQuery, $user);

        return $equipmentQuery->get();
    }

    private function loadServiceOptions(?User $user)
    {
        $servicesQuery = Service::query()->excludeHiddenForUi()->select('id', 'name')->orderBy('name');

        if ($user && !$user->hasGlobalAccess()) {
            $serviceIds = $user->isUnitRestricted()
                ? ($user->service_id ? [(int) $user->service_id] : [])
                : $user->allowedServiceIds();
            $servicesQuery->whereIn('id', $serviceIds);
        }

        return $servicesQuery->get();
    }

    private function loadStaffOptions(): array
    {
        $technicians = User::query()
            ->whereIn('role', ['technician', 'technicien', 'ingenieur', 'major'])
            ->orderBy('name')
            ->get(['id', 'name', 'login', 'role']);

        return [
            'technicians' => $technicians,
            'engineers' => $technicians->where('role', 'major')->values(),
        ];
    }

    private function buildCorrectiveDataQualityReport(): array
    {
        if (!Schema::hasTable('bilan_maintenance_correctives')) {
            return [
                'duplicates_count' => 0,
                'unmatched_company_count' => 0,
                'invalid_date_count' => 0,
                'duplicate_samples' => collect(),
                'unmatched_company_samples' => collect(),
                'invalid_date_samples' => collect(),
            ];
        }

        $rows = DB::table('bilan_maintenance_correctives')
            ->get([
                'id',
                'company_name',
                'equipment_designation',
                'serial_number',
                'intervention_date_text',
                'service_names',
            ]);

        $companies = DB::table('companies')->pluck('name');
        $companyTokens = $companies
            ->map(fn ($name) => $this->normalizeToken((string) $name))
            ->filter()
            ->unique()
            ->values();

        $duplicates = $rows
            ->groupBy(function ($row) {
                return implode('|', [
                    $this->normalizeToken((string) ($row->company_name ?? '')),
                    $this->normalizeToken((string) ($row->equipment_designation ?? '')),
                    $this->normalizeToken((string) ($row->serial_number ?? '')),
                    $this->normalizeToken((string) ($row->intervention_date_text ?? '')),
                ]);
            })
            ->filter(fn ($group, $key) => $key !== '|||' && $group->count() > 1)
            ->values();

        $unmatched = $rows
            ->filter(function ($row) use ($companyTokens) {
                $token = $this->normalizeToken((string) ($row->company_name ?? ''));
                if ($token === '') {
                    return true;
                }

                foreach ($companyTokens as $companyToken) {
                    if ($companyToken !== '' && (str_contains($companyToken, $token) || str_contains($token, $companyToken))) {
                        return false;
                    }
                }

                return true;
            })
            ->values();

        $invalidDates = $rows
            ->filter(function ($row) {
                return !$this->isValidCorrectiveDate((string) ($row->intervention_date_text ?? ''));
            })
            ->values();

        return [
            'duplicates_count' => (int) $duplicates->sum(fn ($group) => max(0, $group->count() - 1)),
            'unmatched_company_count' => $unmatched->count(),
            'invalid_date_count' => $invalidDates->count(),
            'duplicate_samples' => $duplicates->take(10)->map(function ($group) {
                $first = $group->first();

                return [
                    'company_name' => (string) ($first->company_name ?? '-'),
                    'equipment_designation' => (string) ($first->equipment_designation ?? '-'),
                    'serial_number' => (string) ($first->serial_number ?? '-'),
                    'intervention_date_text' => (string) ($first->intervention_date_text ?? '-'),
                    'occurrences' => $group->count(),
                ];
            })->values(),
            'unmatched_company_samples' => $unmatched->take(10)->map(fn ($row) => [
                'company_name' => (string) ($row->company_name ?? '-'),
                'equipment_designation' => (string) ($row->equipment_designation ?? '-'),
                'service_names' => (string) ($row->service_names ?? '-'),
            ])->values(),
            'invalid_date_samples' => $invalidDates->take(10)->map(fn ($row) => [
                'company_name' => (string) ($row->company_name ?? '-'),
                'equipment_designation' => (string) ($row->equipment_designation ?? '-'),
                'intervention_date_text' => (string) ($row->intervention_date_text ?? '-'),
            ])->values(),
        ];
    }

    private function normalizeToken(string $value): string
    {
        $ascii = strtoupper(trim((string) iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value)));

        return preg_replace('/[^A-Z0-9]+/', '', $ascii) ?: '';
    }

    private function isValidCorrectiveDate(string $value): bool
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return false;
        }

        try {
            Carbon::parse($trimmed);
            return true;
        } catch (\Throwable $e) {
        }

        if (preg_match('/^\d{1,2}[\/-]\d{1,2}[\/-]\d{4}$/', $trimmed) === 1) {
            [$a, $b, $y] = preg_split('/[\/-]/', $trimmed);
            return checkdate((int) $b, (int) $a, (int) $y);
        }

        return false;
    }
}
