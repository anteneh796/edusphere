<?php

namespace App\Domains\Notifications\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\NotificationCategory;
use App\Support\Enums\NotificationPriority;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'category',
        'priority',
        'icon',
        'redirect_url',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ---------------------------------- Helpers --------------------------------- */

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markRead(): void
    {
        $this->updateQuietly(['read_at' => $this->read_at ?? now()]);
    }

    public function categoryLabel(): string
    {
        return NotificationCategory::tryFrom($this->category ?? 'system')?->label()
            ?? ucfirst($this->category ?? 'System');
    }

    public function priorityBadgeColor(): string
    {
        return NotificationPriority::tryFrom($this->priority ?? 'low')?->badgeColor() ?? 'neutral';
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function scopePriority(Builder $query, ?string $priority): Builder
    {
        return $priority ? $query->where('priority', $priority) : $query;
    }
}
