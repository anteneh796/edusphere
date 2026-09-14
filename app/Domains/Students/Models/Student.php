<?php

namespace App\Domains\Students\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Exams\Models\ExamResult;
use App\Support\Enums\StudentStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'student_number',
        'first_name',
        'last_name',
        'other_names',
        'gender',
        'date_of_birth',
        'photo_path',
        'national_id',
        'status',
        'enrollment_date',
        'previous_school',
        'address',
        'health_notes',
        'grade_level_id',
        'class_room_id',
        'academic_year_id',
        'guardian_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'enrollment_date' => 'date',
        ];
    }

    /* ---------------------------------- Helpers --------------------------------- */

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->other_names,
            $this->last_name,
        ])));
    }

    public function initials(): string
    {
        return strtoupper(
            str($this->first_name)->substr(0, 1)->value().
            str($this->last_name)->substr(0, 1)->value()
        );
    }

    public function age(): int
    {
        return (int) $this->date_of_birth?->age ?? 0;
    }

    public function statusLabel(): string
    {
        return StudentStatus::tryFrom($this->status)?->label() ?? ucfirst($this->status ?? '');
    }

    public function statusBadgeColor(): string
    {
        return StudentStatus::tryFrom($this->status)?->badgeColor() ?? 'neutral';
    }

    /* -------------------------------- Relations -------------------------------- */

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class)->withTimestamps()->withPivot('is_primary');
    }

    public function primaryGuardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'guardian_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class)->latest('academic_year_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [StudentStatus::Active->value, StudentStatus::New->value]);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('student_number', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('other_names', 'like', "%{$term}%");
        });
    }
}
