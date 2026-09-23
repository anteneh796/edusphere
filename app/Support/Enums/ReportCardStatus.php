<?php

namespace App\Support\Enums;

enum ReportCardStatus: string
{
    case Draft = 'draft';
    case Generated = 'generated';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Generated => 'Generated',
            self::Submitted => 'Submitted',
            self::Approved => 'Approved',
            self::Published => 'Published',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Generated => 'info',
            self::Submitted => 'warning',
            self::Approved => 'primary',
            self::Published => 'success',
        };
    }

    public function isPublishable(): bool
    {
        return $this === self::Approved || $this === self::Published;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
