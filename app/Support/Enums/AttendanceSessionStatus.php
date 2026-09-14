<?php

namespace App\Support\Enums;

enum AttendanceSessionStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Closed => 'Closed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Open => 'primary',
            self::Closed => 'neutral',
        };
    }
}
