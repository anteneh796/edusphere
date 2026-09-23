<?php

namespace App\Domains\Admissions\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeCapacityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'capacities' => ['required', 'array'],
            'capacities.*.grade_level_id' => ['required', 'exists:grade_levels,id'],
            'capacities.*.capacity' => ['required', 'integer', 'min:0', 'max:9999'],
            'capacities.*.allow_override' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'capacities' => __('grade capacities'),
        ];
    }
}
