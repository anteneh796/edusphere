<?php

namespace App\Support\Enums;

enum AcquisitionStatus: string
{
    case Pending = 'pending';
    case Received = 'received';
    case Catalogued = 'catalogued';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Received => 'Received',
            self::Catalogued => 'Catalogued',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
