<?php

namespace App\Domains\Exams\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Accounts\Models\User;
use App\Support\Enums\ExamStatus;
use App\Support\Enums\ExamType;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'academic_year_id',
        'created_by_id',
        'name',
        'type',
        'start_date',
        'end_date',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => ExamType::class,
            'status' => ExamStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function isPublished(): bool
    {
        return $this->status === ExamStatus::Published;
    }

    public function isCompleted(): bool
    {
        return $this->status === ExamStatus::Completed;
    }

    public function resultsEnteredCount(): int
    {
        return (int) $this->papers()->withCount('results')->get()->sum('results_count');
    }

    /* -------------------------------- Relations -------------------------------- */

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function papers(): HasMany
    {
        return $this->hasMany(ExamSubject::class)->orderBy('position');
    }

    /* --------------------------------- Scopes ---------------------------------- */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ExamStatus::Published->value);
    }
}
