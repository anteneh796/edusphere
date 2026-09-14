<?php

namespace App\Domains\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_room_id' => ['required', 'exists:class_rooms,id'],
            'date' => ['required', 'date'],
        ];
    }
}
