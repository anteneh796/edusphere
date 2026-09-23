<?php

namespace App\Domains\Admissions\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\CommunicationType;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionCommunication extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'application_id',
        'type',
        'direction',
        'contact',
        'subject',
        'message',
        'occurred_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    public function typeLabel(): string
    {
        return CommunicationType::tryFrom($this->type)?->label() ?? ucfirst($this->type ?? '');
    }

    public function directionLabel(): string
    {
        return $this->direction === 'inbound' ? 'Inbound' : 'Outbound';
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
