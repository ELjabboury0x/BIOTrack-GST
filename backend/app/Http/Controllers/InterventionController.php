<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Equipment;
use App\Models\ExternalIntervention;
use App\Models\ExternalInterventionLog;
use App\Models\Intervention;
use App\Models\MaintenanceReport;
use App\Models\User;
use App\Services\DashboardMetricsService;
use App\Services\RealtimeMetricsBroadcaster;
use App\Support\ServiceAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class InterventionController extends Controller
{
    public function __construct(
        private DashboardMetricsService $dashboardMetricsService,
        private RealtimeMetricsBroadcaster $realtimeMetricsBroadcaster
    ) {
    }

    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $retard = (int) $request->query('retard', 0);
        $lateThresholdDays = 2;

        $interventionQuery = Intervention::query()
            ->with('equipment:id,inventory_number_current,designation')
            ->when(in_array($status, ['en_attente', 'en_cours', 'termine'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($retard === 1, function ($query) use ($lateThresholdDays) {
                $query->where('status', 'en_attente')
                    ->whereDate('date_start', '<', now()->subDays($lateThresholdDays)->toDateString());
            })
            ->latest('id');

        ServiceAccess::applyInterventionScope($interventionQuery, $request->user());

        $rows = $interventionQuery
            ->get()
            ->map(function (Intervention $intervention) {
                $equipmentName = trim((string) ($intervention->equipment?->designation ?? ''));
                $equipmentInventory = trim((string) ($intervention->equipment?->inventory_number_current ?? ''));
                $equipmentLabel = $equipmentName !== ''
                    ? $equipmentName
                    : ($equipmentInventory !== '' ? $equipmentInventory : 'Équipement #' . $intervention->equipment_id);

                $complaintRef = '-';
                if (Schema::hasColumn('interventions', 'complaint_id')) {
                    $complaintId = (int) ($intervention->complaint_id ?? 0);
                    $complaintRef = $complaintId > 0 ? ('REC-' . str_pad((string) $complaintId, 6, '0', STR_PAD_LEFT)) : '-';
                }

                return [
                    'id' => $intervention->id,
                    'code' => $intervention->code,
                    'reclamation' => $complaintRef,
                    'equipement' => $equipmentLabel,
                    'type' => trim($intervention->type . ' (' . ucfirst((string) ($intervention->maintenance_scope ?: 'interne')) . ')'),
                    'technicien' => $intervention->technician_name ?: 'Non assigné',
                    'statut' => $intervention->status,
                    'date_debut' => optional($intervention->date_start)->toDateString(),
                    'can_close' => $intervention->status !== 'termine',
                    'close_url' => route('interventions.close.form', $intervention->id),
                    'edit_url' => route('interventions.edit', $intervention->id),
                ];
            })
            ->values();

        return view('pages.interventions', [
            'interventionsData' => $rows,
        ]);
    }

    public function create(Request $request)
    {
        $equipmentQuery = Equipment::query()
            ->select('id', 'inventory_number_current', 'designation')
            ->orderBy('designation');

        ServiceAccess::applyEquipmentScope($equipmentQuery, $request->user());

        $equipments = $equipmentQuery->get();

        return view('pages.forms.interventions-create', [
            'equipments' => $equipments,
        ]);
    }

    public function store(Request $request)
    {
        $allowedTypes = ['Préventive', 'Curative', 'Corrective', 'Prédictive', 'Améliorative', 'Systématique', 'Urgente'];

        $validated = $request->validate([
            'equipment_id' => ['required', 'integer', 'exists:equipments,id'],
            'technician_name' => ['nullable', 'string', 'max:150'],
            'type' => ['required', 'in:' . implode(',', $allowedTypes)],
            'maintenance_scope' => ['required', 'in:interne,externe'],
            'external_call_made' => ['nullable', 'boolean'],
            'statut' => ['required', 'in:en_attente,en_cours,termine'],
            'date_start' => ['nullable', 'date'],
        ], [
            'equipment_id.required' => 'Veuillez sélectionner un équipement dans la liste.',
            'equipment_id.exists' => 'Équipement invalide. Veuillez sélectionner un équipement existant.',
            'equipment_id.integer' => 'Équipement invalide. Veuillez sélectionner un équipement valide.',
        ]);

        $equipment = Equipment::query()->findOrFail((int) $validated['equipment_id']);
        Gate::authorize('view-equipment', $equipment);

        $externalCallMade = $validated['maintenance_scope'] === 'externe' && (bool) ($validated['external_call_made'] ?? false);
        if ($externalCallMade && (int) ($equipment->company_id ?? 0) <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'external_call_made' => 'Impossible de créer le ticket SAV automatique : cet équipement n\'est lié à aucune société externe.',
                ]);
        }

        $user = $request->user();
        $isTechnician = $user && in_array((string) $user->role, ['technicien', 'technician'], true);
        $technicianName = $isTechnician
            ? ((string) ($user->name ?: $user->login ?: ''))
            : ($validated['technician_name'] ?? null);

        $intervention = DB::transaction(function () use ($equipment, $technicianName, $validated, $externalCallMade) {
            $createdIntervention = Intervention::create([
                'code' => $this->generateInterventionCode(),
                'equipment_id' => $equipment->id,
                'technician_name' => $technicianName,
                'type' => $validated['type'],
                'maintenance_scope' => $validated['maintenance_scope'],
                'status' => $validated['statut'],
                'date_start' => $validated['date_start'] ?? now()->toDateString(),
            ]);

            if ($externalCallMade) {
                $this->createExternalTicketForIntervention($createdIntervention, $equipment, 'ticket_created_from_otdm');
            }

            return $createdIntervention;
        });

        $this->broadcastOtRealtimeUpdate('created');

        return redirect()
            ->route('interventions')
            ->with('success', 'Intervention ajoutée avec succès. Code généré: ' . $intervention->code);
    }

    public function show(int $id)
    {
        return redirect()->route('interventions');
    }

    public function edit(Request $request, int $id)
    {
        $intervention = Intervention::query()
            ->with([
                'equipment:id,inventory_number_current,designation,icon_class,service_id,company_id',
                'externalIntervention:id,intervention_id,ticket_number',
            ])
            ->findOrFail($id);

        Gate::authorize('view-intervention', $intervention);

        $equipmentQuery = Equipment::query()
            ->select('id', 'inventory_number_current', 'designation', 'icon_class')
            ->orderBy('designation');

        ServiceAccess::applyEquipmentScope($equipmentQuery, $request->user());

        $equipments = $equipmentQuery->get();

        return view('pages.forms.interventions-edit', [
            'intervention' => $intervention,
            'equipments' => $equipments,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $intervention = Intervention::query()->findOrFail($id);
        Gate::authorize('view-intervention', $intervention);

        $allowedTypes = ['Préventive', 'Curative', 'Corrective', 'Prédictive', 'Améliorative', 'Systématique', 'Urgente'];

        $validated = $request->validate([
            'equipment_id' => ['required', 'integer', 'exists:equipments,id'],
            'technician_name' => ['nullable', 'string', 'max:150'],
            'type' => ['required', 'in:' . implode(',', $allowedTypes)],
            'maintenance_scope' => ['required', 'in:interne,externe'],
            'external_call_made' => ['nullable', 'boolean'],
            'statut' => ['required', 'in:en_attente,en_cours,termine'],
            'date_start' => ['nullable', 'date'],
        ], [
            'equipment_id.required' => 'Veuillez sélectionner un équipement dans la liste.',
            'equipment_id.exists' => 'Équipement invalide. Veuillez sélectionner un équipement existant.',
            'equipment_id.integer' => 'Équipement invalide. Veuillez sélectionner un équipement valide.',
        ]);

        $equipment = Equipment::query()->findOrFail((int) $validated['equipment_id']);
        Gate::authorize('view-equipment', $equipment);

        $externalCallMade = $validated['maintenance_scope'] === 'externe' && (bool) ($validated['external_call_made'] ?? false);
        if ($externalCallMade && (int) ($equipment->company_id ?? 0) <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'external_call_made' => 'Impossible de créer le ticket SAV automatique : cet équipement n\'est lié à aucune société externe.',
                ]);
        }

        $user = $request->user();
        $isTechnician = $user && in_array((string) $user->role, ['technicien', 'technician'], true);
        $technicianName = $isTechnician
            ? ((string) ($user->name ?: $user->login ?: ''))
            : ($validated['technician_name'] ?? null);

        DB::transaction(function () use ($intervention, $equipment, $technicianName, $validated, $externalCallMade) {
            $intervention->update([
                'equipment_id' => $equipment->id,
                'technician_name' => $technicianName,
                'type' => $validated['type'],
                'maintenance_scope' => $validated['maintenance_scope'],
                'status' => $validated['statut'],
                'date_start' => $validated['date_start'] ?? $intervention->date_start,
            ]);

            if ($externalCallMade) {
                $this->createExternalTicketForIntervention($intervention->fresh(), $equipment, 'ticket_created_from_otdm_update');
            }
        });

        $this->broadcastOtRealtimeUpdate('updated');

        return redirect()
            ->route('interventions')
            ->with('success', 'Intervention modifiée avec succès.');
    }

    public function closeForm(int $id)
    {
        $intervention = Intervention::query()
            ->with('equipment:id,inventory_number_current,designation,service_id')
            ->findOrFail($id);

        Gate::authorize('view-intervention', $intervention);

        return view('pages.forms.interventions-close', [
            'intervention' => $intervention,
        ]);
    }

    public function close(Request $request, int $id)
    {
        $intervention = Intervention::query()
            ->with('equipment:id,service_id,inventory_number_current,designation,serial_number,brand_name,model_name,unit_name')
            ->findOrFail($id);
        Gate::authorize('view-intervention', $intervention);

        if ($intervention->status === 'termine') {
            return redirect()
                ->route('interventions')
                ->with('success', 'Cette intervention est déjà clôturée.');
        }

        $validated = $request->validate([
            'date_end' => ['required', 'date', 'after_or_equal:date_start'],
            'failure_cause' => ['nullable', 'string', 'max:255'],
            'closure_note' => ['required', 'string', 'max:2000'],
        ]);

        $intervention->update([
            'status' => 'termine',
            'closure_type' => null,
            'date_end' => $validated['date_end'],
            'failure_cause' => $validated['failure_cause'] ?? null,
            'closure_note' => $validated['closure_note'],
            'closed_by_name' => auth()->user()?->name ?? 'Système',
            'closed_at' => now(),
        ]);

        $linkedComplaint = null;
        if (Schema::hasColumn('interventions', 'complaint_id') && (int) ($intervention->complaint_id ?? 0) > 0) {
            $linkedComplaint = Complaint::query()->find((int) $intervention->complaint_id);
        }

        if (!$linkedComplaint) {
            $linkedComplaint = Complaint::query()
                ->where('equipment_id', (int) $intervention->equipment_id)
                ->whereIn('status', ['open', 'in_progress'])
                ->latest('id')
                ->first();
        }

        if ($linkedComplaint) {
            $linkedComplaint->update([
                'status' => 'resolved',
            ]);
        }

        $prefilledReport = null;
        if ($linkedComplaint) {
            $prefilledReport = $this->createPrefilledReportFromComplaint($intervention, $linkedComplaint, $request);
        }

        $this->broadcastOtRealtimeUpdate('closed');

        if ($prefilledReport) {
            return redirect()
                ->route('maintenance-reports.edit', $prefilledReport)
                ->with('success', 'Intervention clôturée avec succès. Le rapport d\'intervention est prérempli automatiquement.');
        }

        return redirect()
            ->route('interventions')
            ->with('success', 'Intervention clôturée avec succès.');
    }

    private function createExternalTicketForIntervention(Intervention $intervention, Equipment $equipment, string $logAction): void
    {
        if ((int) ($equipment->company_id ?? 0) <= 0) {
            return;
        }

        if ($intervention->externalIntervention()->exists()) {
            return;
        }

        $serviceName = null;
        if ((int) ($equipment->service_id ?? 0) > 0) {
            $serviceName = (string) DB::table('services')->where('id', (int) $equipment->service_id)->value('name');
            $serviceName = trim($serviceName) !== '' ? trim($serviceName) : null;
        }

        $firstCallAt = $intervention->date_start ? $intervention->date_start->copy()->startOfDay() : now();
        $statusValue = 'ouvert';

        $ticketPayload = [
            'intervention_id' => (int) $intervention->id,
            'equipment_id' => (int) $equipment->id,
            'company_id' => (int) $equipment->company_id,
            'service_name' => $serviceName,
            'failure_datetime' => $firstCallAt,
            'first_call_datetime' => $firstCallAt,
            'technician_name' => $intervention->technician_name,
        ];

        if (Schema::hasColumn('external_interventions', 'status')) {
            $ticketPayload['status'] = $statusValue;
        }

        if (Schema::hasColumn('external_interventions', 'intervention_status')) {
            $ticketPayload['intervention_status'] = $statusValue;
        }

        $ticket = ExternalIntervention::query()->create($ticketPayload);

        if (Schema::hasColumn('external_interventions', 'ticket_number')) {
            $ticket->update([
                'ticket_number' => sprintf('SAV-%s-%05d', now()->format('Y'), (int) $ticket->id),
            ]);
        }

        if (Schema::hasTable('external_intervention_logs')) {
            ExternalInterventionLog::query()->create([
                'external_intervention_id' => (int) $ticket->id,
                'user_id' => auth()->id(),
                'action_type' => $logAction,
                'from_status' => null,
                'to_status' => $statusValue,
                'payload' => [
                    'source' => 'interventions_module',
                    'intervention_id' => (int) $intervention->id,
                    'maintenance_scope' => (string) $intervention->maintenance_scope,
                ],
                'logged_at' => now(),
            ]);
        }
    }

    private function createPrefilledReportFromComplaint(Intervention $intervention, Complaint $complaint, Request $request): ?MaintenanceReport
    {
        $equipment = $intervention->equipment;
        if (!$equipment) {
            return null;
        }

        $serviceId = (int) ($complaint->service_id ?: $equipment->service_id ?: 0);
        if ($serviceId <= 0) {
            return null;
        }

        $technicianUserId = (int) ($request->user()?->id ?? 0);
        if ($technicianUserId <= 0) {
            $technicianUserId = (int) User::query()
                ->whereIn('role', ['technician', 'technicien', 'ingenieur', 'major', 'admin', 'manager'])
                ->orderBy('id')
                ->value('id');
        }

        if ($technicianUserId <= 0) {
            return null;
        }

        $problemDescription = trim((string) $complaint->description);
        if ($problemDescription === '') {
            $problemDescription = 'Réclamation liée à l\'intervention ' . $intervention->code . '.';
        }

        $operationsPerformed = trim((string) $intervention->closure_note);
        if ($operationsPerformed === '') {
            $operationsPerformed = 'Clôture ' . ($intervention->closure_type ?: 'TECO') . ' appliquée sur OT/DM ' . $intervention->code . '.';
        }

        $report = new MaintenanceReport([
            'intervention_type' => MaintenanceReport::TYPE_CURATIVE,
            'status' => MaintenanceReport::STATUS_DRAFT,
            'intervention_date' => $intervention->date_end ?: now()->toDateString(),
            'equipment_id' => (int) $equipment->id,
            'service_id' => $serviceId,
            'user_id' => $technicianUserId,
            'hospital_name' => 'Hôpital Universitaire Mère-Enfant Mohammed VI - Tanger',
            'unit_code' => trim((string) ($equipment->unit_name ?? '')),
            'equipment_designation' => $equipment->designation,
            'equipment_serial_number' => $equipment->serial_number,
            'equipment_inventory_number' => $equipment->inventory_number_current,
            'brand_name' => $equipment->brand_name,
            'model_name' => $equipment->model_name,
            'problem_description' => $problemDescription,
            'operations_performed' => $operationsPerformed,
        ]);

        $report->save();

        return $report;
    }

    public function codes(Request $request)
    {
        $status = $request->string('status')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        $interventionQuery = Intervention::query()
            ->with('equipment:id,inventory_number_current,designation')
            ->when(in_array($status, ['en_attente', 'en_cours', 'termine'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($dateFrom !== '', function ($query) use ($dateFrom) {
                $query->whereDate('date_start', '>=', $dateFrom);
            })
            ->when($dateTo !== '', function ($query) use ($dateTo) {
                $query->whereDate('date_start', '<=', $dateTo);
            })
            ->latest('id');

        ServiceAccess::applyInterventionScope($interventionQuery, $request->user());

        $codesData = $interventionQuery
            ->get()
            ->map(function (Intervention $intervention) {
                $equipmentName = trim((string) ($intervention->equipment?->designation ?? ''));
                $equipmentInventory = trim((string) ($intervention->equipment?->inventory_number_current ?? ''));
                $equipmentLabel = $equipmentName !== ''
                    ? $equipmentName
                    : ($equipmentInventory !== '' ? $equipmentInventory : 'Équipement #' . $intervention->equipment_id);

                return [
                    'id' => $intervention->id,
                    'code' => $intervention->code,
                    'equipement' => $equipmentLabel,
                    'type' => trim($intervention->type . ' (' . ucfirst((string) ($intervention->maintenance_scope ?: 'interne')) . ')'),
                    'statut' => $intervention->status,
                    'date_debut' => optional($intervention->date_start)->toDateString(),
                    'date_creation' => optional($intervention->created_at)->toDateString(),
                ];
            })
            ->values();

        return view('pages.interventions-codes', [
            'codesData' => $codesData,
            'selectedStatus' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    private function generateInterventionCode(): string
    {
        $year = now()->format('Y');

        return DB::transaction(function () use ($year) {
            $latestCode = Intervention::query()
                ->where('code', 'like', 'INT-' . $year . '-%')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->value('code');

            $sequence = 1;
            if (is_string($latestCode) && preg_match('/^INT-' . $year . '-(\d{4})$/', $latestCode, $matches)) {
                $sequence = ((int) $matches[1]) + 1;
            }

            return sprintf('INT-%s-%04d', $year, $sequence);
        });
    }

    private function broadcastOtRealtimeUpdate(string $action): void
    {
        $this->realtimeMetricsBroadcaster->broadcastDashboardMetrics(
            $this->dashboardMetricsService->build()
        );

        // Explicit OT signal helps major read-only views refresh immediately.
        $this->realtimeMetricsBroadcaster->broadcastGlobalChange('Intervention', $action);
    }
}
