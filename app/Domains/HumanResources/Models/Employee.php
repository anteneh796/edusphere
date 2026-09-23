<?php

namespace App\Domains\HumanResources\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\EmploymentStatus;
use App\Support\Enums\EmploymentType;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_id',
        'full_name',
        'gender',
        'date_of_birth',
        'national_id',
        'photo_path',
        'phone',
        'email',
        'address',
        'department_id',
        'position_id',
        'employment_type',
        'joining_date',
        'supervisor_id',
        'employment_status',
        'university',
        'qualification',
        'degree',
        'specialization',
        'teaching_license',
        'years_of_experience',
        'certifications',
        'professional_skills',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joining_date' => 'date',
            'years_of_experience' => 'integer',
        ];
    }

    /* -------------------------------- Relations -------------------------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supervisor_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'supervisor_id');
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(EmployeeQualification::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmploymentContract::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function trainingRecords(): HasMany
    {
        return $this->hasMany(TrainingRecord::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(HrDocument::class);
    }

    public function payrollProfile(): HasOne
    {
        return $this->hasOne(PayrollProfile::class);
    }

    public function officialLetters(): HasMany
    {
        return $this->hasMany(OfficialLetter::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(EmployeeStatusHistory::class);
    }

    /* ---------------------------------- Helpers --------------------------------- */

    public function statusEnum(): ?EmploymentStatus
    {
        return EmploymentStatus::tryFrom($this->employment_status);
    }

    public function typeEnum(): ?EmploymentType
    {
        return EmploymentType::tryFrom($this->employment_type);
    }

    public function statusBadgeColor(): string
    {
        return $this->statusEnum()?->badgeColor() ?? 'neutral';
    }

    public function isEmployed(): bool
    {
        return $this->statusEnum()?->isEmployed() ?? false;
    }

    public function initials(): string
    {
        return strtoupper(str($this->full_name)->substr(0, 1)->value());
    }

    public function yearsOfService(): int
    {
        if (! $this->joining_date) {
            return 0;
        }

        return (int) $this->joining_date->diffInYears(now());
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('employment_status', EmploymentStatus::Active->value);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('employment_status', $status) : $query;
    }

    public function scopeType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('employment_type', $type) : $query;
    }

    public function scopeOfDepartment(Builder $query, ?string $departmentId): Builder
    {
        return $departmentId ? $query->where('department_id', $departmentId) : $query;
    }

    public function scopeOfPosition(Builder $query, ?string $positionId): Builder
    {
        return $positionId ? $query->where('position_id', $positionId) : $query;
    }
}