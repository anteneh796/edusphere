<?php

namespace App\Domains\Exams\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveExamResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'results' => ['required', 'array'],
            'results.*.student_id' => ['required', 'distinct', 'exists:students,id'],
            'results.*.marks_obtained' => ['nullable', 'numeric', 'min:0'],
            'results.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'results.required' => 'No results were submitted.',
            'results.*.student_id.distinct' => 'Each student can only appear once.',
        ];
    }
}
