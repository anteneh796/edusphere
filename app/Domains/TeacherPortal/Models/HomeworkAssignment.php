<?php

namespace App\Domains\TeacherPortal\Models;

use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeworkAssignment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'class_subject_id',
        'teacher_id',
        'title',
        'instructions',
        'assigned_on',
        'due_on',
        'max_marks',
        'visibility',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'assigned_on' => 'date',
            'due_on' => 'date',
            'max_marks' => 'integer',
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

    public function submissions(): HasMany
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    public function submissionCount(): int
    {
        return $this->submissions()->count();
    }
}
