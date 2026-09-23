<?php

namespace App\Support\Enums;

enum StaffAttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case OnLeave = 'on_leave';
    case OfficialDuty = 'official_duty';
    case HalfDay = 'half_day';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Absent => 'Absent',
            self::Late => 'Late',
            self::OnLeave => 'On leave',
            self::OfficialDuty => 'Official duty',
            self::HalfDay => 'Half day',
        };
    }

    public function short(): string
    {
        return match ($this) {
            self::Present => 'P',
            self::Absent => 'A',
            self::Late => 'L',
            self::OnLeave => 'LV',
            self::OfficialDuty => 'OD',
            self::HalfDay => 'HD',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Present, self::OfficialDuty => 'success',
            self::Absent => 'danger',
            self::Late => 'warning',
            self::OnLeave => 'accent',
            self::HalfDay => 'info',
        };
    }
}