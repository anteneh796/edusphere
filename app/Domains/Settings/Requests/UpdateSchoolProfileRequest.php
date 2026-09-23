<?php

namespace App\Domains\Settings\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_name' => ['required', 'string', 'max:200'],
            'school_tagline' => ['nullable', 'string', 'max:200'],
            'school_email' => ['nullable', 'email', 'max:150'],
            'school_phone' => ['nullable', 'string', 'max:30'],
            'school_address' => ['nullable', 'string', 'max:255'],
            'school_website' => ['nullable', 'url', 'max:255'],
            'academic_year' => ['nullable', 'string', 'regex:/^\d{4}\s*[-–—\/]\s*\d{4}$/'],
            'currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'string', 'max:100', Rule::in(timezone_identifiers_list())],
            'language' => ['required', Rule::in(['en', 'am'])],
            'announcement_enabled' => ['nullable', 'boolean'],
            'announcement_text' => ['nullable', 'string', 'max:500'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_telegram' => ['nullable', 'url', 'max:255'],

            // Security & login
            'login_method_email' => ['nullable', 'boolean'],
            'login_method_username' => ['nullable', 'boolean'],
            'login_method_employee_id' => ['nullable', 'boolean'],
            'login_method_student_id' => ['nullable', 'boolean'],
            'password_min_length' => ['nullable', 'integer', 'between:8,64'],
            'password_require_uppercase' => ['nullable', 'boolean'],
            'password_require_lowercase' => ['nullable', 'boolean'],
            'password_require_number' => ['nullable', 'boolean'],
            'password_require_symbol' => ['nullable', 'boolean'],
            'password_history_count' => ['nullable', 'integer', 'between:0,50'],
            'password_expiration_days' => ['nullable', 'integer', 'between:0,365'],
            'session_timeout_global' => ['nullable', 'integer', 'between:1,1440'],
        ];
    }
}
