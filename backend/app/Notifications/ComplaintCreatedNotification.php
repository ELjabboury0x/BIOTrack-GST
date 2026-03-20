<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplaintCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private Complaint $complaint)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'complaint_created',
            'complaint_id' => $this->complaint->id,
            'service_id' => $this->complaint->service_id,
            'service_name' => (string) ($this->complaint->service?->name ?? '-'),
            'equipment_id' => $this->complaint->equipment_id,
            'equipment_label' => trim((string) ($this->complaint->equipment?->inventory_number_current ?? '') . ' ' . (string) ($this->complaint->equipment?->designation ?? '')),
            'priority' => $this->complaint->priority,
            'status' => $this->complaint->status,
            'reported_by_name' => $this->complaint->reported_by_name,
            'description' => mb_substr((string) $this->complaint->description, 0, 180),
            'created_at' => optional($this->complaint->created_at)->toDateTimeString(),
        ];
    }
}
