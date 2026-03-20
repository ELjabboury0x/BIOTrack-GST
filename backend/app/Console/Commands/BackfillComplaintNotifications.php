<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BackfillComplaintNotifications extends Command
{
    protected $signature = 'notifications:backfill-complaints {--dry-run : Preview without inserting}';

    protected $description = 'Copy existing complaint notifications to all admin, engineer, major and technician accounts.';

    public function handle(): int
    {
        $targetUsers = User::query()
            ->where('is_active', true)
            ->whereIn('role', ['admin', 'ingenieur', 'major', 'technicien', 'technician'])
            ->get(['id']);

        if ($targetUsers->isEmpty()) {
            $this->warn('No active admin/engineer/major/technician users found.');
            return self::SUCCESS;
        }

        $sourceQuery = DB::table('notifications')
            ->where('type', 'App\\Notifications\\ComplaintCreatedNotification')
            ->where('notifiable_type', User::class)
            ->orderBy('created_at');

        $sourceRows = $sourceQuery->get(['data', 'created_at', 'updated_at']);

        if ($sourceRows->isEmpty()) {
            $this->warn('No complaint notifications found to backfill.');
            return self::SUCCESS;
        }

        $sourceByComplaintId = [];
        foreach ($sourceRows as $row) {
            $payload = $this->decodePayload($row->data);
            $complaintId = (int) ($payload['complaint_id'] ?? 0);
            if ($complaintId <= 0) {
                continue;
            }

            if (!isset($sourceByComplaintId[$complaintId])) {
                $sourceByComplaintId[$complaintId] = [
                    'payload' => $payload,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            }
        }

        if (empty($sourceByComplaintId)) {
            $this->warn('No valid complaint payloads found in notifications.');
            return self::SUCCESS;
        }

        $recipientIds = $targetUsers->pluck('id')->map(fn ($id) => (int) $id)->all();

        $existingRows = DB::table('notifications')
            ->where('type', 'App\\Notifications\\ComplaintCreatedNotification')
            ->where('notifiable_type', User::class)
            ->whereIn('notifiable_id', $recipientIds)
            ->get(['notifiable_id', 'data']);

        $existingByRecipient = [];
        foreach ($existingRows as $row) {
            $recipientId = (int) $row->notifiable_id;
            $payload = $this->decodePayload($row->data);
            $complaintId = (int) ($payload['complaint_id'] ?? 0);
            if ($complaintId <= 0) {
                continue;
            }
            $existingByRecipient[$recipientId][$complaintId] = true;
        }

        $insertRows = [];
        foreach ($recipientIds as $recipientId) {
            foreach ($sourceByComplaintId as $complaintId => $source) {
                if (!empty($existingByRecipient[$recipientId][$complaintId])) {
                    continue;
                }

                $insertRows[] = [
                    'id' => (string) Str::uuid(),
                    'type' => 'App\\Notifications\\ComplaintCreatedNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $recipientId,
                    'data' => json_encode($source['payload'], JSON_UNESCAPED_UNICODE),
                    'read_at' => null,
                    'created_at' => $source['created_at'] ?? now(),
                    'updated_at' => $source['updated_at'] ?? now(),
                ];
            }
        }

        $toInsert = count($insertRows);
        $this->info('Targets: ' . count($recipientIds));
        $this->info('Unique complaints from source: ' . count($sourceByComplaintId));
        $this->info('Notifications to insert: ' . $toInsert);

        if ($toInsert === 0) {
            $this->info('Nothing to backfill (already synchronized).');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info('Dry-run mode: no data inserted.');
            return self::SUCCESS;
        }

        foreach (array_chunk($insertRows, 500) as $chunk) {
            DB::table('notifications')->insert($chunk);
        }

        $this->info('Backfill completed successfully.');

        return self::SUCCESS;
    }

    /**
     * @param mixed $raw
     * @return array<string, mixed>
     */
    private function decodePayload($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
