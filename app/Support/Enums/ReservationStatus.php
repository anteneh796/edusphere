<?php

namespace App\Support\Enums;

enum ReservationStatus: string
{
    case Requested = 'requested';
    case Ready = 'ready';
    case Collected = 'collected';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Ready => 'Ready for Pickup',
            self::Collected => 'Collected',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Requested => 'warning',
            self::Ready => 'info',
            self::Collected => 'success',
            self::Cancelled => 'neutral',
            self::Expired => 'danger',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
