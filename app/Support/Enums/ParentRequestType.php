<?php

namespace App\Support\Enums;

enum ParentRequestType: string
{
    case DocumentRequest = 'document_request';
    case TransferCertificate = 'transfer_certificate';
    case EnrollmentConfirmation = 'enrollment_confirmation';
    case InfoUpdate = 'info_update';
    case Absence = 'absence';
    case Meeting = 'meeting';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::DocumentRequest => 'Student document request',
            self::TransferCertificate => 'Transfer certificate request',
            self::EnrollmentConfirmation => 'Enrollment confirmation',
            self::InfoUpdate => 'Parent information update',
            self::Absence => 'Absence request',
            self::Meeting => 'Meeting request',
            self::Other => 'Other school service',
        };
    }
}
