<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Equipment;
use App\Models\MaintenanceReport;
use App\Models\Service;
use App\Models\User;
use App\Services\AppSettingsService;
use App\Services\AuditLogger;
use App\Services\DashboardMetricsService;
use App\Services\RealtimeMetricsBroadcaster;
use App\Support\ServiceAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ComplaintController extends Controller
{
    public function __construct(
        private DashboardMetricsService $dashboardMetricsService,
        private RealtimeMetricsBroadcaster $realtimeMetricsBroadcaster,
        private AuditLogger $auditLogger
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $selectedServiceId = $request->integer('service_id');
        $selectedStatus = $request->string('status')->toString();
        $perPage = max(5, min(200, app(AppSettingsService::class)->int('items_per_page', 20)));

        $query = Complaint::query()
            ->with([
                'service:id,code,name',
                'equipment:id,service_id,inventory_number_current,designation',
            ])
            ->latest('id');

        ServiceAccess::applyComplaintScope($query, $user);

        if ($selectedServiceId > 0) {
            $query->where('service_id', $selectedServiceId);
        }

        if (in_array($selectedStatus, ['open', 'in_progress', 'resolved'], true)) {
            $query->where('status', $selectedStatus);
        }

        $complaints = $query->paginate($perPage);
        $complaints->appends($request->query());

        $servicesQuery = Service::query()->excludeHiddenForUi()->select('id', 'name')->orderBy('name');
        if ($user && !$user->hasGlobalAccess()) {
            $serviceIds = $user->isUnitRestricted()
                ? $user->unitScopedServiceIds()
                : $user->allowedServiceIds();
            $servicesQuery->whereIn('id', $serviceIds);
        }

        return view('pages.reclamations', [
            'complaints' => $complaints,
            'services' => $servicesQuery->get(),
            'selectedServiceId' => $selectedServiceId,
            'selectedStatus' => $selectedStatus,
        ]);
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved'],
        ]);

        Gate::authorize('view-complaint', $complaint);

        $previousStatus = (string) $complaint->status;
        $wasResolved = $previousStatus === 'resolved';

        $complaint->update([
            'status' => $validated['status'],
        ]);

        $this->auditLogger->log('complaint.status.updated', $complaint, [
            'previous_status' => $previousStatus,
            'new_status' => $validated['status'],
        ], $request);

        if ($validated['status'] === 'resolved' && !$wasResolved) {
            try {
                $deletedCount = $complaint->deleteAttachments();
                if ($deletedCount > 0) {
                    Log::info('Complaint attachments deleted on closure', [
                        'complaint_id' => $complaint->id,
                        'files_deleted' => $deletedCount,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to delete complaint attachments', [
                    'complaint_id' => $complaint->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $prefilledReport = $this->createPrefilledReportFromComplaint($complaint, $request);

            if ($prefilledReport) {
                try {
                    $this->dashboardMetricsService->invalidateCache();
                } catch (\Throwable $e) {
                }

                return redirect()
                    ->route('maintenance-reports.edit', $prefilledReport)
                    ->with('success', 'Réclamation clôturée. Le rapport d\'intervention est prérempli automatiquement.');
            }
        }

        try {
            $this->dashboardMetricsService->invalidateCache();
        } catch (\Throwable $e) {
        }

        return redirect()
            ->route('reclamations.index')
            ->with('success', 'Statut de la réclamation mis à jour.');
    }

    private function createPrefilledReportFromComplaint(Complaint $complaint, Request $request): ?MaintenanceReport
    {
        $equipment = Equipment::query()->find((int) $complaint->equipment_id);
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
            $problemDescription = 'Réclamation #' . $complaint->id . ' clôturée depuis le module Réclamations.';
        }

        $operationsPerformed = 'Clôture de la réclamation depuis le module Réclamations.';

        $report = new MaintenanceReport([
            'intervention_type' => MaintenanceReport::TYPE_CURATIVE,
            'status' => MaintenanceReport::STATUS_DRAFT,
            'intervention_date' => now()->toDateString(),
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
}
