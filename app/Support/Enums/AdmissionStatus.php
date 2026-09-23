<?php

namespace App\Support\Enums;

enum AdmissionStatus: string
{
    case Inquiry = 'inquiry';
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case AssessmentScheduled = 'assessment_scheduled';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Enrolled = 'enrolled';
    case Waitlisted = 'waitlisted';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Inquiry => 'Inquiry',
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under review',
            self::AssessmentScheduled => 'Assessment scheduled',
            self::PendingApproval => 'Pending approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Enrolled => 'Enrolled',
            self::Waitlisted => 'Waitlisted',
            self::Withdrawn => 'Withdrawn',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Inquiry => 'neutral',
            self::Draft => 'neutral',
            self::Submitted => 'info',
            self::UnderReview => 'info',
            self::AssessmentScheduled => 'warning',
            self::PendingApproval => 'warning',
            self::Approved => 'success',
            self::Enrolled => 'success',
            self::Rejected => 'danger',
            self::Waitlisted => 'accent',
            self::Withdrawn => 'danger',
        };
    }

    /**
     * Stages an applicant is still in the active air pipeline (not yet decided).
     */
    public static function candidateStages(): array
    {
        return [
            self::Inquiry->value,
            self::Draft->value,
            self::Submitted->value,
            self::UnderReview->value,
            self::AssessmentScheduled->value,
            self::PendingApproval->value,
            self::Waitlisted->value,
        ];
    }
}
