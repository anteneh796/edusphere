<?php

namespace App\Domains\Accounts\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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
            'new_password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }
}
