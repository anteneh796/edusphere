<?php

namespace App\Domains\Accounts\Requests\Auth;

use App\Domains\Accounts\Models\User;
use App\Domains\Accounts\Services\PasswordService;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'string', 'email', 'max:150'],
            'password' => ['required', 'confirmed', ...PasswordService::policyRules(), function ($attribute, $value, $fail) {
                $user = User::where('email', $this->input('email'))->first();

                if ($user && PasswordService::wasUsed($user, (string) $value)) {
                    $fail('You have used this password recently. Please choose a different password.');
                }
            }],
        ];
    }
}
