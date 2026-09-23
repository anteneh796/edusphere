<?php

namespace App\Support\Enums;

enum StudentTimelineType: string
{
    case Enrolled = 'enrolled';
    case Placed = 'placed';
    case SectionChange = 'section_change';
    case MedicalUpdated = 'medical_updated';
    case DocumentUploaded = 'document_uploaded';
    case DocumentVerified = 'document_verified';
    case Promoted = 'promoted';
    case Transfer = 'transfer';
    case Withdrawn = 'withdrawn';
    case StatusChange = 'status_change';
    case Note = 'note';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
