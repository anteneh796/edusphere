<?php

namespace App\Support\Enums;

enum ExamType: string
{
    case Quiz = 'quiz';
    case Assignment = 'assignment';
    case Midterm = 'midterm';
    case Final = 'final';
    case Term = 'term';

    public function label(): string
    {
        return match ($this) {
            self::Quiz => 'Quiz',
            self::Assignment => 'Assignment',
            self::Midterm => 'Midterm Exam',
            self::Final => 'Final Exam',
            self::Term => 'Term Exam',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Quiz => 'info',
            self::Assignment => 'warning',
            self::Midterm => 'primary',
            self::Final => 'success',
            self::Term => 'accent',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
