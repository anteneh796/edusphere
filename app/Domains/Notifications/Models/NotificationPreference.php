<?php

namespace App\Domains\Notifications\Models;

use App\Domains\Accounts\Models\User;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'notify_email',
        'notify_sms',
        'notify_push',
    ];

    protected function casts(): array
    {
        return [
            'notify_email' => 'boolean',
            'notify_sms' => 'boolean',
            'notify_push' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
