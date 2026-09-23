<?php

namespace App\Domains\Students\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveEmergencyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'relationship' => ['required', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:30'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:99'],
            'authorized_pickup' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
