<?php

namespace App\Support;

class GradeScale
{
    /**
     * Letter grade for a percentage mark.
     */
    public static function letter(int|float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 40 => 'D',
            default => 'F',
        };
    }

    public static function passes(int|float $percentage): bool
    {
        return $percentage >= 50;
    }

    public static function badgeColor(string $letter): string
    {
        return match ($letter) {
            'A+', 'A' => 'success',
            'B+', 'B' => 'primary',
            'C' => 'warning',
            'D' => 'danger',
            default => 'neutral',
        };
    }
}
