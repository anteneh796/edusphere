<?php

namespace App\Domains\Exams\Models;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Academics\Models\Subject;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSubject extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'exam_subjects';

    protected $fillable = [
        'exam_id',
        'class_room_id',
        'subject_id',
        'max_marks',
        'pass_marks',
        'weight',
        'exam_date',
        'position',
        'instruction',
    ];

    protected function casts(): array
    {
        return [
            'max_marks' => 'decimal:2',
            'pass_marks' => 'decimal:2',
            'weight' => 'decimal:2',
            'exam_date' => 'date',
        ];
    }

    /* -------------------------------- Relations -------------------------------- */

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }
}
