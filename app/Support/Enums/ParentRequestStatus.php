<?php

namespace App\Support\Enums;

enum ParentRequestStatus: string
{
    case Submitted = 'submitted';
    case Processing = 'processing';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Processing => 'Processing',
            self::Completed => 'Completed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Submitted => 'info',
            self::Processing => 'warning',
            self::Completed => 'success',
        };
    }
}
