<?php

namespace App\Domains\Academics\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignSubjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subjects' => ['required', 'array'],
            'subjects.*.subject_id' => ['required', 'distinct', 'exists:subjects,id'],
            'subjects.*.teacher_id' => ['nullable', 'exists:users,id'],
            'subjects.*.periods_per_week' => ['nullable', 'integer', 'min:1', 'max:40'],
        ];
    }

    public function messages(): array
    {
        return [
            'subjects.required' => 'Assign at least one subject to this class.',
        ];
    }
}
