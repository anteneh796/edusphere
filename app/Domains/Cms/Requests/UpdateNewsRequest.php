<?php

namespace App\Domains\Cms\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'alpha_dash', 'max:120', Rule::unique('news', 'slug')->ignore($this->route('news'))->withoutTrashed()],
            'excerpt' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'published' => ['required', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}