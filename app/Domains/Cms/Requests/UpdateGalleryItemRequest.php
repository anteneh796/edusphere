<?php

namespace App\Domains\Cms\Requests;

use App\Domains\Cms\Models\GalleryItem;
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
            'album' => ['nullable', 'string', 'max:100'],
            'media_type' => ['nullable', 'string', 'in:'.implode(',', array_keys(GalleryItem::MEDIA_TYPE_LABELS))],
            'video_url' => ['nullable', 'url', 'max:500', 'required_if:media_type,video'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published' => ['required', 'boolean'],
        ];
    }
}
