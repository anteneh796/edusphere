<?php

namespace App\Support\Enums;

enum QualificationType: string
{
    case Qualification = 'qualification';
    case Certification = 'certification';
    case ProfessionalSkill = 'professional_skill';

    public function label(): string
    {
        return match ($this) {
            self::Qualification => 'Qualification',
            self::Certification => 'Certification',
            self::ProfessionalSkill => 'Professional skill',
        };
    }
}