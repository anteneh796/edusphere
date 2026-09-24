<?php

namespace App\Domains\Admissions\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Accounts\Models\User;
use App\Domains\Cms\Models\Inquiry;
use App\Domains\Students\Models\Student;
use App\Support\Enums\AdmissionStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionApplication extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'application_number',
        'status',
        'first_name',
        'last_name',
        'other_names',
        'gender',
        'date_of_birth',
        'national_id',
        'address',
        'previous_school',
        'intake_academic_year_id',
        'grade_level_id',
        'source_inquiry_id',
        'student_id',
        'parent_user_id',
        'applied_at',
        'decided_at',
        'decision',
        'decision_comment',
        'decision_by',
        'waitlisted_at',
        'waitlist_position',
        'enrolled_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'applied_at' => 'datetime',
            'decided_at' => 'datetime',
            'waitlisted_at' => 'datetime',
            'enrolled_at' => 'datetime',
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

    public function statusLabel(): string
    {
        return AdmissionStatus::tryFrom($this->status)?->label() ?? ucfirst($this->status ?? '');
    }

    public function statusBadgeColor(): string
    {
        return AdmissionStatus::tryFrom($this->status)?->badgeColor() ?? 'neutral';
    }

    public function isCandidate(): bool
    {
        return in_array($this->status, AdmissionStatus::candidateStages(), true);
    }

    public function enrolledSeatsForGrade(): int
    {
        return $this->gradeLevel
            ? Student::where('grade_level_id', $this->grade_level_id)
                ->whereIn('status', ['new', 'active'])
                ->count()
            : 0;
    }

    /* -------------------------------- Relations -------------------------------- */

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function intakeYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'intake_academic_year_id');
    }

    public function sourceInquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class, 'source_inquiry_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    public function decisionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decision_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parents(): HasMany
    {
        return $this->hasMany(ApplicationGuardian::class, 'application_id')->orderByDesc('is_primary');
    }

    public function primaryParent(): HasOne
    {
        return $this->hasOne(ApplicationGuardian::class, 'application_id')->where('is_primary', true);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicantDocument::class)->latest();
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(AdmissionAssessment::class)->latest('scheduled_at');
    }

    public function communications(): HasMany
    {
        return $this->hasMany(AdmissionCommunication::class)->latest('occurred_at');
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopeCandidates(Builder $query): Builder
    {
        return $query->whereIn('status', AdmissionStatus::candidateStages());
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', AdmissionStatus::PendingApproval->value);
    }

    public function scopeWaitlisted(Builder $query): Builder
    {
        return $query->where('status', AdmissionStatus::Waitlisted->value)->orderBy('waitlist_position');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('application_number', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('other_names', 'like', "%{$term}%");
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeGrade(Builder $query, ?string $gradeLevelId): Builder
    {
        return $gradeLevelId ? $query->where('grade_level_id', $gradeLevelId) : $query;
    }
}
