<?php

namespace App\Support\Enums;

enum HrDocumentCategory: string
{
    case EmploymentContract = 'employment_contract';
    case DegreeCertificate = 'degree_certificate';
    case TeachingLicense = 'teaching_license';
    case NationalId = 'national_id';
    case CurriculumVitae = 'curriculum_vitae';
    case RecommendationLetter = 'recommendation_letter';
    case MedicalCertificate = 'medical_certificate';
    case PerformanceReport = 'performance_report';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::EmploymentContract => 'Employment contract',
            self::DegreeCertificate => 'Degree certificate',
            self::TeachingLicense => 'Teaching license',
            self::NationalId => 'National ID copy',
            self::CurriculumVitae => 'CV',
            self::RecommendationLetter => 'Recommendation letter',
            self::MedicalCertificate => 'Medical certificate',
            self::PerformanceReport => 'Performance report',
            self::Other => 'Other',
        };
    }
}