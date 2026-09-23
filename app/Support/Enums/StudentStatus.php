<?php

namespace App\Support\Enums;

enum StudentStatus: string
{
    case New = 'new';
    case Active = 'active';
    case Promoted = 'promoted';
    case Transferred = 'transferred';
    case Withdrawn = 'withdrawn';
    case Suspended = 'suspended';
    case Graduated = 'graduated';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Active => 'Active',
            self::Promoted => 'Promoted',
            self::Transferred => 'Transferred',
            self::Withdrawn => 'Withdrawn',
            self::Suspended => 'Suspended',
            self::Graduated => 'Graduated Grade 8',
            self::Archived => 'Archived',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Active => 'success',
            self::Promoted => 'primary',
            self::Transferred => 'warning',
            self::Withdrawn => 'neutral',
            self::Suspended => 'danger',
            self::Graduated => 'accent',
            self::Archived => 'neutral',
        };
    }
}
