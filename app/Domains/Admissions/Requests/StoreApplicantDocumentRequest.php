<?php

namespace App\Domains\Admissions\Requests;

use App\Support\Enums\AdmissionDocumentCategory;
use Illuminate\Foundation\Http\FormRequest;

class StoreApplicantDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categories = implode(',', array_column(AdmissionDocumentCategory::cases(), 'value'));

        return [
            'category' => ['required', "in:{$categories}"],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category' => __('document type'),
            'document' => __('document'),
        ];
    }
}
