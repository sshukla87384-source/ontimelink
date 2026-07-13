<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    /**
     * IPs are stored as salted hashes: enough to correlate abuse,
     * without keeping raw PII in the database.
     */
    public static function hashIp(?string $ip): ?string
    {
        return $ip === null ? null : hash_hmac('sha256', $ip, config('app.key'));
    }

    public function log(
        string $event,
        string $category = 'activity',
        ?Model $subject = null,
        array $context = [],
        ?int $userId = null,
        ?Request $request = null,
    ): void {
        $request ??= request();

        AuditLog::create([
            'user_id' => $userId ?? $request->user()?->id,
            'event' => $event,
            'category' => $category,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'context' => $context ?: null,
            'ip_hash' => self::hashIp($request->ip()),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }
}
