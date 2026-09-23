<?php

namespace App\Domains\HumanResources\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\LeaveRequestStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days',
        'reason',
        'status',
        'submitted_at',
        'submitted_by_id',
        'reviewed_by_id',
        'reviewed_at',
        'reviewed_note',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'days' => 'integer',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    /* ---------------------------------- Helpers --------------------------------- */

    public function statusEnum(): ?LeaveRequestStatus
    {
        return LeaveRequestStatus::tryFrom($this->status);
    }

    public function statusLabel(): string
    {
        return $this->statusEnum()?->label() ?? ucfirst($this->status);
    }

    public function statusBadgeColor(): string
    {
        return $this->statusEnum()?->badgeColor() ?? 'neutral';
    }

    public function isOpen(): bool
    {
        return $this->statusEnum()?->isOpen() ?? false;
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopeOfStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', LeaveRequestStatus::Approved->value);
    }
}