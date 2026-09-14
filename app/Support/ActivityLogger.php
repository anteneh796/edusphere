<?php

namespace App\Support;

use App\Domains\Accounts\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Persist an audit entry for a sensitive action.
     */
    public static function log(string $action, string $module, ?string $recordId = null, ?array $meta = null, ?string $userId = null): void
    {
        $user = $userId ? (string) $userId : optional(auth()->user())->getKey();

        AuditLog::create([
            'user_id' => $user,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'meta' => $meta,
            'ip_address' => Request::ip(),
            'user_agent' => substr(Request::userAgent() ?? 'unknown', 0, 500),
        ]);
    }
}
