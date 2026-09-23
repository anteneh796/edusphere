<?php

namespace App\Domains\Exams\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Student;
use App\Support\Enums\TranscriptStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transcript extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'status',
        'year_level_label',
        'term_count',
        'total_max_marks',
        'total_obtained_marks',
        'average_percent',
        'generated_by_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => TranscriptStatus::class,
            'total_max_marks' => 'decimal:2',
            'total_obtained_marks' => 'decimal:2',
            'average_percent' => 'decimal:2',
        ];
    }

    /* ----------------------------- Relations ----------------------------- */

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_id');
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class);
    }
}
