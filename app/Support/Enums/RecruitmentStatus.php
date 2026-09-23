<?php

namespace App\Support\Enums;

enum RecruitmentStatus: string
{
    case Applied = 'applied';
    case Interviewing = 'interviewing';
    case Shortlisted = 'shortlisted';
    case Offered = 'offered';
    case Hired = 'hired';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Applied => 'Applied',
            self::Interviewing => 'Interviewing',
            self::Shortlisted => 'Shortlisted',
            self::Offered => 'Offer made',
            self::Hired => 'Hired',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Applied => 'neutral',
            self::Interviewing => 'accent',
            self::Shortlisted => 'info',
            self::Offered => 'warning',
            self::Hired => 'success',
            self::Rejected => 'danger',
        };
    }
}