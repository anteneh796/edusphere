<?php

namespace App\Support\Enums;

enum ApprovalType: string
{
    case StudentTransfer = 'student_transfer';
    case TeacherLeave = 'teacher_leave';
    case FeeWaiver = 'fee_waiver';
    case AdmissionAcceptance = 'admission_acceptance';
    case TimetablePublication = 'timetable_publication';

    public function label(): string
    {
        return match ($this) {
            self::StudentTransfer => 'Student Transfer',
            self::TeacherLeave => 'Teacher Leave',
            self::FeeWaiver => 'Fee Waiver',
            self::AdmissionAcceptance => 'Admission Acceptance',
            self::TimetablePublication => 'Timetable Publication',
        };
    }

    /** Default reviewer role for each approval type. */
    public function reviewerRole(): RoleName
    {
        return match ($this) {
            self::StudentTransfer => RoleName::Principal,
            self::TeacherLeave => RoleName::VicePrincipal,
            self::FeeWaiver => RoleName::SchoolAdmin,
            self::AdmissionAcceptance => RoleName::Registrar,
            self::TimetablePublication => RoleName::Principal,
        };
    }
}
