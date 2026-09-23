<?php

namespace App\Domains\Accounts\Requests;

use App\Domains\Accounts\Services\PasswordService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', function ($attribute, $value, $fail) {
                if (! Hash::check($value, $this->user()->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'new_password' => ['required', 'confirmed', ...PasswordService::policyRules(), PasswordService::historyRule()],
        ];
    }
}
