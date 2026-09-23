<?php

namespace App\Support\Enums;

enum ExamStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Published = 'published';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under Review',
            self::Approved => 'Approved',
            self::Published => 'Published',
            self::Completed => 'Completed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Submitted => 'info',
            self::UnderReview => 'warning',
            self::Approved => 'primary',
            self::Published => 'success',
            self::Completed => 'accent',
        };
    }

    public function isPublishable(): bool
    {
        return match ($this) {
            self::Approved, self::Published, self::Completed => true,
            default => false,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
