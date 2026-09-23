<?php

namespace App\Domains\Students\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEnrollment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'grade_level_id',
        'class_room_id',
        'roll_number',
        'status',
        'result',
        'enrolled_at',
        'left_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'date',
            'left_at' => 'date',
            'roll_number' => 'integer',
        ];
    }

    public function scopeRollAssignable(Builder $query, string $classRoomId, string $academicYearId): Builder
    {
        return $query
            ->where('class_room_id', $classRoomId)
            ->where('academic_year_id', $academicYearId)
            ->whereNotNull('roll_number')
            ->orderByDesc('roll_number');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }
}
