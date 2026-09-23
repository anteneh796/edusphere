<?php

namespace App\Support\Enums;

enum StaffType: string
{
    case Teaching = 'teaching';
    case Administrative = 'administrative';
    case Finance = 'finance';
    case Reception = 'reception';
    case IT = 'information_technology';
    case Support = 'support';

    public function label(): string
    {
        return match ($this) {
            self::Teaching => 'Teaching',
            self::Administrative => 'Administrative',
            self::Finance => 'Finance',
            self::Reception => 'Reception',
            self::IT => 'Information Technology',
            self::Support => 'Support',
        };
    }
}
