<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:2000'],
            'keys.p256dh' => ['required', 'string', 'max:300'],
            'keys.auth' => ['required', 'string', 'max:300'],
            'contentEncoding' => ['nullable', 'string', 'max:50'],
        ]);

        $endpoint = trim((string) $validated['endpoint']);
        $endpointHash = hash('sha256', $endpoint);

        PushSubscription::query()->updateOrCreate(
            [
                'user_id' => (int) $request->user()->id,
                'endpoint_hash' => $endpointHash,
            ],
            [
                'endpoint' => $endpoint,
                'public_key' => (string) data_get($validated, 'keys.p256dh', ''),
                'auth_token' => (string) data_get($validated, 'keys.auth', ''),
                'content_encoding' => (string) ($validated['contentEncoding'] ?? 'aesgcm'),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'ok' => true,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:2000'],
        ]);

        $endpointHash = hash('sha256', trim((string) $validated['endpoint']));

        PushSubscription::query()
            ->where('user_id', (int) $request->user()->id)
            ->where('endpoint_hash', $endpointHash)
            ->delete();

        return response()->json([
            'ok' => true,
        ]);
    }
}
