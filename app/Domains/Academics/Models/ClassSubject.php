<?php

namespace App\Domains\Academics\Models;

use App\Domains\Accounts\Models\User;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ClassSubject extends Pivot
{
    use HasFactory, HasUuid;

    protected $table = 'class_subject';

    protected $fillable = [
        'class_room_id',
        'subject_id',
        'teacher_id',
        'periods_per_week',
        'position',
        'is_homeroom',
    ];

    protected $casts = [
        'periods_per_week' => 'integer',
        'position' => 'integer',
        'is_homeroom' => 'boolean',
    ];

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function scopeAssignedTo(Builder $query, User $teacher): Builder
    {
        return $query->where('teacher_id', $teacher->getKey());
    }

    public function scopeHomeroom(Builder $query): Builder
    {
        return $query->where('is_homeroom', true);
    }
}
