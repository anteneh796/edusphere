<?php

namespace App\Domains\Academics\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80', 'unique:subjects,name,'.$this->route('subject')?->getKey()],
            'code' => ['required', 'string', 'max:12', 'alpha', 'unique:subjects,code,'.$this->route('subject')?->getKey()],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }
}
