<?php

namespace App\Domains\Settings\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'academic_year' => ['nullable', 'string', 'regex:/^\d{4}\s*[-–—/]\s*\d{4}$/'],
            'currency' => ['required', 'string', 'size:3'],
        ];
    }
}
