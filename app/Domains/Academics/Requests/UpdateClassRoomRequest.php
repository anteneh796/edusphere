<?php

namespace App\Domains\Academics\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_level_id' => ['required', 'exists:grade_levels,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:30'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ];
    }
}
