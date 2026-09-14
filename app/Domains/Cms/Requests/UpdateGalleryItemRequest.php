<?php

namespace App\Domains\Cms\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGalleryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'caption' => ['nullable', 'string', 'max:180'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published' => ['required', 'boolean'],
        ];
    }
}