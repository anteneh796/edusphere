<?php

namespace App\Domains\Attendance\Requests;

use App\Support\Enums\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;

class SyncAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'max:1000'],
            'records.*.client_id' => ['required', 'uuid'],
            'records.*.class_room_id' => ['required', 'exists:class_rooms,id'],
            'records.*.date' => ['required', 'date'],
            'records.*.student_id' => ['nullable', 'exists:students,id'],
            'records.*.student_number' => ['nullable', 'string', 'max:20'],
            'records.*.status' => ['required', 'in:'.implode(',', AttendanceStatus::values())],
            'records.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'records.*.student_id.required_without' => 'A student id or student number is required.',
        ];
    }
}
