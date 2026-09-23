<?php

namespace App\Domains\Exams\Models;

use App\Domains\Academics\Models\AcademicTerm;
use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Student;
use App\Support\Enums\ReportCardStatus;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportCard extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'academic_year_id',
        'academic_term_id',
        'exam_id',
        'student_id',
        'report_card_number',
        'status',
        'term_name',
        'term_sequence',
        'total_max_marks',
        'total_obtained_marks',
        'average_percent',
        'class_rank',
        'class_size',
        'attendance_absent',
        'attendance_present',
        'attendance_total',
        'teacher_comment',
        'principal_remarks',
        'promotion_status',
        'generated_by_id',
        'approved_by_id',
        'approved_at',
        'published_by_id',
        'published_at',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReportCardStatus::class,
            'total_max_marks' => 'decimal:2',
            'total_obtained_marks' => 'decimal:2',
            'average_percent' => 'decimal:2',
            'class_rank' => 'integer',
            'class_size' => 'integer',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    /* ----------------------------- Relations ----------------------------- */

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReportCardItem::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ReportCardComment::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by_id');
    }

    /* ----------------------------- Helpers ----------------------------- */

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    public function isPublishable(): bool
    {
        return $this->status === ReportCardStatus::Approved;
    }
}
