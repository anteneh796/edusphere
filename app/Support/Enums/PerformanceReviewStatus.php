<?php

namespace App\Support\Enums;

enum PerformanceReviewStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::Completed => 'Completed',
            self::Archived => 'Archived',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Submitted => 'warning',
            self::Completed => 'success',
            self::Archived => 'neutral',
        };
    }
}