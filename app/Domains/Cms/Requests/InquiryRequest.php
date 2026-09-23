<?php

namespace App\Domains\Cms\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:admissions,general,visit'],
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'student_name' => ['nullable', 'string', 'max:120'],
            'grade_level' => ['nullable', 'string', 'max:60'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => __('full name'),
            'email' => __('email'),
            'type' => __('inquiry type'),
        ];
    }
}
