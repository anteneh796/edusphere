<?php

namespace App\Domains\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('attendance.configure') === true;
    }

    public function rules(): array
    {
        return [
            'attendance_mode' => ['required', 'in:daily,period'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'late_threshold_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'excused_counts_as_present' => ['boolean'],
            'absence_alert_threshold' => ['required', 'integer', 'min:1', 'max:99'],
            'late_alert_threshold' => ['required', 'integer', 'min:1', 'max:99'],
            'parent_absence_notification' => ['boolean'],
            'correction_approval_required' => ['boolean'],
        ];
    }
}
