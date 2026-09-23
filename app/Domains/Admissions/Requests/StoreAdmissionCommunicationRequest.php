<?php

namespace App\Domains\Admissions\Requests;

use App\Support\Enums\CommunicationType;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdmissionCommunicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $types = implode(',', array_column(CommunicationType::cases(), 'value'));

        return [
            'type' => ['required', "in:{$types}"],
            'direction' => ['required', 'in:inbound,outbound'],
            'contact' => ['nullable', 'string', 'max:150'],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['nullable', 'string', 'max:2000'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => __('communication type'),
            'direction' => __('direction'),
        ];
    }
}
