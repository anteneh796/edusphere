<?php

namespace App\Domains\HumanResources\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\PerformanceReviewStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    use HasFactory, HasUuid;

    public const CATEGORIES = [
        'teaching_quality',
        'classroom_management',
        'professional_conduct',
        'attendance_score',
        'student_engagement',
        'admin_responsibility',
    ];

    protected $fillable = [
        'employee_id',
        'period',
        'evaluator_id',
        'teaching_quality',
        'classroom_management',
        'professional_conduct',
        'attendance_score',
        'student_engagement',
        'admin_responsibility',
        'overall_score',
        'strengths',
        'improvements',
        'recommendations',
        'status',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function statusEnum(): ?PerformanceReviewStatus
    {
        return PerformanceReviewStatus::tryFrom($this->status);
    }

    public function statusLabel(): string
    {
        return $this->statusEnum()?->label() ?? ucfirst($this->status);
    }

    public function statusBadgeColor(): string
    {
        return $this->statusEnum()?->badgeColor() ?? 'neutral';
    }

    public function categoryScores(): array
    {
        $scores = [];

        foreach (self::CATEGORIES as $category) {
            $scores[$category] = $this->{$category};
        }

        return $scores;
    }

    public function computeOverall(): ?float
    {
        $scores = array_values(array_filter($this->categoryScores(), fn ($score) => $score !== null));

        if ($scores === []) {
            return null;
        }

        return round(array_sum($scores) / count($scores), 2);
    }

    public function ratingLabel(): string
    {
        $overall = $this->overall_score ?? $this->computeOverall();

        if ($overall === null) {
            return '—';
        }

        return match (true) {
            $overall >= 85 => 'Excellent',
            $overall >= 70 => 'Good',
            $overall >= 50 => 'Fair',
            default => 'Needs improvement',
        };
    }
}