<?php

namespace App\Domains\Cms\Requests;

use App\Domains\Cms\Models\NewsItem;
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
            'category' => ['nullable', 'string', 'in:'.implode(',', array_keys(NewsItem::CATEGORIES))],
            'excerpt' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
            'published' => ['required', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
