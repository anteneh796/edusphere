<?php

namespace App\Support\Enums;

enum AdmissionDocumentCategory: string
{
    case BirthCertificate = 'birth_certificate';
    case ReportCard = 'report_card';
    case NationalId = 'national_id';
    case PassportPhoto = 'passport_photo';
    case TransferLetter = 'transfer_letter';
    case GuardianId = 'guardian_id';

    public function label(): string
    {
        return match ($this) {
            self::BirthCertificate => 'Birth certificate',
            self::ReportCard => 'Previous report card',
            self::NationalId => 'National ID',
            self::PassportPhoto => 'Passport photo',
            self::TransferLetter => 'Transfer letter',
            self::GuardianId => 'Guardian ID',
        };
    }
}
