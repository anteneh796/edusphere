<?php

namespace App\Domains\Students\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'relationship' => ['required', 'in:father,mother,guardian,sibling'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'national_id' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:students,id', 'distinct'],
        ];
    }
}
