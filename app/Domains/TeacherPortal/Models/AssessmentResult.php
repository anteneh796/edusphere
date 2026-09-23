<?php

namespace App\Domains\TeacherPortal\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Student;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentResult extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'classroom_assessment_id',
        'student_id',
        'marks_obtained',
        'status',
        'remarks',
        'entered_by_id',
    ];

    protected function casts(): array
    {
        return [
            'marks_obtained' => 'integer',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(ClassroomAssessment::class, 'classroom_assessment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by_id');
    }
}
