<?php

namespace App\Listeners;

use App\Events\ComplaintCreated;
use App\Models\User;
use App\Notifications\ComplaintCreatedNotification;
use App\Services\AppSettingsService;
use App\Services\WebPushNotificationService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendComplaintCreatedNotification
{
    public function handle(ComplaintCreated $event): void
    {
        $settings = app(AppSettingsService::class);
        if (!$settings->bool('notifications_urgent_interventions', true)) {
            return;
        }

        $complaint = $event->complaint;

        $recipients = User::query()
            ->where('is_active', true)
            ->whereIn('role', ['admin', 'ingenieur', 'technicien', 'technician'])
            ->get()
            ->unique('id');

        if ($recipients->isEmpty()) {
            return;
        }

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new ComplaintCreatedNotification($complaint));
            } catch (Throwable $exception) {
                Log::warning('Complaint database notification failed', [
                    'recipient_id' => (int) $recipient->id,
                    'complaint_id' => (int) $complaint->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        try {
            app(WebPushNotificationService::class)->sendToUsers($recipients, [
                'title' => 'Nouvelle réclamation GMAO',
                'body' => trim((string) ($complaint->service?->name ?? 'Service') . ' • ' . (string) ($complaint->reported_by_name ?? 'Utilisateur')),
                'url' => route('dashboard.notifications.complaints.show', $complaint, false),
                'tag' => 'complaint-' . $complaint->id,
                'data' => [
                    'complaint_id' => (int) $complaint->id,
                    'service_id' => (int) $complaint->service_id,
                ],
            ]);
        } catch (Throwable $exception) {
            Log::warning('Complaint web push dispatch failed', [
                'complaint_id' => (int) $complaint->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
