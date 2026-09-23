<?php

namespace App\Support\Enums;

enum OfficialLetterType: string
{
    case Employment = 'employment';
    case Appointment = 'appointment';
    case ContractRenewal = 'contract_renewal';
    case ExperienceCertificate = 'experience_certificate';
    case LeaveApproval = 'leave_approval';
    case Warning = 'warning';
    case Promotion = 'promotion';
    case ResignationAcceptance = 'resignation_acceptance';

    public function label(): string
    {
        return match ($this) {
            self::Employment => 'Employment letter',
            self::Appointment => 'Appointment letter',
            self::ContractRenewal => 'Contract renewal',
            self::ExperienceCertificate => 'Experience certificate',
            self::LeaveApproval => 'Leave approval',
            self::Warning => 'Warning letter',
            self::Promotion => 'Promotion letter',
            self::ResignationAcceptance => 'Resignation acceptance',
        };
    }
}