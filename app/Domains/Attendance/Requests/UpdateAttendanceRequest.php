<?php

namespace App\Domains\Attendance\Requests;

use App\Support\Enums\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array'],
            'records.*.student_id' => ['required', 'distinct', 'exists:students,id'],
            'records.*.status' => ['required', 'in:'.implode(',', AttendanceStatus::values())],
            'records.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'records.required' => 'No attendance records were submitted.',
            'records.*.student_id.distinct' => 'Each student can only appear once.',
        ];
    }
}
