<?php

namespace App\Domains\Admissions\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'grade_level_id' => ['nullable', Rule::exists('grade_levels', 'id')->where(fn ($query) => $query->where('is_active', true)->whereNotNull('stage'))],
            'parents' => ['nullable', 'array', 'min:1'],
            'parents.*.id' => ['nullable', 'exists:application_guardians,id'],
            'parents.*.first_name' => ['required', 'string', 'max:100'],
            'parents.*.last_name' => ['required', 'string', 'max:100'],
            'parents.*.relationship' => ['required', 'in:father,mother,grandparent,sibling,other'],
            'parents.*.phone' => ['nullable', 'string', 'max:20'],
            'parents.*.email' => ['nullable', 'email', 'max:150'],
            'parents.*.occupation' => ['nullable', 'string', 'max:100'],
            'parents.*.national_id' => ['nullable', 'string', 'max:30'],
            'parents.*.address' => ['nullable', 'string', 'max:255'],
            'parents.*.is_primary' => ['nullable', 'boolean'],
            'parents.*.is_emergency' => ['nullable', 'boolean'],
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
