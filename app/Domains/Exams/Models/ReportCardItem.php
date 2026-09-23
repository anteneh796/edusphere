<?php

namespace App\Domains\Exams\Models;

use App\Domains\Academics\Models\Subject;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCardItem extends Model
{
    use HasFactory, HasUuid;

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            if ($item->reportCard?->isLocked()) {
                throw new \RuntimeException('Published report card items are immutable.');
            }
        });

        static::updating(function (self $item): void {
            if ($item->reportCard?->isLocked()) {
                throw new \RuntimeException('Published report card items are immutable.');
            }
        });

        static::deleting(function (self $item): void {
            if ($item->reportCard?->isLocked()) {
                throw new \RuntimeException('Published report card items cannot be deleted.');
            }
        });
    }

    protected $fillable = [
        'report_card_id',
        'subject_id',
        'position',
        'weight',
        'max_marks',
        'pass_marks',
        'marks_obtained',
        'percentage',
        'passes',
        'teacher_comment',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'max_marks' => 'decimal:2',
            'pass_marks' => 'decimal:2',
            'marks_obtained' => 'decimal:2',
            'percentage' => 'decimal:2',
            'passes' => 'boolean',
        ];
    }

    /* ------------------------------- Domain logic ------------------------------- */

    public function passes(): bool
    {
        return $this->marks_obtained !== null && (float) $this->marks_obtained >= (float) $this->pass_marks;
    }

    /* -------------------------------- Relations -------------------------------- */

    public function reportCard(): BelongsTo
    {
        return $this->belongsTo(ReportCard::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
