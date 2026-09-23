<?php

namespace App\Domains\Approvals\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\ApprovalStatus;
use App\Support\Enums\ApprovalType;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequest extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'type',
        'status',
        'subject_type',
        'subject_id',
        'requested_by_id',
        'reviewed_by_id',
        'reason',
        'reviewer_note',
        'data',
        'submitted_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function subject()
    {
        return $this->morphTo();
    }

    /* ---------------------------------- Helpers --------------------------------- */

    public function typeEnum(): ?ApprovalType
    {
        return ApprovalType::tryFrom($this->type);
    }

    public function statusEnum(): ?ApprovalStatus
    {
        return ApprovalStatus::tryFrom($this->status);
    }

    public function typeLabel(): string
    {
        return $this->typeEnum()?->label() ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function statusBadgeColor(): string
    {
        return $this->statusEnum()?->badgeColor() ?? 'neutral';
    }

    public function reviewerTitle(): string
    {
        return $this->typeEnum()?->reviewerRole()->label() ?? 'Approval';
    }

    public function isPending(): bool
    {
        return $this->status === ApprovalStatus::Pending->value;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ApprovalStatus::Pending->value);
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('type', $type) : $query;
    }

    public function scopeOfStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }
}
