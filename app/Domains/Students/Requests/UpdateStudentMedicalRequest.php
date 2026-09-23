<?php

namespace App\Domains\Students\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentMedicalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'blood_group' => ['nullable', 'string', 'max:5'],
            'allergies' => ['nullable', 'string', 'max:1000'],
            'medical_conditions' => ['nullable', 'string', 'max:1000'],
            'medications' => ['nullable', 'string', 'max:1000'],
            'disability_support' => ['nullable', 'string', 'max:1000'],
            'doctor_name' => ['nullable', 'string', 'max:100'],
            'emergency_hospital' => ['nullable', 'string', 'max:150'],
            'health_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
