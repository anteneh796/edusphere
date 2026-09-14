<?php

namespace App\Support\Enums;

enum GuardianRelationship: string
{
    case Father = 'father';
    case Mother = 'mother';
    case Guardian = 'guardian';
    case Sibling = 'sibling';

    public function label(): string
    {
        return match ($this) {
            self::Father => 'Father',
            self::Mother => 'Mother',
            self::Guardian => 'Guardian',
            self::Sibling => 'Sibling',
        };
    }
}
