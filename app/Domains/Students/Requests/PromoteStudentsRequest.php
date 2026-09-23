<?php

namespace App\Domains\Students\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromoteStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_room_id' => ['required', 'exists:class_rooms,id'],
            'target_academic_year_id' => ['required', 'exists:academic_years,id'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
