<?php

namespace App\Domains\Students\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guardian = $this->route('guardian');

        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'relationship' => ['required', 'in:father,mother,guardian,sibling'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('guardians', 'email')->ignore($guardian)],
            'occupation' => ['nullable', 'string', 'max:100'],
            'national_id' => ['nullable', 'string', 'max:30', Rule::unique('guardians', 'national_id')->ignore($guardian)],
            'address' => ['nullable', 'string', 'max:255'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:students,id', 'distinct'],
        ];
    }
}
