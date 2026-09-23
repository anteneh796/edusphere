<?php

namespace App\Domains\HumanResources\Models;

use App\Support\Enums\RecruitmentStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentCandidate extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'position_id',
        'applied_position',
        'cv_path',
        'top_skills',
        'experience_years',
        'interview_date',
        'decision',
        'decision_notes',
        'hiring_status',
        'notes',
        'converted_employee_id',
    ];

    protected function casts(): array
    {
        return [
            'interview_date' => 'date',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function convertedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'converted_employee_id');
    }

    public function statusEnum(): ?RecruitmentStatus
    {
        return RecruitmentStatus::tryFrom($this->hiring_status);
    }

    public function statusLabel(): string
    {
        return $this->statusEnum()?->label() ?? ucfirst($this->hiring_status);
    }

    public function statusBadgeColor(): string
    {
        return $this->statusEnum()?->badgeColor() ?? 'neutral';
    }
}