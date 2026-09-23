<?php

namespace App\Support\Enums;

enum ReportCardCommentType: string
{
    case Teacher = 'teacher';
    case Principal = 'principal';
    case Attendance = 'attendance';

    public function label(): string
    {
        return match ($this) {
            self::Teacher => 'Teacher',
            self::Principal => 'Principal',
            self::Attendance => 'Attendance',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Teacher => 'info',
            self::Principal => 'primary',
            self::Attendance => 'warning',
        };
    }
}
