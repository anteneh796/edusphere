<?php

namespace App\Domains\Students\Requests;

use App\Support\Enums\StudentTransferType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(StudentTransferType::class)],
            'to_class_room_id' => ['required_if:type,internal', 'nullable', 'exists:class_rooms,id'],
            'transfer_date' => ['nullable', 'date'],
            'destination_school' => ['required_if:type,external', 'nullable', 'string', 'max:150'],
            'certificate_number' => ['nullable', 'string', 'max:50'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
