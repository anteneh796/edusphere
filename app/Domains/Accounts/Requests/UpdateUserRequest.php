<?php

namespace App\Domains\Accounts\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($this->user?->id)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s-]+$/'],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
            'status' => ['required', 'in:active,inactive,suspended'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ];
    }
}
