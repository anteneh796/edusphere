<?php

namespace App\Domains\Admissions\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationGuardianRequest extends FormRequest
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
            'relationship' => ['required', 'in:father,mother,guardian,sibling'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'national_id' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
            'is_emergency' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => __('guardian first name'),
            'last_name' => __('guardian last name'),
            'relationship' => __('guardian relationship'),
        ];
    }
}
