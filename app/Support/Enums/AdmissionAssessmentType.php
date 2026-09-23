<?php

namespace App\Support\Enums;

enum AdmissionAssessmentType: string
{
    case Interview = 'interview';
    case Literacy = 'literacy';
    case Numeracy = 'numeracy';
    case Observation = 'observation';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Interview => 'Interview',
            self::Literacy => 'Literacy',
            self::Numeracy => 'Numeracy',
            self::Observation => 'Observation',
            self::Other => 'Other',
        };
    }
}
