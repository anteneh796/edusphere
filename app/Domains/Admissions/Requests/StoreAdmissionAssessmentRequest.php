<?php

namespace App\Domains\Admissions\Requests;

use App\Support\Enums\AdmissionAssessmentType;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdmissionAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $types = implode(',', array_column(AdmissionAssessmentType::cases(), 'value'));

        return [
            'type' => ['required', "in:{$types}"],
            'scheduled_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => __('assessment type'),
            'scheduled_at' => __('scheduled at'),
        ];
    }
}
