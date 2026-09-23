<?php

namespace App\Support\Enums;

enum EmploymentStatus: string
{
    case Active = 'active';
    case Probation = 'probation';
    case OnLeave = 'on_leave';
    case Suspended = 'suspended';
    case Resigned = 'resigned';
    case Retired = 'retired';
    case Terminated = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Probation => 'Probation',
            self::OnLeave => 'On leave',
            self::Suspended => 'Suspended',
            self::Resigned => 'Resigned',
            self::Retired => 'Retired',
            self::Terminated => 'Terminated',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Probation => 'accent',
            self::OnLeave => 'warning',
            self::Suspended => 'danger',
            self::Resigned => 'neutral',
            self::Retired => 'neutral',
            self::Terminated => 'danger',
        };
    }

    public function isEmployed(): bool
    {
        return in_array($this, [self::Active, self::Probation, self::OnLeave], true);
    }
}