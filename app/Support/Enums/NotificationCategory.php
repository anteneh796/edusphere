<?php

namespace App\Support\Enums;

enum NotificationCategory: string
{
    case Approval = 'approval';
    case Inquiry = 'inquiry';
    case System = 'system';
    case Fee = 'fee';
    case Attendance = 'attendance';

    public function label(): string
    {
        return match ($this) {
            self::Approval => 'Approvals',
            self::Inquiry => 'Inquiries',
            self::System => 'System',
            self::Fee => 'Finance',
            self::Attendance => 'Attendance',
        };
    }
}
