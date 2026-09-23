<?php

namespace App\Domains\Accounts\Requests;

use App\Domains\Accounts\Services\PasswordService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($user?->id)],
            'username' => ['nullable', 'string', 'max:50',
                Rule::unique('users', 'username')->ignore($user?->id)],
            'employee_id' => ['nullable', 'string', 'max:30',
                Rule::unique('users', 'employee_id')->ignore($user?->id)],
            'student_number' => ['nullable', 'string', 'max:30',
                Rule::unique('users', 'student_number')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s-]+$/'],
            'status' => ['required', 'in:active,inactive,suspended,archived'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ];

        if ($this->filled('password')) {
            $rules['password'] = ['nullable', 'confirmed', ...PasswordService::policyRules(), PasswordService::historyRule($user)];
            $rules['password_confirmation'] = ['required'];
        }

        return $rules;
    }
}
