<?php

namespace App\Support\Enums;

enum RoleName: string
{
    case SuperAdmin = 'super_admin';
    case SchoolAdmin = 'school_admin';
    case Principal = 'principal';
    case VicePrincipal = 'vice_principal';
    case Registrar = 'registrar';
    case FinanceOfficer = 'finance_officer';
    case HROfficer = 'hr_officer';
    case Reception = 'reception';
    case Teacher = 'teacher';
    case Student = 'student';
    case Parent = 'parent';

    public function label(): string
    {
        return config('rbac.role_labels', [])[$this->value] ?? ucfirst(str_replace('_', ' ', $this->value));
    }

    /** Lower priority value = higher privilege. */
    public function level(): int
    {
        return match ($this) {
            self::SuperAdmin => 1,
            self::Principal => 2,
            self::SchoolAdmin => 3,
            self::VicePrincipal => 4,
            self::Registrar => 5,
            self::FinanceOfficer => 6,
            self::HROfficer => 7,
            self::Reception => 8,
            self::Teacher => 8,
            self::Parent => 9,
            self::Student => 10,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
