<?php

namespace App\Domains\Admissions\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\AdmissionAssessmentStatus;
use App\Support\Enums\AdmissionAssessmentType;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionAssessment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'application_id',
        'type',
        'scheduled_at',
        'location',
        'status',
        'conducted_at',
        'score',
        'notes',
        'conducted_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'conducted_at' => 'datetime',
            'score' => 'integer',
        ];
    }

    public function typeLabel(): string
    {
        return AdmissionAssessmentType::tryFrom($this->type)?->label() ?? ucfirst($this->type ?? '');
    }

    public function statusLabel(): string
    {
        return AdmissionAssessmentStatus::tryFrom($this->status)?->label() ?? ucfirst($this->status ?? '');
    }

    public function statusBadgeColor(): string
    {
        return AdmissionAssessmentStatus::tryFrom($this->status)?->badgeColor() ?? 'neutral';
    }

    public function isOpen(): bool
    {
        return $this->status === AdmissionAssessmentStatus::Scheduled->value;
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class);
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
