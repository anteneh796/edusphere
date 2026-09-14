<?php

namespace App\Domains\Academics\Models;

use App\Domains\Accounts\Models\User;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ClassSubject extends Pivot
{
    use HasUuid;

    protected $table = 'class_subject';

    protected $casts = [
        'periods_per_week' => 'integer',
        'position' => 'integer',
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
}
