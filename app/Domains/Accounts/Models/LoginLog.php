<?php

namespace App\Domains\Accounts\Models;

use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'user_id',
        'event',
        'identifier',
        'ip_address',
        'user_agent',
        'device',
        'browser',
        'platform',
        'login_at',
        'logout_at',
        'last_activity_at',
        'session_id',
    ];

    protected function casts(): array
    {
        return [
            'login_at' => 'datetime',
            'logout_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
