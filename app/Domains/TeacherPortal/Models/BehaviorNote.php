<?php

namespace App\Domains\TeacherPortal\Models;

use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Student;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BehaviorNote extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'class_subject_id',
        'type',
        'severity',
        'recorded_on',
        'note',
        'action_taken',
        'visibility',
    ];

    protected function casts(): array
    {
        return [
            'recorded_on' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }
}
