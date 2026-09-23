<?php

namespace App\Domains\Admissions\Models;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Accounts\Models\User;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeCapacity extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'academic_year_id',
        'grade_level_id',
        'capacity',
        'allow_override',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'allow_override' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
