<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class WebPushNotificationService
{
    public function sendToUsers(Collection $users, array $payload): void
    {
        if ($users->isEmpty()) {
            return;
        }

        if (!extension_loaded('curl')) {
            Log::warning('WebPush skipped: curl extension is not loaded.');
            return;
        }

        $vapidPublic = trim((string) config('webpush.vapid.public_key', ''));
        $vapidPrivate = trim((string) config('webpush.vapid.private_key', ''));
        $vapidSubject = trim((string) config('webpush.vapid.subject', 'mailto:admin@example.com'));

        if ($vapidPublic === '' || $vapidPrivate === '') {
            return;
        }

        $userIds = $users->pluck('id')->map(fn ($id) => (int) $id)->unique()->values();
        if ($userIds->isEmpty()) {
            return;
        }

        $subscriptions = PushSubscription::query()
            ->whereIn('user_id', $userIds)
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => $vapidSubject,
                    'publicKey' => $vapidPublic,
                    'privateKey' => $vapidPrivate,
                ],
            ], [
                'TTL' => 300,
            ]);
        } catch (Throwable $exception) {
            Log::warning('WebPush initialization failed', [
                'message' => $exception->getMessage(),
            ]);
            return;
        }

        $title = (string) ($payload['title'] ?? 'Notification GMAO');
        $body = (string) ($payload['body'] ?? 'Nouvelle mise à jour');
        $url = (string) ($payload['url'] ?? '/dashboard');
        $tag = (string) ($payload['tag'] ?? 'gmao-notification');
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        $notificationPayload = json_encode([
            'title' => $title,
            'body' => $body,
            'icon' => '/favicon.svg',
            'badge' => '/favicon.svg',
            'tag' => $tag,
            'url' => $url,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);

        if (!is_string($notificationPayload) || $notificationPayload === '') {
            Log::warning('WebPush payload encoding failed');
            return;
        }

        try {
            foreach ($subscriptions as $subscriptionRecord) {
                $subscription = Subscription::create([
                    'endpoint' => (string) $subscriptionRecord->endpoint,
                    'publicKey' => (string) $subscriptionRecord->public_key,
                    'authToken' => (string) $subscriptionRecord->auth_token,
                    'contentEncoding' => (string) ($subscriptionRecord->content_encoding ?: 'aesgcm'),
                ]);

                $webPush->queueNotification($subscription, $notificationPayload);
            }

            foreach ($webPush->flush() as $report) {
                $endpoint = (string) $report->getRequest()->getUri();
                $endpointHash = hash('sha256', $endpoint);

                if ($report->isSuccess()) {
                    PushSubscription::query()
                        ->where('endpoint_hash', $endpointHash)
                        ->update(['last_used_at' => now()]);
                    continue;
                }

                $response = method_exists($report, 'getResponse') ? $report->getResponse() : null;
                $statusCode = $response ? (int) $response->getStatusCode() : 0;

                if (in_array($statusCode, [404, 410], true)) {
                    PushSubscription::query()->where('endpoint_hash', $endpointHash)->delete();
                }

                Log::warning('WebPush notification failed', [
                    'endpoint' => $endpoint,
                    'status' => $statusCode,
                    'reason' => method_exists($report, 'getReason') ? $report->getReason() : null,
                ]);
            }
        } catch (Throwable $exception) {
            Log::warning('WebPush send failed', [
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
