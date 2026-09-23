<?php

namespace App\Domains\TeacherPortal\Models;

use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurriculumUnit extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'class_subject_id',
        'teacher_id',
        'position',
        'title',
        'description',
        'total_lessons',
        'covered_lessons',
        'status',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'total_lessons' => 'integer',
            'covered_lessons' => 'integer',
            'started_at' => 'date',
            'completed_at' => 'date',
        ];
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
