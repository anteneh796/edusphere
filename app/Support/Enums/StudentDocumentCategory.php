<?php

namespace App\Support\Enums;

enum StudentDocumentCategory: string
{
    case BirthCertificate = 'birth_certificate';
    case ReportCard = 'report_card';
    case TransferCertificate = 'transfer_certificate';
    case NationalId = 'national_id';
    case MedicalCertificate = 'medical_certificate';
    case Photo = 'photo';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::BirthCertificate => 'Birth Certificate',
            self::ReportCard => 'Report Card',
            self::TransferCertificate => 'Transfer Certificate',
            self::NationalId => 'National ID (FAN/FIN)',
            self::MedicalCertificate => 'Medical Certificate',
            self::Photo => 'Photo',
            self::Other => 'Other',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
