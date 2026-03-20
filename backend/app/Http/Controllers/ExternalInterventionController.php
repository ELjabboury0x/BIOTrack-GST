<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Equipment;
use App\Models\ExternalIntervention;
use App\Models\ExternalInterventionLog;
use App\Models\Intervention;
use App\Models\Service;
use App\Support\ServiceAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExternalInterventionController extends Controller
{
    /* ------------------------------------------------------------------ */
    /*  LISTE / INDEX                                                       */
    /* ------------------------------------------------------------------ */
    public function index(Request $request)
    {
        $filters = [
            'company_id'  => (int)   $request->integer('company_id'),
            'service_id'  => (int)   $request->integer('service_id'),
            'service_name'=> trim((string) $request->query('service_name', '')),
            'status'      => trim((string) $request->query('status', '')),
            'date_from'   => trim((string) $request->query('date_from', '')),
            'date_to'     => trim((string) $request->query('date_to', '')),
            'q'           => trim((string) $request->query('q', '')),
        ];

        $query = ExternalIntervention::query()
            ->with([
                'equipment:id,designation,inventory_number_current,service_id',
                'equipment.service:id,name',
                'company:id,name',
                'intervention:id,code,complaint_id,status,date_start',
            ])
            ->orderByDesc('id');

        if ($filters['company_id'] > 0) {
            $companyId = (int) $filters['company_id'];
            $query->where(function ($innerQuery) use ($companyId) {
                $innerQuery->where('company_id', $companyId)
                    ->orWhereHas('equipment', fn ($e) => $e->where('company_id', $companyId));
            });
        }
        if ($filters['service_id'] > 0) {
            $serviceId = (int) $filters['service_id'];
            $query->whereHas('equipment', fn ($e) => $e->where('service_id', $serviceId));
        }
        if ($filters['service_name'] !== '') {
            $query->where('service_name', 'like', '%' . $filters['service_name'] . '%');
        }
        if ($filters['status'] !== '') {
            $col = Schema::hasColumn('external_interventions', 'status') ? 'status' : 'intervention_status';
            $query->where($col, $filters['status']);
        }
        if ($filters['date_from'] !== '') {
            $query->whereDate('first_call_datetime', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] !== '') {
            $query->whereDate('first_call_datetime', '<=', $filters['date_to']);
        }
        if ($filters['q'] !== '') {
            $search = '%' . $filters['q'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', $search)
                  ->orWhere('technician_name', 'like', $search)
                  ->orWhere('service_name', 'like', $search)
                  ->orWhereHas('intervention', fn ($i) => $i->where('code', 'like', $search))
                  ->orWhereHas('equipment', fn ($e) => $e->where('designation', 'like', $search));
            });
        }

        $tickets = $query->paginate(20)->withQueryString();

        // KPI quick stats
        $kpiBase = ExternalIntervention::query();
        $statusCol = Schema::hasColumn('external_interventions', 'status') ? 'status' : 'intervention_status';
        $mttrHours = $this->calcMttr();
        $kpi = [
            'total'      => $kpiBase->count(),
            'ouverts'    => (clone $kpiBase)->whereIn($statusCol, ['ouvert', 'en_attente'])->count(),
            'en_cours'   => (clone $kpiBase)->where($statusCol, 'en_cours')->count(),
            'resolus'    => (clone $kpiBase)->whereIn($statusCol, ['resolu', 'resolved', 'termine', 'ferme'])->count(),
            'mttr_label' => $mttrHours !== null ? (number_format($mttrHours, 1, ',', ' ') . ' h') : '-',
        ];

        return view('pages.sav-tickets.index', [
            'tickets'   => $tickets,
            'filters'   => array_merge($filters, [
                'companyId' => $filters['company_id'],
                'serviceId' => $filters['service_id'],
                'dateFrom'  => $filters['date_from'],
                'dateTo'    => $filters['date_to'],
            ]),
            'kpi'       => $kpi,
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'services'  => Service::query()->orderBy('name')->get(['id', 'name']),
            'statuses'  => [
                'ouvert' => 'Ouvert',
                'en_cours' => 'En cours',
                'resolu' => 'Résolu',
                'ferme' => 'Fermé',
            ],
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  FORMULAIRE DE CRÉATION                                              */
    /* ------------------------------------------------------------------ */
    public function create()
    {
        $equipmentsQuery = Equipment::query()->with('service:id,name')->orderBy('designation');
        ServiceAccess::applyEquipmentScope($equipmentsQuery, Auth::user());

        return view('pages.sav-tickets.form', [
            'ticket'     => null,
            'companies'  => Company::query()->orderBy('name')->get(['id', 'name']),
            'equipments' => $equipmentsQuery->limit(5000)->get(),
            'interventions' => Intervention::query()->orderByDesc('id')->limit(300)->get(['id', 'code', 'date_start']),
            'services'   => Service::query()->excludeHiddenForUi()->orderBy('name')->get(['id', 'name', 'code']),
            'statuses'   => [
                'ouvert' => 'Ouvert',
                'en_cours' => 'En cours',
                'resolu' => 'Résolu',
                'ferme' => 'Fermé',
            ],
            'logs'       => collect(),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  ENREGISTREMENT CRÉATION                                             */
    /* ------------------------------------------------------------------ */
    public function store(Request $request)
    {
        $validated = $this->validateTicketInput($request);

        DB::beginTransaction();
        try {
            $ticketNumber = sprintf('SAV-%s-%05d', now()->format('Y'), (int)(ExternalIntervention::query()->max('id') + 1));

            $statusValue = (string) ($validated['status'] ?? 'ouvert');

            $interventionId = !empty($validated['intervention_id']) ? (int) $validated['intervention_id'] : null;
            if ($interventionId === null && Schema::hasColumn('external_interventions', 'intervention_id')) {
                $intervention = Intervention::query()->create([
                    'code'              => $this->generateInterventionCode(),
                    'equipment_id'      => (int) $validated['equipment_id'],
                    'type'              => 'Curative',
                    'maintenance_scope' => 'externe',
                    'status'            => 'en_cours',
                    'date_start'        => $validated['first_call_datetime'] ?? $validated['failure_datetime'] ?? now(),
                    'technician_name'   => $validated['technician_name'] ?? null,
                ]);
                $interventionId = (int) $intervention->id;
            }

            $ticketPayload = [
                'ticket_number'            => $ticketNumber,
                'equipment_id'             => (int) $validated['equipment_id'],
                'company_id'               => (int) $validated['company_id'],
                'service_name'             => $validated['service_name'] ?? null,
                'failure_datetime'         => $validated['failure_datetime'],
                'first_call_datetime'      => $validated['first_call_datetime'] ?? null,
                'arrival_datetime'         => $validated['arrival_datetime'] ?? null,
                'technician_arrival_datetime' => $validated['arrival_datetime'] ?? null,
                'intervention_description' => $validated['intervention_description'] ?? null,
                'replaced_parts'           => $validated['replaced_parts'] ?? null,
                'technician_name'          => $validated['technician_name'] ?? null,
                'resolution_datetime'      => $validated['resolution_datetime'] ?? null,
            ];

            if (Schema::hasColumn('external_interventions', 'intervention_id') && $interventionId !== null) {
                $ticketPayload['intervention_id'] = $interventionId;
            }
            if (Schema::hasColumn('external_interventions', 'status')) {
                $ticketPayload['status'] = $statusValue;
            }
            if (Schema::hasColumn('external_interventions', 'intervention_status')) {
                $ticketPayload['intervention_status'] = $statusValue;
            }

            $ticket = ExternalIntervention::query()->create($ticketPayload);

            $this->logAction($ticket, 'ticket_created_manual', null, $statusValue, $validated);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }

        return redirect()->route('sav-tickets.index')->with('success', 'Ticket SAV ' . $ticket->ticket_number . ' créé avec succès.');
    }

    /* ------------------------------------------------------------------ */
    /*  FORMULAIRE D'ÉDITION                                                */
    /* ------------------------------------------------------------------ */
    public function edit(ExternalIntervention $savTicket)
    {
        $savTicket->load([
            'equipment.service',
            'company',
            'intervention',
        ]);

        $equipmentsQuery = Equipment::query()->with('service:id,name')->orderBy('designation');
        ServiceAccess::applyEquipmentScope($equipmentsQuery, Auth::user());

        $logs = collect();
        if (Schema::hasTable('external_intervention_logs')) {
            $logs = ExternalInterventionLog::query()
                ->where('external_intervention_id', $savTicket->id)
                ->with('user:id,name')
                ->orderByDesc('logged_at')
                ->get();
        }

        return view('pages.sav-tickets.form', [
            'ticket'     => $savTicket,
            'companies'  => Company::query()->orderBy('name')->get(['id', 'name']),
            'equipments' => $equipmentsQuery->limit(5000)->get(),
            'interventions' => Intervention::query()->orderByDesc('id')->limit(300)->get(['id', 'code', 'date_start']),
            'services'   => Service::query()->excludeHiddenForUi()->orderBy('name')->get(['id', 'name', 'code']),
            'statuses'   => [
                'ouvert' => 'Ouvert',
                'en_cours' => 'En cours',
                'resolu' => 'Résolu',
                'ferme' => 'Fermé',
            ],
            'logs'       => $logs,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  ENREGISTREMENT MISE À JOUR                                          */
    /* ------------------------------------------------------------------ */
    public function update(Request $request, ExternalIntervention $savTicket)
    {
        $validated = $this->validateTicketInput($request, $savTicket->id);

        $previousStatus = (string) ($savTicket->status ?: $savTicket->intervention_status);
        $statusValue = (string) ($validated['status'] ?? $previousStatus ?: 'ouvert');

        DB::beginTransaction();
        try {
            $updatePayload = [
                'service_name'             => $validated['service_name']             ?? $savTicket->service_name,
                'failure_datetime'         => $validated['failure_datetime']         ?? $savTicket->failure_datetime,
                'first_call_datetime'      => $validated['first_call_datetime']      ?? $savTicket->first_call_datetime,
                'arrival_datetime'         => $validated['arrival_datetime']         ?? $savTicket->arrival_datetime,
                'technician_arrival_datetime' => $validated['arrival_datetime']      ?? $savTicket->technician_arrival_datetime,
                'intervention_description' => $validated['intervention_description'] ?? $savTicket->intervention_description,
                'replaced_parts'           => $validated['replaced_parts']           ?? $savTicket->replaced_parts,
                'technician_name'          => $validated['technician_name']          ?? $savTicket->technician_name,
                'resolution_datetime'      => $validated['resolution_datetime']      ?? $savTicket->resolution_datetime,
            ];

            if (Schema::hasColumn('external_interventions', 'status')) {
                $updatePayload['status'] = $statusValue;
            }
            if (Schema::hasColumn('external_interventions', 'intervention_status')) {
                $updatePayload['intervention_status'] = $statusValue;
            }

            $savTicket->update($updatePayload);

            // Close linked Intervention when ticket is resolved
            if (in_array($statusValue, ['resolu', 'ferme'], true) && $savTicket->intervention_id) {
                Intervention::where('id', $savTicket->intervention_id)
                    ->whereNotIn('status', ['termine', 'ferme'])
                    ->update([
                        'status'   => 'termine',
                        'date_end' => $savTicket->resolution_datetime ?? now(),
                    ]);
            }

            if ($previousStatus !== $statusValue) {
                $this->logAction($savTicket, 'status_changed', $previousStatus, $statusValue, []);
            } else {
                $this->logAction($savTicket, 'ticket_updated', $previousStatus, $statusValue, $validated);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }

        return redirect()->route('sav-tickets.index')->with('success', 'Ticket SAV mis à jour.');
    }

    /* ------------------------------------------------------------------ */
    /*  SUPPRESSION                                                         */
    /* ------------------------------------------------------------------ */
    public function destroy(ExternalIntervention $savTicket)
    {
        $this->logAction($savTicket, 'ticket_deleted', $savTicket->status, null, []);
        $savTicket->delete();

        return redirect()->route('sav-tickets.index')->with('success', 'Ticket SAV supprimé.');
    }

    /* ------------------------------------------------------------------ */
    /*  MÉTHODES PRIVÉES                                                    */
    /* ------------------------------------------------------------------ */
    private function validateTicketInput(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'equipment_id'             => ['required', 'integer', 'exists:equipments,id'],
            'company_id'               => ['required', 'integer', 'exists:companies,id'],
            'service_name'             => ['nullable', 'string', 'max:180'],
            'failure_datetime'         => ['nullable', 'date'],
            'first_call_datetime'      => ['nullable', 'date'],
            'arrival_datetime'         => ['nullable', 'date'],
            'intervention_description' => ['nullable', 'string'],
            'replaced_parts'           => ['nullable', 'string'],
            'technician_name'          => ['nullable', 'string', 'max:180'],
            'status'                   => ['required', 'in:ouvert,en_cours,resolu,ferme'],
            'resolution_datetime'      => ['nullable', 'date'],
            'intervention_id'          => ['nullable', 'integer', 'exists:interventions,id'],
        ]);
    }

    private function logAction(ExternalIntervention $ticket, string $action, ?string $from, ?string $to, array $payload): void
    {
        if (!Schema::hasTable('external_intervention_logs')) {
            return;
        }
        ExternalInterventionLog::query()->create([
            'external_intervention_id' => $ticket->id,
            'user_id'                  => Auth::id(),
            'action_type'              => $action,
            'from_status'              => $from,
            'to_status'                => $to,
            'payload'                  => $payload,
            'logged_at'                => now(),
        ]);
    }

    private function generateInterventionCode(): string
    {
        $last = Intervention::query()->whereYear('created_at', now()->year)->max('id') ?? 0;
        return sprintf('INT-%s-%05d', now()->format('Y'), (int)$last + 1);
    }

    private function calcMttr(): ?float
    {
        if (!Schema::hasColumn('external_interventions', 'intervention_duration_hours')) {
            return null;
        }
        $avg = ExternalIntervention::query()
            ->whereNotNull('intervention_duration_hours')
            ->where('intervention_duration_hours', '>', 0)
            ->avg('intervention_duration_hours');
        return $avg ? round((float)$avg, 1) : null;
    }
}
