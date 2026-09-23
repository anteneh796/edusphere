<?php

namespace App\Domains\Admissions\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdmissionApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'other_names' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'in:male,female'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'national_id' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'previous_school' => ['nullable', 'string', 'max:150'],
            'intake_academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'grade_level_id' => ['nullable', 'exists:grade_levels,id'],
            'guardians' => ['nullable', 'array', 'min:1'],
            'guardians.*.id' => ['nullable', 'exists:application_guardians,id'],
            'guardians.*.first_name' => ['required', 'string', 'max:100'],
            'guardians.*.last_name' => ['required', 'string', 'max:100'],
            'guardians.*.relationship' => ['required', 'in:father,mother,guardian,sibling'],
            'guardians.*.phone' => ['nullable', 'string', 'max:20'],
            'guardians.*.email' => ['nullable', 'email', 'max:150'],
            'guardians.*.occupation' => ['nullable', 'string', 'max:100'],
            'guardians.*.national_id' => ['nullable', 'string', 'max:30'],
            'guardians.*.address' => ['nullable', 'string', 'max:255'],
            'guardians.*.is_primary' => ['nullable', 'boolean'],
            'guardians.*.is_emergency' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => __('first name'),
            'last_name' => __('last name'),
        ];
    }
}
