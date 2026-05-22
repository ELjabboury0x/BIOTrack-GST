<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ExternalCompanyPlanning;
use App\Models\ExternalIntervention;
use App\Models\ExternalInterventionLog;
use App\Models\Intervention;
use App\Services\AuditLogger;
use App\Services\AppSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DashboardNotificationController extends Controller
{
    public function complaints(Request $request): JsonResponse
    {
        ExternalCompanyPlanning::syncAutomaticStatuses();

        $settings = app(AppSettingsService::class);
        $urgentEnabled = $settings->bool('notifications_urgent_interventions', true);
        $digestHour = max(0, min(23, $settings->int('notification_digest_hour', 8)));

        // Réclamations/notifications visibles pour admin + ingénieur + major + techniciens
        $role = $request->user()?->role;
        if (!in_array($role, ['admin', 'ingenieur', 'major', 'technicien', 'technician'], true)) {
            return response()->json([
                'count' => 0,
                'items' => [],
                'urgent_enabled' => false,
                'digest_hour' => $digestHour,
                'digest_due_now' => now()->hour === $digestHour,
            ]);
        }

        if (!$urgentEnabled) {
            return response()->json([
                'count' => 0,
                'items' => [],
                'urgent_enabled' => false,
                'digest_hour' => $digestHour,
                'digest_due_now' => now()->hour === $digestHour,
            ]);
        }

        $notificationsCollection = $request->user()
            ->unreadNotifications()
            ->where('type', 'App\\Notifications\\ComplaintCreatedNotification')
            ->latest('created_at')
            ->limit(10)
            ->get();

        $complaintIds = $notificationsCollection
            ->pluck('data.complaint_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $complaintsById = Complaint::query()
            ->whereIn('id', $complaintIds)
            ->get(['id', 'status', 'attachment_path'])
            ->keyBy('id');

        $notifications = $notificationsCollection
            ->map(function ($notification) use ($complaintsById) {
                $complaintId = (int) data_get($notification->data, 'complaint_id', 0);
                $complaint = $complaintsById->get($complaintId);

                if (!$complaint) {
                    $priority = Complaint::normalizePriorityKey((string) data_get($notification->data, 'priority', 'normal'));

                    return [
                        'id' => $notification->id,
                        'title' => 'Ancienne réclamation',
                        'complaint_id' => $complaintId,
                        'service_name' => data_get($notification->data, 'service_name', '-'),
                        'equipment_label' => data_get($notification->data, 'equipment_label', '-'),
                        'priority' => $priority,
                        'reported_by_name' => data_get($notification->data, 'reported_by_name', '-'),
                        'status' => (string) data_get($notification->data, 'status', 'open'),
                        'attachment_image_url' => null,
                        'open_url' => route('dashboard.notifications.complaints.archive', ['notificationId' => $notification->id]),
                        'created_at' => optional($notification->created_at)->diffForHumans(),
                    ];
                }

                $attachments = collect($complaint?->attachment_path ?? [])
                    ->filter(fn ($path) => is_string($path) && trim($path) !== '')
                    ->values();

                $attachmentImageUrl = null;
                if ($attachments->isNotEmpty() && Route::has('dashboard.notifications.complaints.attachment')) {
                    $attachmentImageUrl = route('dashboard.notifications.complaints.attachment', [
                        'complaint' => $complaintId,
                        'index' => 0,
                    ]);
                }

                $priority = Complaint::normalizePriorityKey((string) data_get($notification->data, 'priority', 'normal'));

                return [
                    'id' => $notification->id,
                    'title' => 'Nouvelle réclamation',
                    'complaint_id' => $complaintId,
                    'service_name' => data_get($notification->data, 'service_name', '-'),
                    'equipment_label' => data_get($notification->data, 'equipment_label', '-'),
                    'priority' => $priority,
                    'reported_by_name' => data_get($notification->data, 'reported_by_name', '-'),
                    'status' => (string) ($complaint?->status ?? data_get($notification->data, 'status', 'open')),
                    'attachment_image_url' => $attachmentImageUrl,
                    'open_url' => route('dashboard.notifications.complaints.show', $complaintId),
                    'created_at' => optional($notification->created_at)->diffForHumans(),
                ];
            })
            ->values();

        // Rappels automatiques: plannings externes proches (J-14)
        $today = now()->startOfDay();
        $upcomingLimit = $today->copy()->addDays(14);

        $planningReminders = ExternalCompanyPlanning::query()
            ->with('company:id,name')
            ->whereDate('planned_date', '>=', $today->toDateString())
            ->whereDate('planned_date', '<=', $upcomingLimit->toDateString())
            ->where('status', '!=', 'termine')
            ->orderBy('planned_date')
            ->orderBy('id')
            ->limit(10)
            ->get()
            ->map(function (ExternalCompanyPlanning $planning) use ($today) {
                $daysLeft = $planning->planned_date
                    ? $today->diffInDays($planning->planned_date, false)
                    : null;

                $status = ($daysLeft !== null && $daysLeft <= 2) ? 'urgent' : 'scheduled';

                return [
                    'id' => 'planning-' . $planning->id . '-' . optional($planning->planned_date)->format('Ymd'),
                    'type' => 'planning_reminder',
                    'title' => 'Rappel planning externe',
                    'service_name' => $planning->company?->name ?: 'Société externe',
                    'equipment_label' => (string) ($planning->description ?: 'Maintenance préventive planifiée'),
                    'priority' => $daysLeft !== null && $daysLeft <= 2 ? 'urgent' : 'normal',
                    'reported_by_name' => $planning->contact_person ?: '-',
                    'status' => $status,
                    'attachment_image_url' => null,
                    'open_url' => route('maintenance-preventive', [
                        'company_id' => (int) ($planning->company_id ?? 0),
                        'date_from' => optional($planning->planned_date)->format('Y-m-d'),
                        'date_to' => optional($planning->planned_date)->format('Y-m-d'),
                    ]),
                    'created_at' => ($daysLeft !== null)
                        ? ($daysLeft === 0 ? 'Aujourd\'hui' : ('Dans ' . max(0, $daysLeft) . ' jour(s)'))
                        : '-',
                ];
            })
            ->values();

        $items = $notifications
            ->concat($planningReminders)
            ->sortBy(function (array $item) {
                $status = (string) ($item['status'] ?? '');
                if ($status === 'urgent') {
                    return 0;
                }

                if ($status === 'open') {
                    return 1;
                }

                return 2;
            })
            ->take(10)
            ->values();

        return response()->json([
            'count' => $items->count(),
            'items' => $items,
            'urgent_enabled' => true,
            'digest_hour' => $digestHour,
            'digest_due_now' => now()->hour === $digestHour,
        ]);
    }

    public function markAllComplaintAsRead(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications()
            ->where('type', 'App\\Notifications\\ComplaintCreatedNotification')
            ->update(['read_at' => now()]);

        return response()->json([
            'ok' => true,
        ]);
    }

    public function showComplaint(Request $request, Complaint $complaint)
    {
        if (!$this->canAccessComplaint($request, $complaint)) {
            abort(403, 'Accès refusé à cette notification.');
        }

        $request->user()
            ->unreadNotifications()
            ->where('type', 'App\\Notifications\\ComplaintCreatedNotification')
            ->where('data->complaint_id', $complaint->id)
            ->update(['read_at' => now()]);

        $complaint->load([
            'service:id,code,name',
            'equipment:id,inventory_number_current,designation,service_id,company_id',
        ]);

        return view('pages.notifications.complaint-show', [
            'complaint' => $complaint,
        ]);
    }

    public function showArchivedComplaint(Request $request, string $notificationId)
    {
        $role = $request->user()?->role;
        if (!in_array($role, ['admin', 'ingenieur', 'technicien', 'technician'], true)) {
            abort(403, 'Accès refusé à cette notification.');
        }

        $notification = $request->user()
            ->notifications()
            ->where('type', 'App\\Notifications\\ComplaintCreatedNotification')
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return redirect()
                ->route('reclamations.index')
                ->with('error', 'Ancienne notification de réclamation introuvable.');
        }

        if (!$notification->read_at) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return view('pages.notifications.complaint-show-archived', [
            'priorityLabel' => Complaint::priorityLabel((string) data_get($notification->data, 'priority', 'normal')),
            'notificationData' => [
                'complaint_id' => (int) data_get($notification->data, 'complaint_id', 0),
                'service_name' => (string) data_get($notification->data, 'service_name', '-'),
                'equipment_label' => (string) data_get($notification->data, 'equipment_label', '-'),
                'priority' => Complaint::normalizePriorityKey((string) data_get($notification->data, 'priority', 'normal')),
                'status' => (string) data_get($notification->data, 'status', 'open'),
                'reported_by_name' => (string) data_get($notification->data, 'reported_by_name', '-'),
                'description' => (string) data_get($notification->data, 'description', ''),
                'created_at' => optional($notification->created_at)->format('d/m/Y H:i'),
            ],
        ]);
    }

    public function attachment(Request $request, Complaint $complaint, int $index)
    {
        if (!$this->canAccessComplaint($request, $complaint)) {
            abort(403, 'Accès refusé à cette pièce jointe.');
        }

        $attachment = collect($complaint->attachment_path ?? [])
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->values()
            ->get($index);

        if (!is_string($attachment) || trim($attachment) === '') {
            abort(404);
        }

        if (filter_var($attachment, FILTER_VALIDATE_URL)) {
            return redirect()->away($attachment);
        }

        $resolvedPath = $this->resolveAttachmentStoragePath($attachment);
        if (!$resolvedPath) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($resolvedPath), [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function closeComplaint(Request $request, Complaint $complaint)
    {
        if (!$this->canAccessComplaint($request, $complaint)) {
            abort(403, 'Accès refusé à cette notification.');
        }

        if ($complaint->status === 'resolved') {
            return redirect()
                ->route('dashboard.notifications.complaints.show', $complaint)
                ->with('success', 'Cette réclamation est déjà clôturée.');
        }

        $validated = $request->validate([
            'external_call_made' => ['nullable', 'boolean'],
        ]);

        $externalCallMade = (bool) ($validated['external_call_made'] ?? false);
        $warningMessage = null;

        if ($externalCallMade) {
            $equipmentCompanyId = (int) DB::table('equipments')->where('id', (int) $complaint->equipment_id)->value('company_id');
            if ($equipmentCompanyId <= 0) {
                $externalCallMade = false;
                $warningMessage = 'Cet équipement n\'est lié à aucune société externe. L\'option "appel effectué" a été ignorée et l\'OT/DM a été créé en mode interne.';
            }
        }

        $hasComplaintColumn = Schema::hasColumn('interventions', 'complaint_id');

        $existingInterventionQuery = Intervention::query()
            ->whereIn('status', ['en_attente', 'en_cours'])
            ->where('type', 'Curative')
            ->where('equipment_id', (int) $complaint->equipment_id);

        if ($hasComplaintColumn) {
            $existingInterventionQuery->where('complaint_id', $complaint->id);
        }

        $existingIntervention = $existingInterventionQuery
            ->latest('id')
            ->first();

        if ($existingIntervention) {
            return redirect()
                ->route('interventions.close.form', $existingIntervention->id)
                ->with('success', 'Un OT/DM est déjà en cours pour cette réclamation. Veuillez le compléter.');
        }

        $interventionPayload = [
            'code' => $this->generateInterventionCode(),
            'equipment_id' => (int) $complaint->equipment_id,
            'technician_name' => $request->user()?->name ?: $request->user()?->login,
            'type' => 'Curative',
            'maintenance_scope' => $externalCallMade ? 'externe' : 'interne',
            'status' => 'en_cours',
            'date_start' => now()->toDateString(),
        ];

        if ($hasComplaintColumn) {
            $interventionPayload['complaint_id'] = $complaint->id;
        }

        $intervention = Intervention::create($interventionPayload);

        if ($externalCallMade) {
            $this->createExternalTicketFromNotification($intervention, $complaint);
        }

        $complaint->update([
            'status' => 'in_progress',
        ]);

        app(AuditLogger::class)->log('complaint.closed.from.notification', $complaint, [
            'created_intervention_id' => (int) $intervention->id,
            'new_status' => 'in_progress',
        ], $request);

        $request->user()
            ->unreadNotifications()
            ->where('type', 'App\\Notifications\\ComplaintCreatedNotification')
            ->where('data->complaint_id', $complaint->id)
            ->update(['read_at' => now()]);

        $redirect = redirect()
            ->route('interventions.close.form', $intervention->id)
            ->with('success', 'OT/DM créé à partir de la réclamation. Veuillez saisir le compte-rendu et clôturer l\'intervention.');

        if ($warningMessage !== null) {
            $redirect->with('warning', $warningMessage);
        }

        return $redirect;
    }

    private function createExternalTicketFromNotification(Intervention $intervention, Complaint $complaint): void
    {
        if ($intervention->externalIntervention()->exists()) {
            return;
        }

        $equipment = DB::table('equipments')
            ->where('id', (int) $complaint->equipment_id)
            ->first(['id', 'company_id', 'service_id']);

        if (!$equipment || (int) ($equipment->company_id ?? 0) <= 0) {
            return;
        }

        $serviceName = null;
        if ((int) ($equipment->service_id ?? 0) > 0) {
            $serviceName = (string) DB::table('services')->where('id', (int) $equipment->service_id)->value('name');
            $serviceName = trim($serviceName) !== '' ? trim($serviceName) : null;
        }

        $statusValue = 'ouvert';
        $eventDateTime = now();

        $ticketPayload = [
            'intervention_id' => (int) $intervention->id,
            'equipment_id' => (int) $equipment->id,
            'company_id' => (int) $equipment->company_id,
            'service_name' => $serviceName,
            'failure_datetime' => $eventDateTime,
            'first_call_datetime' => $eventDateTime,
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
                'action_type' => 'ticket_created_from_notification',
                'from_status' => null,
                'to_status' => $statusValue,
                'payload' => [
                    'source' => 'complaint_notification_page',
                    'complaint_id' => (int) $complaint->id,
                    'intervention_id' => (int) $intervention->id,
                ],
                'logged_at' => now(),
            ]);
        }
    }

    private function canAccessComplaint(Request $request, Complaint $complaint): bool
    {
        $user = $request->user();

        if (!$user) {
            return false;
        }

        if ($user->isMajor()) {
            return in_array((int) $complaint->service_id, $user->allowedServiceIds(), true);
        }

        return true;
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

    private function resolveAttachmentStoragePath(string $rawPath): ?string
    {
        $normalized = str_replace('\\', '/', ltrim(trim($rawPath), '/'));
        if ($normalized === '') {
            return null;
        }

        $candidates = [$normalized];

        if (str_starts_with($normalized, 'public/')) {
            $candidates[] = substr($normalized, 7);
        }

        if (str_starts_with($normalized, 'storage/public/')) {
            $candidates[] = substr($normalized, 15);
        }

        if (str_starts_with($normalized, 'storage/')) {
            $candidates[] = substr($normalized, 8);
        }

        $basename = basename($normalized);
        if ($basename !== '' && $basename !== $normalized) {
            $candidates[] = 'complaints/' . $basename;
        }

        foreach (array_values(array_unique(array_filter($candidates, fn ($value) => is_string($value) && trim($value) !== ''))) as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
