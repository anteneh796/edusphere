<?php

namespace App\Support\Enums;

enum RoleName: string
{
    case SuperAdmin = 'super_admin';
    case Principal = 'principal';
    case Registrar = 'registrar';
    case Teacher = 'teacher';
    case Accountant = 'accountant';
    case Parent = 'parent';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Principal => 'Principal',
            self::Registrar => 'Registrar',
            self::Teacher => 'Teacher',
            self::Accountant => 'Accountant',
            self::Parent => 'Parent',
            self::Student => 'Student',
        };
    }

    /** Lower priority value = higher privilege. */
    public function level(): int
    {
        return match ($this) {
            self::SuperAdmin => 1,
            self::Principal => 2,
            self::Registrar => 3,
            self::Teacher => 4,
            self::Accountant => 5,
            self::Parent => 6,
            self::Student => 7,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
