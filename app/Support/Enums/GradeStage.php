<?php

namespace App\Support\Enums;

/**
 * Escola scope stages. EduSphere serves Kindergarten through Grade 8 only.
 */
enum GradeStage: string
{
    case Kindergarten = 'kindergarten';
    case LowerPrimary = 'lower_primary';
    case UpperPrimary = 'upper_primary';

    public function label(): string
    {
        return match ($this) {
            self::Kindergarten => 'Kindergarten',
            self::LowerPrimary => 'Primary (Grades 1–4)',
            self::UpperPrimary => 'Upper Primary (Grades 5–8)',
        };
    }

    /**
     * Grades available to the executive portal, keyed by code => [name, stage].
     */
    public static function offeredGrades(): array
    {
        return [
            'KG1' => ['name' => 'Kindergarten 1', 'stage' => self::Kindergarten],
            'KG2' => ['name' => 'Kindergarten 2', 'stage' => self::Kindergarten],
            '1' => ['name' => 'Grade 1', 'stage' => self::LowerPrimary],
            '2' => ['name' => 'Grade 2', 'stage' => self::LowerPrimary],
            '3' => ['name' => 'Grade 3', 'stage' => self::LowerPrimary],
            '4' => ['name' => 'Grade 4', 'stage' => self::LowerPrimary],
            '5' => ['name' => 'Grade 5', 'stage' => self::UpperPrimary],
            '6' => ['name' => 'Grade 6', 'stage' => self::UpperPrimary],
            '7' => ['name' => 'Grade 7', 'stage' => self::UpperPrimary],
            '8' => ['name' => 'Grade 8', 'stage' => self::UpperPrimary],
        ];
    }
}
