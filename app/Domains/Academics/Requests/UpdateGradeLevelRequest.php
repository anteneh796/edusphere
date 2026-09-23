<?php

namespace App\Domains\Academics\Requests;

use App\Support\Enums\GradeStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGradeLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $activeCodes = array_keys(GradeStage::offeredGrades());

        return [
            'name' => ['required', 'string', 'max:50'],
            'code' => ['required', 'string', 'max:10', Rule::in($activeCodes)],
            'stage' => ['required', 'string', Rule::enum(GradeStage::class)],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:999'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
