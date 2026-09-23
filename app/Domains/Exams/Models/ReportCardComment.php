<?php

namespace App\Domains\Exams\Models;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\ReportCardCommentType;
use App\Support\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCardComment extends Model
{
    use HasFactory, HasUuid;

    protected static function booted(): void
    {
        static::creating(function (self $comment): void {
            if ($comment->reportCard?->isLocked()) {
                throw new \RuntimeException('Published report card comments are immutable.');
            }
        });

        static::updating(function (self $comment): void {
            if ($comment->reportCard?->isLocked()) {
                throw new \RuntimeException('Published report card comments are immutable.');
            }
        });

        static::deleting(function (self $comment): void {
            if ($comment->reportCard?->isLocked()) {
                throw new \RuntimeException('Published report card comments cannot be deleted.');
            }
        });
    }

    protected $fillable = [
        'report_card_id',
        'type',
        'comment',
        'position',
        'commented_by_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => ReportCardCommentType::class,
        ];
    }

    /* -------------------------------- Relations -------------------------------- */

    public function reportCard(): BelongsTo
    {
        return $this->belongsTo(ReportCard::class);
    }

    public function commentedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commented_by_id');
    }
}
