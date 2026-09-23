<?php

namespace App\Support\Enums;

enum TranscriptStatus: string
{
    case Dormant = 'dormant';
    case Active = 'active';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Dormant => 'No record yet',
            self::Active => 'In progress',
            self::Completed => 'Finalized',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Dormant => 'neutral',
            self::Active => 'primary',
            self::Completed => 'success',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
