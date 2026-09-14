<?php

namespace App\Support\Enums;

enum ExamStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Completed => 'Completed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Published => 'info',
            self::Completed => 'success',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
