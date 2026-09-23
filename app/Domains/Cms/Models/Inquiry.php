<?php

namespace App\Domains\Cms\Models;

use App\Domains\Accounts\Models\User;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inquiry extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'inquiries';

    protected $fillable = [
        'type',
        'full_name',
        'email',
        'phone',
        'student_name',
        'grade_level',
        'message',
        'status',
        'handled_at',
        'handled_by',
    ];

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
        ];
    }

    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    /* -------------------------------- Relations -------------------------------- */

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopeUnhandled(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }
}
