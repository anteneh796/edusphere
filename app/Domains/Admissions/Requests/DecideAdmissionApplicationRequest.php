<?php

namespace App\Domains\Admissions\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecideAdmissionApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:approved,rejected,waitlisted'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'force' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'decision' => __('decision'),
            'comment' => __('comment'),
        ];
    }
}
