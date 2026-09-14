<?php

namespace App\Domains\Exams\Requests;

use App\Support\Enums\ExamStatus;
use App\Support\Enums\ExamType;
use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:'.implode(',', ExamType::values())],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:'.implode(',', ExamStatus::values())],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
