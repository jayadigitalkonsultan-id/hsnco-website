<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class SecurityAudit
{
    public function record(string $event, string $outcome, ?User $user = null, ?Request $request = null, ?string $reason = null, array $metadata = [], ?Model $subject = null): void
    {
        $request ??= request();
        AuditLog::query()->create([
            'user_id' => $user?->id,
            'event' => $event,
            'outcome' => $outcome,
            'reason' => $reason,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 1000) : null,
            'device' => $this->device($request?->userAgent()),
            'metadata' => $metadata,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
        ]);
    }

    private function device(?string $agent): ?string
    {
        if (! $agent) return null;
        return str_contains($agent, 'Mobile') ? 'mobile browser' : 'browser';
    }
}
