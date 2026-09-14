<?php

namespace App\Support\Enums;

enum StudentStatus: string
{
    case New = 'new';
    case Active = 'active';
    case Transferred = 'transferred';
    case Graduated = 'graduated';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Active => 'Active',
            self::Transferred => 'Transferred',
            self::Graduated => 'Graduated',
            self::Archived => 'Archived',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Active => 'success',
            self::Transferred => 'warning',
            self::Graduated => 'accent',
            self::Archived => 'neutral',
        };
    }
}
