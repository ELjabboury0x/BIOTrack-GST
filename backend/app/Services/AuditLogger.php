<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogger
{
    public function log(string $action, mixed $subject = null, array $meta = [], ?Request $request = null): void
    {
        $actor = auth()->user();

        AuditLog::query()->create([
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $this->resolveSubjectType($subject),
            'subject_id' => $this->resolveSubjectId($subject),
            'meta' => $meta,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? substr((string) $request->userAgent(), 0, 255) : null,
        ]);
    }

    private function resolveSubjectType(mixed $subject): ?string
    {
        if (is_object($subject)) {
            return get_class($subject);
        }

        if (is_string($subject) && $subject !== '') {
            return $subject;
        }

        return null;
    }

    private function resolveSubjectId(mixed $subject): ?int
    {
        if (is_object($subject) && isset($subject->id)) {
            return (int) $subject->id;
        }

        if (is_array($subject) && isset($subject['id'])) {
            return (int) $subject['id'];
        }

        if (is_numeric($subject)) {
            return (int) $subject;
        }

        return null;
    }
}
