<?php

namespace App\Domains\Cms\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'alpha_dash', 'max:60', Rule::unique('pages', 'slug')->ignore($this->route('page'))->withoutTrashed()],
            'title' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'published' => ['required', 'boolean'],
        ];
    }
}