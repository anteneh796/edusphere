<?php

namespace App\Domains\Accounts\Requests;

use App\Domains\Accounts\Services\PasswordService;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'employee_id' => ['nullable', 'string', 'max:30', 'unique:users,employee_id'],
            'student_number' => ['nullable', 'string', 'max:30', 'unique:users,student_number'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s-]+$/'],
            'password' => ['required', 'confirmed', ...PasswordService::policyRules()],
            'status' => ['required', 'in:active,inactive,suspended,archived'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'username',
            'employee_id' => 'employee ID',
            'student_number' => 'student number',
        ];
    }
}
