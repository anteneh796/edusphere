<?php

namespace App\Support\Enums;

enum ParentRelationship: string
{
    case Father = 'father';
    case Mother = 'mother';
    case Grandparent = 'grandparent';
    case Sibling = 'sibling';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Father => 'Father',
            self::Mother => 'Mother',
            self::Grandparent => 'Grandparent',
            self::Sibling => 'Sibling',
            self::Other => 'Other',
        };
    }
}
