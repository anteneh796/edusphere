<?php

namespace App\Domains\Admissions\Requests;

use App\Support\Enums\DocumentVerificationStatus;
use Illuminate\Foundation\Http\FormRequest;

class VerifyApplicantDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $statuses = implode(',', [DocumentVerificationStatus::Verified->value, DocumentVerificationStatus::Rejected->value, DocumentVerificationStatus::ReplacementRequired->value]);

        return [
            'status' => ['required', "in:{$statuses}"],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => __('verification status'),
            'rejection_reason' => __('rejection reason'),
        ];
    }
}
