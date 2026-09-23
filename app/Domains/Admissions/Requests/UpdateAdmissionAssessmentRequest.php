<?php

namespace App\Domains\Admissions\Requests;

use App\Support\Enums\AdmissionAssessmentStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdmissionAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $statuses = implode(',', array_column(AdmissionAssessmentStatus::cases(), 'value'));

        return [
            'status' => ['required', "in:{$statuses}"],
            'score' => ['nullable', 'integer', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => __('assessment status'),
            'score' => __('score'),
        ];
    }
}
