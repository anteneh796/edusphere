<?php

namespace App\Support\Enums;

enum MeetingRequestStatus: string
{
    case Requested = 'requested';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Confirmed => 'Confirmed',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Requested => 'info',
            self::Confirmed => 'success',
            self::Completed => 'accent',
            self::Cancelled => 'neutral',
        };
    }
}
