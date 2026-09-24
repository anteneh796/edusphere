<?php

namespace App\Domains\Students\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'gender' => ['required', 'in:male,female'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'place_of_birth' => ['nullable', 'string', 'max:100'],
            'national_id' => ['nullable', 'string', 'max:30', 'unique:students,national_id'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'enrollment_date' => ['required', 'date'],
            'previous_school' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'health_notes' => ['nullable', 'string', 'max:255'],
            'grade_level_id' => ['required', 'exists:grade_levels,id'],
            'class_room_id' => ['required', 'exists:class_rooms,id'],
            'guardian.first_name' => ['required', 'string', 'max:100'],
            'guardian.last_name' => ['required', 'string', 'max:100'],
            'guardian.relationship' => ['required', 'in:father,mother,grandparent,sibling,other'],
            'guardian.phone' => ['nullable', 'string', 'max:20'],
            'guardian.email' => ['nullable', 'email', 'max:150'],
            'guardian.occupation' => ['nullable', 'string', 'max:100'],
        ];
    }
}
