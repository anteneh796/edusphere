<?php

namespace App\Support\Enums;

enum StudentTransferType: string
{
    case Internal = 'internal';
    case External = 'external';

    public function label(): string
    {
        return match ($this) {
            self::Internal => 'Internal transfer',
            self::External => 'External transfer',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
