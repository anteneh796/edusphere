<?php

namespace App\Support\Enums;

enum PromotionStatus: string
{
    case Pending = 'pending';
    case Promoted = 'promoted';
    case Retained = 'retained';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Promoted => 'Promoted',
            self::Retained => 'Retained',
            self::Completed => 'Completed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'neutral',
            self::Promoted => 'success',
            self::Retained => 'warning',
            self::Completed => 'accent',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
