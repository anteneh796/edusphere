<?php

namespace App\Support\Enums;

enum ContractRenewalStatus: string
{
    case Active = 'active';
    case ExpiringSoon = 'expiring_soon';
    case Expired = 'expired';
    case Renewed = 'renewed';
    case Terminated = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::ExpiringSoon => 'Expiring soon',
            self::Expired => 'Expired',
            self::Renewed => 'Renewed',
            self::Terminated => 'Terminated',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::ExpiringSoon => 'warning',
            self::Expired => 'neutral',
            self::Renewed => 'accent',
            self::Terminated => 'danger',
        };
    }
}