<?php

namespace App\Domains\Accounts\Requests;

use App\Domains\Accounts\Services\PasswordService;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'password' => ['nullable', 'confirmed'],
            'password_confirmation' => ['required_with:password'],
        ];

        if ($this->filled('password')) {
            $rules['password'] = array_merge($rules['password'], PasswordService::policyRules());
            $rules['password'][] = PasswordService::historyRule($this->route('user'));
        }

        return $rules;
    }
}
