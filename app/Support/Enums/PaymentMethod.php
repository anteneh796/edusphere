<?php

namespace App\Support\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Bank = 'bank';
    case Mobile = 'mobile';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Bank => 'Bank Transfer',
            self::Mobile => 'Mobile Money',
            self::Other => 'Other',
        };
    }
}
