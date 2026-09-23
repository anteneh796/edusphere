<?php

namespace App\Domains\TeacherPortal\Models;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassroomAssessment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'class_subject_id',
        'class_room_id',
        'teacher_id',
        'title',
        'type',
        'status',
        'total_marks',
        'assessment_date',
        'room',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'total_marks' => 'integer',
            'assessment_date' => 'date',
        ];
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(AssessmentResult::class, 'classroom_assessment_id');
    }
}
