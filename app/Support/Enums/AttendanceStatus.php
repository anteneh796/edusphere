<?php

namespace App\Support\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case Excused = 'excused';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Absent => 'Absent',
            self::Late => 'Late',
            self::Excused => 'Excused',
        };
    }

    public function short(): string
    {
        return match ($this) {
            self::Present => 'P',
            self::Absent => 'A',
            self::Late => 'L',
            self::Excused => 'E',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Present => 'success',
            self::Absent => 'danger',
            self::Late => 'warning',
            self::Excused => 'info',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
