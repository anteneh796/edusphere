<?php

namespace App\Domains\Exams\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Student;
use App\Support\GradeScale;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'exam_subject_id',
        'student_id',
        'marks_obtained',
        'grade',
        'remarks',
        'entered_by_id',
    ];

    protected function casts(): array
    {
        return [
            'marks_obtained' => 'decimal:2',
        ];
    }

    public function percentage(): ?float
    {
        if ($this->marks_obtained === null) {
            return null;
        }

        $max = (float) ($this->examSubject?->max_marks ?? 100);

        return $max > 0 ? round(((float) $this->marks_obtained / $max) * 100, 1) : null;
    }

    public function gradeColor(): string
    {
        return $this->grade ? GradeScale::badgeColor($this->grade) : 'neutral';
    }

    /* -------------------------------- Relations -------------------------------- */

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
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
